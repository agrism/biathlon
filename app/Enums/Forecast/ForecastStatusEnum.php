<?php

namespace App\Enums\Forecast;


use App\Helpers\Forecasts\ForecastAbstractionHelper;
use App\Helpers\Forecasts\ForecastFirstSixPlacesServiceHelper;
use App\Models\User;

enum ForecastStatusEnum: string
{
    case COMING = 'coming';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
}
