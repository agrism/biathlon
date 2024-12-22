<?php

namespace App\Console\Commands;

use App\Casts\AthleteStatsDetailsCast;
use App\Enums\DisciplineEnum;
use App\Enums\Forecast\AwardPointEnum;
use App\Enums\Forecast\ForecastStatusEnum;
use App\Helpers\Forecasts\ForecastFirstSixPlacesServiceHelper;
use App\Models\Athlete;
use App\Models\Forecast;
use App\Models\ForecastAward;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\FinalDataValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\PointValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ReadForecastResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-forecast-results-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected FinalDataValueObject $finalResults;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forecastsToHande = Forecast::query()
            ->with('competition.results')
            ->whereHas('competition', function (Builder $builder) {
                $builder->whereNotNull('results_handled_at');
            })
            ->where('status', ForecastStatusEnum::COMING)
            ->get();

        $forecastsToHande->each(function (Forecast $forecast) {

            $isTeamDiscipline = DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)
                ->isTeamDiscipline();

            $forecast->final_data->results = $forecast->competition->getAthletesByRank(
                isTeamDiscipline: $isTeamDiscipline,
                limit: 6
            )
                ->pluck('athlete')
                ->map(function (Athlete $athlete): AthleteValueObject {
                    return new AthleteValueObject(
                        id: $athlete->id,
                        tempId: $athlete->temp_id,
                        name: $athlete->getFullName(),
                        flagUrl: $athlete->flag_uri,
                        stats: new AthleteStatsDetailValueObject(
                            statSeason: $athlete->stat_season,
                            statsSeasonPointTotal: $athlete->stat_p_total,
                            statsSeasonPointsSprint: $athlete->stat_p_sprint,
                            statsSeasonPointsIndividual: $athlete->stat_p_individual,
                            statsSeasonPointsPursuit: $athlete->stat_p_pursuit,
                            statsSeasonPointsMass: $athlete->stat_p_mass,
                            statsSkiKmb: $athlete->stat_ski_kmb,
                            statSkiing: $athlete->stat_skiing,
                            statShooting: $athlete->stat_shooting,
                            statShootingProne: $athlete->stat_shooting_prone,
                            statShootingStanding: $athlete->stat_shooting_standing,
                        ),
                    );
                })->toArray();

            foreach ($forecast->final_data->users as &$user) {
                $pointService = ForecastFirstSixPlacesServiceHelper::instance();
                $pointService->calculateUserPoints(forecast: $forecast, user: $user->getModel());

                $user->points = [
                    new PointValueObject(
                        type: AwardPointEnum::REGULAR_POINT,
                        value: $pointService->getMainPoints(),
                    ),
                    new PointValueObject(
                        type: AwardPointEnum::BONUS_POINT,
                        value: $pointService->getBonusPoints(),
                    )
                ];
            }

            $forecast->status = ForecastStatusEnum::COMPLETED;
            $forecast->save();

            collect($forecast->final_data->users)->each(function (UserValueObject $user) use ($forecast) {
                collect($user->points)->each(function (PointValueObject $point) use ($forecast, $user) {
                    if (!$award = ForecastAward::query()
                        ->where('forecast_id', $forecast->id)
                        ->where('user_id', $user->id)
                        ->where('type', $point->type)
                        ->first()) {
                        $award = new ForecastAward;
                        $award->forecast_id = $forecast->id;
                        $award->user_id = $user->id;
                        $award->type = $point->type;
                    }

                    $award->points = $point->value;
                    $award->save();
                });
            });

            dump('forecast: ' . $forecast->id . ' completed');
        });
    }
}
