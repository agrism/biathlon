<?php

namespace App\Console\Commands;

use App\Enums\DisciplineEnum;
use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\Models\User;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use Illuminate\Console\Command;

class TempTransferForecastDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:temp-transfer-forecast-data-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ForecastSubmittedData::query()->each(function(ForecastSubmittedData $data){

            $forecast = Forecast::query()->where('id', $data->forecast_id)->first();

            if(!$userModel = User::query()->where('id', $data->user_id)->first()){
                dd('User model not found by Id: '.$data->user_id);
            }

            $user  = $forecast->final_data->getUserByUserModel($userModel);

            $valueObjectAthletes = $user->getAthletes();

            if(!$forecast->competition){
                dd('Competition not found, forecastId: '.$data->forecast->id);
            }

            $isTeamDiscipline = DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)->isTeamDiscipline();

            foreach ($data->submitted_data->athletes as $index => $submittedUserModel){


                if(!$submittedUserModel){
                    continue;
                }

                if(!$submittedUserModel?->id){
                    continue;
                }

                if(!$athlete = Athlete::query()->where('id', $submittedUserModel->id)->first()){
                    dd('Athlete not found by id: '.$submittedUserModel->id);
                }

                $athlete->attachTempId(isTeamDiscipline: $isTeamDiscipline);

                data_set($valueObjectAthletes, $index, new AthleteValueObject(
                    id: $athlete->id,
                    tempId: $athlete->temp_id,
                    name: $athlete->getFullName(),
                    flagUrl: $athlete->flag_uri,
                ));
            }

            $user->athletes = $valueObjectAthletes;

            $forecast->final_data->updateUser($user);
            $forecast->save();
            dump('forecast_submitted_data processed, id: '. $data->id);
        });
    }
}
