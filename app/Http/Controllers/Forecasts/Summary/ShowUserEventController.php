<?php

namespace App\Http\Controllers\Forecasts\Summary;

use App\Enums\Forecast\AwardPointEnum;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCompetition;
use App\Models\Forecast;
use App\Models\ForecastAward;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowUserEventController extends Controller
{
    public function __invoke(Request $request, string $userId, string $eventId): View
    {
        $user = User::query()->where('id', $userId)->first();
        $event = Event::query()
            ->with(['competitions.forecast.awards.user'=> function($q)use($userId): void{
                $q->where('id', $userId);
            }])
            ->whereHas('competitions.forecast.awards.user', function($q)use($userId): void{
                $q->where('id', $userId);
            })
            ->where('id', $eventId)->first();

        if(!$user || !$event){
            abort(404);
        }

        return view('forecasts.summary.show-user-event', compact('event', 'user'));
    }
}
