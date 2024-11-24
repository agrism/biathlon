<?php

namespace App\Console\Commands;

use App\Enums\Forecast\ForecastStatusEnum;
use App\Enums\Forecast\ForecastTypeEnum;
use App\Models\EventCompetition;
use App\Models\Forecast;
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
            ->with('forecasts')
            ->get()
            ->each(function (EventCompetition $competition): bool {
                if ($competition->forecasts->count() > 0) {
                    return true;
                }

                $forecast = new Forecast;
                $forecast->event_competition_id = $competition->id;
                $forecast->status = ForecastStatusEnum::COMING;
                $forecast->name = 'Forecast: ' . $competition->description;
                $forecast->type = ForecastTypeEnum::FORECAST_FIRST_SIX_PLACES;
                $forecast->submit_deadline_at = $competition->start_time;
                $forecast->save();

                dump('created: ' . $forecast->name);

                return true;
            });
    }
}
