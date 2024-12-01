<?php

namespace App\Enums\Forecast;


use App\Helpers\Forecasts\ForecastAbstractionHelper;
use App\Helpers\Forecasts\ForecastFirstSixPlacesServiceHelper;

enum ForecastTypeEnum: string
{
    case FORECAST_FIRST_SIX_PLACES = 'forecast-first-six-places';

    public function getHelper(): ForecastAbstractionHelper
    {
        return match ($this){
            self::FORECAST_FIRST_SIX_PLACES => new ForecastFirstSixPlacesServiceHelper,
        };
    }
}
