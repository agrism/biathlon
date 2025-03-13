<?php

namespace App\Console\Commands;

use App\Models\Athlete;
use App\Models\EventCompetition;
use App\Models\EventCompetitionResult;
use App\Services\BiathlonResultApi;
use App\ValueObjects\Athletes\AthleteStatsDetailValueObject;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class ReadCompetitionResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-competition-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(BiathlonResultApi $api)
    {
        EventCompetition::query()
            ->where('start_time', '>', Carbon::parse('2025-01-01'))
            ->where('start_time', '<', now()->addDay())
            ->with(['results', 'event'])
            ->whereHas('event', function ($q) {
                $q->where('level', 1);
            })
            ->whereNull('results_handled_at')
            ->get()->each(function (EventCompetition $competition) use ($api): bool {
                $response = $api->results(raceId: $competition->race_remote_id);

                if ($response->status() !== Response::HTTP_OK) {
                    dump(['code'=> $response->status()]);
                    dd($response->getBody());
                    return true;
                }

                $data = $response->body();

                $data = json_decode($data, true);
                $results = data_get($data, 'Results');

                $isStartLst = data_get($data, 'IsStartList');
                $isResultList = data_get($data, 'IsResult');
                $status = data_get($data, 'Competition.StatusId');
                $statusText = data_get($data, 'Competition.StatusText');

                dump([
                   '$isStartLst' => $isStartLst,
                   '$isResultList' => $isResultList,
                   '$status' => $status,
                   '$statusText' => $statusText,
//                   '$data' => $data,
                ]);

//                $dbHasFinalResultsAlready = EventCompetitionResult::query()
//                        ->where('event_competition_id', $competition->id)
//                        ->first()?->rank !== null;
//
//                if($dbHasFinalResultsAlready){
//                    dump('added results, competitionId: ' . $competition->id);
//                    if(!$competition->results_handled_at){
//                        $competition->results_handled_at = now();
//                        $competition->save();
//                    }
//                    return true;
//                }

                foreach ($results as $resultData) {
                    $athleteRemoteId = data_get($resultData, 'IBUId');

                    if (!$athlete = Athlete::query()->where('ibu_id', $athleteRemoteId)->first()) {
                        $apiAthlete = $api->athlete(ibuId: $athleteRemoteId);
                        $apiAthlete = $apiAthlete->body();
                        $apiAthlete = json_decode($apiAthlete);

                        $athlete = new Athlete;
                        $athlete->ibu_id = $athleteRemoteId;
                        $athlete->is_team = empty(data_get($apiAthlete, 'GivenName'));

                        $athlete->family_name = data_get($apiAthlete, 'FamilyName');
                        $athlete->given_name = data_get($apiAthlete, 'GivenName');
                        $athlete->other_family_names = data_get($apiAthlete, 'otherFamilyNames');
                        $athlete->other_given_names = data_get($apiAthlete, 'otherGivenNames');
                        $athlete->nat = data_get($apiAthlete, 'NAT');
                        $athlete->nf = data_get($apiAthlete, 'NF');
                        $athlete->birth_date = data_get($apiAthlete, 'Birthdate') ? Carbon::parse(
                            data_get($apiAthlete, 'Birthdate')
                        ) : null;
                        $athlete->gender_id = data_get($apiAthlete, 'GenderId');
                        $athlete->functions = data_get($apiAthlete, 'Functions');
                        $athlete->badge_value = null;
                        $athlete->badge_order = null;
                        $athlete->photo_uri = data_get($apiAthlete, 'PhotoURI');
                        $athlete->flag_uri = data_get($apiAthlete, 'FlagURI');
                        $athlete->save();
                    }


                    $result = EventCompetitionResult::query()
                        ->where('event_competition_id', $competition->id)
                        ->where('athlete_id', $athlete->id)
                        ->first();

                    $result = $result ?: new EventCompetitionResult;
                    $result->event_competition_id = $competition->id;
                    $result->start_order = data_get($resultData, 'StartOrder');
                    $result->irm = data_get($resultData, 'IRM');
                    $result->athlete_id = $athlete->id;
                    $result->bib = data_get($resultData, 'Bib');
                    $result->leg = data_get($resultData, 'Leg');
                    $result->rank = $this->nullIfEmpty(data_get($resultData, 'Rank'));
                    $result->shootings = data_get($resultData, 'Shootings');
                    $result->shooting_total = data_get($resultData, 'ShootingTotal');
                    $result->run_time = data_get($resultData, 'RunTime');
                    $result->total_time = data_get($resultData, 'TotalTime');
                    $result->wc = $this->nullIfEmpty(data_get($resultData, 'WC'));
                    $result->nc = $this->nullIfEmpty(data_get($resultData, 'NC'));
                    $result->noc = $this->nullIfEmpty(data_get($resultData, 'NOC'));
                    $result->start_time = data_get($resultData, 'StartTime');
                    $result->start_info = data_get($resultData, 'StartInfo');
                    $result->start_row = data_get($resultData, 'StartRow');
                    $result->start_lane = data_get($resultData, 'StartLane');
                    $result->bib_color = data_get($resultData, 'BibColor');
                    $result->behind = data_get($resultData, 'Behind');
                    $result->start_group = data_get($resultData, 'StartGroup');
                    $result->team_id = data_get($resultData, 'TeamId');
                    $result->pursuit_start_distance = data_get($resultData, 'PursuitStartDistance');
                    $result->result = data_get($resultData, 'Result');
                    $result->leg_rank = data_get($resultData, 'LegRank');
                    $result->team_rank_after_leg = data_get($resultData, 'TeamRankAfterLeg');
                    $result->start_confirmed = data_get($resultData, 'StartConfirmed');

                    if(!$athlete->details?->IBUId){
                        $athlete = ReadAthletesCommand::readAnsSaveAthleteDetailsData($athlete);
                    }

                    $result->stat_details = new AthleteStatsDetailValueObject(
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
                    );
                    $result->save();

                    dump('added results, competitionId: ' . $competition->id);

                }

                if($isResultList){
                    $competition->results_handled_at = now();
                    $competition->save();
                }

                usleep(500);

                return true;
            });
    }

    protected function nullIfEmpty(mixed $val): mixed
    {
        return empty($val) ? null : $val;
    }
}
