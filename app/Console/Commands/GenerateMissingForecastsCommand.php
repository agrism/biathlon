<?php

namespace App\Console\Commands;

use App\Enums\Forecast\ForecastStatusEnum;
use App\Enums\Forecast\ForecastTypeEnum;
use App\Models\EventCompetition;
use App\Models\Forecast;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMissingForecastsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-missing-forecasts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        EventCompetition::query()
            ->where('start_time', '>', now()->subMonths(3))
            ->with('forecast')
            ->get()
            ->each(function (EventCompetition $competition): bool {
                if ($competition->forecast) {
                    return true;
                }

                $forecast = new Forecast;
                $forecast->event_competition_id = $competition->id;
                $forecast->status = ForecastStatusEnum::COMING;
                $forecast->name = 'Forecast: ' . $competition->description;

                if(now()->lt(Carbon::parse('2025-10-20 00:00:00'))){
                    $forecast->type = ForecastTypeEnum::FORECAST_FIRST_SIX_PLACES;
                } else {
                    $forecast->type = ForecastTypeEnum::FORECAST_DAINIS_SCHEMA;
                }

                $forecast->submit_deadline_at = $competition->start_time;
                $forecast->save();

                dump('created: ' . $forecast->name);

                return true;
            });
    }
}
