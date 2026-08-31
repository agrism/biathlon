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
use App\Models\User;
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

        if(!$forecast){
            return redirect()->to(route('forecasts.index'));
        }

        $isTeamDiscipline = DisciplineEnum::tryFrom($forecast->competition->discipline_remote_id)->isTeamDiscipline();

        $startingUserTempIds = $forecast->competition->results->map(function (EventCompetitionResult $result)use($isTeamDiscipline){
            return $result->athlete->attachTempId(isTeamDiscipline: $isTeamDiscipline)->temp_id;
        })->toArray();

        $favoriteAthleteIds = [];

        if($user = auth()->user()){
            $favoriteAthleteIds = FavoriteHelper::instance()->getUserFavoriteAthletesId($user);
        }

        $targetUser = null;
        if ($request->has('user_id')) {
            $targetUser = User::find($request->get('user_id'));
        }

        if (!$targetUser && auth()->check()) {
            $targetUser = auth()->user();
        }

        if($showContentOnly){
            return view('forecasts.partials.show-content', compact('forecast', 'favoriteAthleteIds', 'startingUserTempIds', 'isTeamDiscipline', 'targetUser'));
        }

        return view('forecasts.show', compact('forecast', 'favoriteAthleteIds', 'startingUserTempIds', 'isTeamDiscipline', 'targetUser'));
    }
}
