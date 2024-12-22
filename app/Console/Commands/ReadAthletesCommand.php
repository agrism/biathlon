<?php

namespace App\Console\Commands;

use App\Casts\AthleteDetailsCast;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\EventCompetitionResult;
use App\Services\BiathlonResultApi;
use Illuminate\Console\Command;

class ReadAthletesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-athletes';

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
        Event::query()
            ->where('level', 1)
//            ->where('start_date', '<', now()->startOfYear())
            ->where('end_date', '>',  now()->startOfYear())
            ->with('competitions.results.athlete')
            ->first()
            ?->competitions->each(function(EventCompetition $competition): void{

                $competition->results->each(function(EventCompetitionResult $result): void{
                    $result->refresh();
                    static::readAnsSaveAthleteDetailsData($result->athlete);
                });
            });

    }

    public static function readAnsSaveAthleteDetailsData(Athlete $athlete): Athlete
    {
        dump('before upd Athlete: ' . $athlete->getFullName());

        $response = app(BiathlonResultApi::class)->athlete(ibuId: $athlete->ibu_id);
        $rawDetails = $response->json();

        $details = AthleteDetailsCast::createDetails(details: $rawDetails);

        $athlete->details = $details;
        $athlete->details_updated_at = now();
        $athlete->save();
        $athlete->refresh();

        dump('updated Athlete: ' . $athlete->getFullName());

        return $athlete;
    }
}
