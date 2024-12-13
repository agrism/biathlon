<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('app:read-competition-results')->everyFiveMinutes();
    $schedule->command('app:read-forecast-results-command')->everyMinute();
};
