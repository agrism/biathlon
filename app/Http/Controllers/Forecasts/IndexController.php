<?php

namespace App\Http\Controllers\Forecasts;

use App\Enums\Forecast\AwardPointEnum;
use App\Enums\Forecast\ForecastStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\Season;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->registerBread('Predictions');

        $authUserId = auth()->id();
        $authUser = auth()->user();

        // Active season selection (defaults to 2627)
        $seasonParam = $request->query('season', '2627');
        $season = Season::where('name', $seasonParam)->first() ?? Season::where('name', '2627')->first();
        $seasonId = $season?->id;

        // Fetch forecasts for this season (World Cup level 1 + Munich Loop One)
        $forecasts = Forecast::query()
            ->whereHas('competition.event', function ($q) use ($seasonId) {
                $q->where('season_id', $seasonId)->where(function ($sub) {
                    $sub->where('level', 1)->orWhere('events.event_remote_id', 'SB2627SIBUL1MU');
                });
            })
            ->with(['competition.event', 'competition.results'])
            ->get();

        $now = now();

        // Attach auth user forecast stats & metadata to each forecast
        $forecasts->transform(function (Forecast $forecast) use ($authUserId, $authUser) {
            $userObj = null;
            $submittedCount = 0;
            $userPoints = null;

            if ($authUserId && $forecast->final_data) {
                $userObj = $forecast->final_data->getUserByUserModel($authUser);
                if ($userObj) {
                    $submittedCount = collect($userObj->athletes ?? [])->filter(fn($a) => !empty($a->id))->count();
                    if ($forecast->status === ForecastStatusEnum::COMPLETED) {
                        $reg = $userObj->getPointsByType(AwardPointEnum::REGULAR_POINT);
                        $bon = $userObj->getPointsByType(AwardPointEnum::BONUS_POINT);
                        $userPoints = [
                            'regular' => $reg,
                            'bonus' => $bon,
                            'total' => $reg + $bon,
                        ];
                    }
                }
            }

            $forecast->auth_submitted_count = $submittedCount;
            $forecast->auth_is_ready = ($submittedCount === 6);
            $forecast->auth_points = $userPoints;

            // Winner & scores for completed races
            if ($forecast->status === ForecastStatusEnum::COMPLETED && $forecast->final_data) {
                $forecast->winner = $forecast->final_data->results[0] ?? null;
                $allScores = collect($forecast->final_data->users ?? [])->map(fn($u) => $u->getPointsByType(AwardPointEnum::REGULAR_POINT) + $u->getPointsByType(AwardPointEnum::BONUS_POINT));
                $forecast->top_score = $allScores->max() ?? 0;
                $forecast->player_count = $allScores->count();
            }

            // Determine gender / category for quick filtering
            $desc = $forecast->competition?->description ?? '';
            $cat = $forecast->competition?->cat_remote_id ?? '';
            $forecast->category_type = match(true) {
                str_ends_with($cat, 'M') || str_contains($desc, "Men's") || str_contains($desc, "Men ") => 'men',
                str_ends_with($cat, 'W') || str_contains($desc, "Women's") || str_contains($desc, "Women ") => 'women',
                default => 'relay',
            };

            return $forecast;
        });

        // 1. Upcoming forecasts: submit_deadline_at > now, sorted ASC (closest deadline first!)
        $upcomingForecasts = $forecasts->filter(fn($f) => $f->submit_deadline_at && $f->submit_deadline_at->gt($now))
            ->sortBy('submit_deadline_at')
            ->values();

        // Next race in focus (earliest upcoming deadline)
        $nextForecast = $upcomingForecasts->first();

        // 2. Live / In progress: deadline passed, but not marked COMPLETED yet
        $liveForecasts = $forecasts->filter(fn($f) => $f->submit_deadline_at && $f->submit_deadline_at->lte($now) && $f->status !== ForecastStatusEnum::COMPLETED)
            ->sortByDesc('submit_deadline_at')
            ->values();

        // 3. Completed: status == COMPLETED, sorted DESC (most recent first!)
        $completedForecasts = $forecasts->filter(fn($f) => $f->status === ForecastStatusEnum::COMPLETED)
            ->sortByDesc('submit_deadline_at')
            ->values();

        // 4. Group upcoming forecasts by Stage (event)
        $upcomingByStage = $upcomingForecasts->groupBy('competition.event_id');

        // 5. Total counts for tabs
        $stats = [
            'upcoming_count' => $upcomingForecasts->count(),
            'live_count' => $liveForecasts->count(),
            'completed_count' => $completedForecasts->count(),
            'user_ready_count' => $upcomingForecasts->where('auth_is_ready', true)->count(),
            'user_todo_count' => $upcomingForecasts->where('auth_is_ready', false)->count(),
        ];

        // 6. Available seasons for archive
        $availableSeasons = Season::whereIn('name', ['2627', '2526', '2425'])->orderBy('name', 'desc')->get();

        return view('forecasts.index', compact(
            'nextForecast',
            'upcomingForecasts',
            'upcomingByStage',
            'liveForecasts',
            'completedForecasts',
            'stats',
            'availableSeasons',
            'season'
        ));
    }
}
