<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\Season;
use App\Services\BiathlonResultApi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class ReadCompetitionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-competitions';

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
        Event::query()
//            ->where('id', '>', 258)
//            ->where('id', '>', 513)
            ->get()->each(function (Event $event) use ($api): bool {
            $response = $api->competitions(eventId: $event->event_remote_id);

            if ($response->status() !== Response::HTTP_OK) {
                dump($response->status());
                dd($response->body());
                return true;
            }

            $data = $response->body();

            $eventCompetitions = json_decode($data, true);

            foreach ($eventCompetitions as $competitionData) {

                if (EventCompetition::query()->where(
                    'race_remote_id',
                    $raceId = data_get($competitionData, 'RaceId')
                )->exists()) {
                    dump('cont: '.$raceId);
                    continue;
                }

                $competition = new EventCompetition;
                $competition->event_id = $event->id;
                $competition->race_remote_id = $raceId;
                $competition->km = data_get($competitionData, 'km');
                $competition->cat_remote_id = data_get($competitionData, 'catId');
                $competition->discipline_remote_id = data_get($competitionData, 'DisciplineId');
                $competition->start_time = Carbon::parse(data_get($competitionData, 'StartTime'));
                $competition->description = data_get($competitionData, 'Description');
                $competition->short_description = data_get($competitionData, 'ShortDescription');
                $competition->location = data_get($competitionData, 'Location');
                $competition->nr_shootings = data_get($competitionData, 'NrShootings');
                $competition->nr_spare_rounds = data_get($competitionData, 'NrSpareRounds');
                $competition->has_spare_rounds = data_get($competitionData, 'HasSpareRounds');
                $competition->penalty_seconds = data_get($competitionData, 'PenaltySeconds');
                $competition->nr_legs = data_get($competitionData, 'NrLegs');
                $competition->shooting_positions = data_get($competitionData, 'ShootingPositions');
                $competition->local_utc_offset = data_get($competitionData, 'LocalUTCOffset');
                $competition->rsc = data_get($competitionData, 'RSC');
                $competition->save();
            }

            return true;
        });
    }
}
