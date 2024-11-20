<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Season;
use App\Services\BiathlonResultApi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class ReadEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-events';

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
        Season::query()->get()->each(function (Season $season) use ($api): bool {
            $response = $api->events(seasonId: $season->name_remote);

//            dump($season->name_remote);
//            dump($response->body());

            if ($response->status() !== Response::HTTP_OK) {
                dd($response->getBody());
                return true;
            }

            $data = $response->body();

            $seasonEvents = json_decode($data, true);

            foreach ($seasonEvents as $eventData) {

                if (Event::query()->where(
                    'event_remote_id',
                    $eventRemoteId = data_get($eventData, 'EventId')
                )->exists()) {
                    dump('cont: '.$eventRemoteId);
                    continue;
                }

                $event = new Event;
                $event->event_remote_id = $eventRemoteId;
                $event->season_id = $season->id;
                $event->trimester = data_get($eventData, 'Trimester');
                $event->start_date = Carbon::parse(data_get($eventData, 'StartDate'));
                $event->end_date = Carbon::parse(data_get($eventData, 'EndDate'));
                $event->first_competition_date = Carbon::parse(data_get($eventData, 'FirstCompetitionDate'));
                $event->description = data_get($eventData, 'Description');
                $event->event_series_no = data_get($eventData, 'EventSeriesNr');
                $event->short_description = data_get($eventData, 'ShortDescription');
                $event->altitude = data_get($eventData, 'Altitude');
                $event->organizer_remote_id = data_get($eventData, 'OrganizerId');
                $event->organizer = data_get($eventData, 'Organizer');
                $event->nat = data_get($eventData, 'Nat');
                $event->nat_long = data_get($eventData, 'NatLong');
                $event->medal_set_id = data_get($eventData, 'MedalSetId');
                $event->event_classification_id = data_get($eventData, 'EventClassificationId');
                $event->level = data_get($eventData, 'Level');
                $event->utc_offset = data_get($eventData, 'UTCOffset');
                $event->notes = data_get($eventData, 'Notes');
                $event->save();
            }

            return true;
        });
    }
}
