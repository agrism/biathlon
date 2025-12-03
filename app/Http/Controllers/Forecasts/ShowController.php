<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\DisciplineEnum;
use App\Helpers\FavoriteHelper;
use App\Helpers\LinkHelper;
use App\Helpers\SeasonHelper;
use App\Http\Controllers\Controller;
use App\Models\Athlete;
use App\Models\EventCompetitionResult;
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

        $forecast = Forecast::query()->with('competition.results.athlete')
            ->where('id', $id)
            ->first();

        $isTeamDiscipline = DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)->isTeamDiscipline();

        $startingUserTempIds = $forecast->competition->results->map(function (EventCompetitionResult $result)use($isTeamDiscipline){
            return $result->athlete->attachTempId(isTeamDiscipline: $isTeamDiscipline)->temp_id;
        })->toArray();

        if(!$forecast){
            return redirect()->to(route('forecasts.index'));
        }

        $favoriteAthleteIds = [];

        if($user = auth()->user()){
            $favoriteAthleteIds = FavoriteHelper::instance()->getUserFavoriteAthletesId($user);
        }

        if($showContentOnly){
            return view('forecasts.partials.show-content', compact('forecast', 'favoriteAthleteIds', 'startingUserTempIds', 'isTeamDiscipline'));
        }

        return view('forecasts.show', compact('forecast', 'favoriteAthleteIds', 'startingUserTempIds', 'isTeamDiscipline'));
    }
}
