<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:read-competition-results')->everyFiveMinutes();
Schedule::command('app:read-forecast-results-command')->everyMinute();
Schedule::command('app:generate-missing-forecasts')->daily();
Schedule::command('app:read-athletes')->daily();
