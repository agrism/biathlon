<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class SubmitSelectedAthleteController extends Controller
{
    public function __invoke(Request $request, string $id, string $place, string $athlete, LinkHelper $linkHelper): View|Response
    {
        if(!in_array($place, range(0,5))){
            abort(401, 'Place provided: '.$place);
        }

        if(!$forecast = Forecast::query()->where('id', $id)->first()){
            abort(404, 'Forecast not found: '.$id);
        }

        if(!$athleteModel = Athlete::query()->where('id', $athlete)->first()){
            abort(404, 'Athlete not found: '.$athlete);
        }

        if($forecast->submit_deadline_at->lt(now())){
            abort(401, 'forecast is closed');
        }

        /** @var ForecastSubmittedData $forecastSubmittedData */
        if(!$forecastSubmittedData = ForecastSubmittedData::query()
            ->where('forecast_id', $forecast->id)
            ->where('user_id', auth()->id())
            ->first()){
            $forecastSubmittedData = new ForecastSubmittedData;
            $forecastSubmittedData->forecast_id = $forecast->id;
            $forecastSubmittedData->user_id = auth()->id();
            $forecastSubmittedData->submitted_data = new ForecastFirstSixPlacesDataValueObject();
            $forecastSubmittedData->save();
        }

        /** @var ForecastFirstSixPlacesDataValueObject $data */
        $data = $forecastSubmittedData->submitted_data;

        $data->athletes[$place] = $athleteModel;

        $forecastSubmittedData->submitted_data = new ForecastFirstSixPlacesDataValueObject($data->athletes);

        $forecastSubmittedData->save();

        return (new ShowController())($request, $id, $linkHelper, SeasonHelper::instance(), true);
    }
}
