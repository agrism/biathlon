<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\MoveDirectionEnum;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class HidePlaceController extends Controller
{
    public function __invoke(Request $request, string $id, string $place): View|Response
    {
        if(!auth()->check()){
            abort(401, 'Login first');
        }

        if($place < 0 || $place > 5){
            abort(400, 'Invalid place provided: '.$place);
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

        /** @var AthleteValueObject $athleteToUpdate */
        $athleteToUpdate = data_get($userValueObject->athletes, $place);
        $athleteToUpdate->isHidden = !$athleteToUpdate->isHidden;

        data_set($userValueObject->athletes, $place, $athleteToUpdate);

        $forecast->final_data->updateUser($userValueObject);
        $forecast->save();

        return (new ShowController())($request, $id, LinkHelper::instance(), SeasonHelper::instance(), true);
    }
}
