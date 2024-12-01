<?php

namespace App\Http\Controllers\Forecasts;

use App\Helpers\FavoriteHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Forecast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class ShowController extends Controller
{
    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, string $id, LinkHelper $linkHelper, SeasonHelper $seasonHelper, bool $showContentOnly = false): View|Response|RedirectResponse|array
    {
        $this->linkHelper = $linkHelper;

        $forecast = Forecast::query()->with('competition')
            ->where('id', $id)
            ->first();


        if(!$forecast){
            return redirect()->to(route('forecasts.index'));
        }

        $favoriteAthleteIds = [];

        if($user = auth()->user()){
            $favoriteAthleteIds = FavoriteHelper::instance()->getUserFavoriteAthletesId($user);
        }

        if($showContentOnly){
            return view('forecasts.partials.show-content', compact('forecast', 'favoriteAthleteIds'));
        }

        return view('forecasts.show', compact('forecast', 'favoriteAthleteIds'));
    }
}
