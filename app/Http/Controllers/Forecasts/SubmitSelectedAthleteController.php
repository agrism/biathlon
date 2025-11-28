<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Forecasts\Summary\ShowUserEventController;
use App\Models\Athlete;
use App\Models\Forecast;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class SubmitSelectedAthleteController extends Controller
{
    public function __invoke(Request $request, string $id, string $place, string $athlete, LinkHelper $linkHelper): View|Response|string
    {
        if(!auth()->check()){
            abort(401, 'Login first');
        }

        if(!in_array($place, range(0,5))){
            abort(401, 'Place provided: '.$place);
        }

        if(!$forecast = Forecast::query()->with('competition')->where('id', $id)->first()){
            abort(404, 'Forecast not found: '.$id);
        }

        if($forecast->submit_deadline_at->lt(now())){
            abort(401, 'forecast is closed');
        }

        /** @var Athlete $athleteModel */
        if(!$athleteModel = Athlete::query()->where('id', $athlete)->first()){
            abort(404, 'Athlete not found: '.$athlete);
        }

        $userValueObject = $forecast->final_data->getUserByUserModel(auth()->user());
        $userValueObject->athletes = $userValueObject->getAthletes();

        $isAthleteAlreadyAdded = false;

        collect($userValueObject->athletes)->each(function(AthleteValueObject $athleteValueObject) use(&$isAthleteAlreadyAdded, $athleteModel): void{
            if($athleteModel->id == $athleteValueObject->id){
                $isAthleteAlreadyAdded = true;
            }
        });

        if($isAthleteAlreadyAdded){
            abort(401, 'athlete is added already, athlete: '.$athleteModel->family_name);
        }

        data_set($userValueObject->athletes, $place, new AthleteValueObject(
            id: $athleteModel->id,
            tempId: $athleteModel->attachTempId(
                    isTeamDiscipline: DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)->isTeamDiscipline()
            )->temp_id,
            name: $athleteModel->getFullName(),
            flagUrl: $athleteModel->flag_uri,
            stats: new AthleteStatsDetailValueObject(
                statSeason: $athleteModel->stat_season,
                statsSeasonPointTotal: $athleteModel->stat_p_total,
                statsSeasonPointsSprint: $athleteModel->stat_p_sprint,
                statsSeasonPointsIndividual: $athleteModel->stat_p_individual,
                statsSeasonPointsPursuit: $athleteModel->stat_p_pursuit,
                statsSeasonPointsMass: $athleteModel->stat_p_mass,
                statsSkiKmb: $athleteModel->stat_ski_kmb,
                statSkiing: $athleteModel->stat_skiing,
                statShooting: $athleteModel->stat_shooting,
                statShootingProne: $athleteModel->stat_shooting_prone,
                statShootingStanding: $athleteModel->stat_shooting_standing,
            ),
            isHidden: data_get($userValueObject->athletes, $place)?->isHidden ?? false
        ));

        $forecast->final_data->updateUser($userValueObject);
        $forecast->save();

        $content = (new ShowController())($request, $id, $linkHelper, SeasonHelper::instance(), true);

        return response($content)->header('HX-Trigger', 'getIsUserCompletedForecastData-'.$forecast->id);
    }
}
