<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('app:read-competition-results')->everyMinute();
    $schedule->command('app:read-forecast-results-command')->everyMinute();
};
