<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

return function (Schedule $schedule) {

    $schedule->call(function() {
        Log::info('Scheduler test: ' . now());
    })->everyMinute();

    $schedule->command('app:read-competition-results')->everyMinute();
    $schedule->command('app:read-forecast-results-command')->everyMinute();
};
