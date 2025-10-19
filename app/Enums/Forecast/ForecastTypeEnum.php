<?php

namespace App\Enums\Forecast;


use App\Helpers\Forecasts\ForecastAbstractionHelper;
use App\Helpers\Forecasts\ForecastDainisServiceHelper;
use App\Helpers\Forecasts\ForecastFirstSixPlacesServiceHelper;

enum ForecastTypeEnum: string
{
    case FORECAST_FIRST_SIX_PLACES = 'forecast-first-six-places';
    case FORECAST_DAINIS_SCHEMA = 'dainis-schema';

    public function getHelper(): ForecastAbstractionHelper
    {
        return match ($this){
            self::FORECAST_FIRST_SIX_PLACES => new ForecastFirstSixPlacesServiceHelper,
            self::FORECAST_DAINIS_SCHEMA => new ForecastDainisServiceHelper,
        };
    }
}
