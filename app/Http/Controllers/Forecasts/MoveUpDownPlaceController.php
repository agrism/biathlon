<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\MoveDirectionEnum;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class MoveUpDownPlaceController extends Controller
{
    public function __invoke(Request $request, string $id, string $place, string $direction): View|Response
    {
        $directionEnum = MoveDirectionEnum::tryFrom($direction);

        if(!auth()->check()){
            abort(401, 'Login first');
        }

        if($directionEnum == MoveDirectionEnum::UP){
            if($place < 1 || $place > 5){
                abort(400, 'Invalid place provided: '.$place);
            }
        } else {
            if($place < 0 || $place > 4){
                abort(400, 'Invalid place provided: '.$place);
            }
        }

        if(!in_array($place, range(0,5))){
            abort(401, 'Place provided: '.$place);
        }

        if(!$forecast = Forecast::query()->where('id', $id)->first()){
            abort(404, 'Forecast not found: '.$id);
        }

        if($forecast->submit_deadline_at->lt(now())){
            abort(401, 'forecast is closed');
        }

        $userValueObject = $forecast->final_data->getUserByUserModel(auth()->user());

        $userValueObject->athletes = $userValueObject->getAthletes();

        if($directionEnum == MoveDirectionEnum::DOWN){
            $athleteToMoveDown = data_get($userValueObject->athletes, $place);
            $athleteToMoveUp = data_get($userValueObject->athletes, $place+1);

            data_set($userValueObject->athletes, $place+1, $athleteToMoveDown);
            data_set($userValueObject->athletes, $place, $athleteToMoveUp);
        } else {
            $athleteToMoveUp = data_get($userValueObject->athletes, $place);
            $athleteToMoveDown = data_get($userValueObject->athletes, $place-1);

            data_set($userValueObject->athletes, $place-1, $athleteToMoveUp);
            data_set($userValueObject->athletes, $place, $athleteToMoveDown);
        }

        $forecast->final_data->updateUser($userValueObject);
        $forecast->save();

        return (new ShowController())($request, $id, LinkHelper::instance(), SeasonHelper::instance(), true);
    }
}
