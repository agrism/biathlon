<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class SubmitSelectedAthleteController extends Controller
{
    public function __invoke(Request $request, string $id, string $place, string $athlete, LinkHelper $linkHelper): View|Response
    {
        if(!auth()->check()){
            abort(401, 'Login first');
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

        if(!$athleteModel = Athlete::query()->where('id', $athlete)->first()){
            abort(404, 'Athlete not found: '.$athlete);
        }

        $userValueObject = $forecast->final_data->getUserByUserModel(auth()->user());
        $userValueObject->athletes = $userValueObject->getAthletes();
        data_set($userValueObject->athletes, $place, new AthleteValueObject(
            id: $athleteModel->id,
            tempId: $athleteModel->attachTempId(
                    isTeamDiscipline: DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)->isTeamDiscipline()
            )->temp_id,
            name: $athleteModel->getFullName(),
            flagUrl: $athleteModel->flag_uri,
        ));

        $forecast->final_data->updateUser($userValueObject);
        $forecast->save();

        return (new ShowController())($request, $id, $linkHelper, SeasonHelper::instance(), true);
    }
}
