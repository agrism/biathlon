<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\Generic\GenericViewIndexHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\EventCompetitionResult;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper, SeasonHelper $seasonHelper): View
    {
        $authUserId = auth()->id();

        $this->linkHelper = $linkHelper;

        $forecast = Forecast::query()
            ->with('competition.results.athlete')
            ->with([
                'competition.results' => function ($query) {
                    $query->where('rank', '<=', 6)
                        ->orderBy('rank')
                        ->take(6);
                }
            ])
            ->with('submittedData.user')
            ->where('id', $id)
            ->first();

        $authUserSubmittedData = null;

        if ($authUserId) {
            $authUserSubmittedData = ForecastSubmittedData::query()
                ->where('forecast_id', $id)->where('user_id', $authUserId)->first()?->submitted_data;
        }

        if (!$authUserSubmittedData) {
            $authUserSubmittedData = new ForecastFirstSixPlacesDataValueObject(
                [
                    new Athlete,
                    new Athlete,
                    new Athlete,
                    new Athlete,
                    new Athlete,
                    new Athlete,
                ]
            );
        }

        return view('forecasts.show', compact('forecast', 'authUserSubmittedData'));
    }
}
