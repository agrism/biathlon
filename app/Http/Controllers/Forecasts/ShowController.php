<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\FavoriteHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\Forecast;
use App\Models\ForecastSubmittedData;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class ShowController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper, SeasonHelper $seasonHelper, bool $showContentOnly = false): View|Response|RedirectResponse|array
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

        if(!$forecast){
            return redirect()->to(route('forecasts.index'));
        }

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

        $favoriteAthleteIds = [];

        if($user = auth()->user()){
            $favoriteAthleteIds = FavoriteHelper::instance()->getUserFavoriteAthletesId($user);
        }

        if($showContentOnly){
            return view('forecasts.partials.show-content', compact('forecast', 'authUserSubmittedData', 'favoriteAthleteIds'));
        }

        return view('forecasts.show', compact('forecast', 'authUserSubmittedData', 'favoriteAthleteIds'));
    }
}
