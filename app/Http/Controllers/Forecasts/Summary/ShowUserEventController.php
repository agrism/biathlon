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
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\AthleteValueObject;
use App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowUserEventController extends Controller
{
    protected ?int $userId = null;
    protected ?int $authUserId = null;
    public function __invoke(Request $request, string $userId, string $eventId): View
    {
        $this->authUserId = auth()->id();

        $user = User::query()->where('id', $this->userId = $userId)->first();
        $event = Event::query()
            ->with(['competitions.forecast.awards.user'=> function($q): void{
                $q->where('id', $this->userId);
            }])
            ->with('competitions.results')
            ->where('id', $eventId)->first();

        if(!$user || !$event){
            abort(404);
        }

        $event->competitions->map(function(EventCompetition $competition):EventCompetition{
            $awards = $competition->forecast->awards->filter(function(ForecastAward $award):bool{
                return $award->user_id == $this->userId;
            });
            $competition->forecast->awards = $awards;

            if(!$this->authUserId){
                return $competition;
            }

            if(!$authUserAthletes = collect($competition->forecast->final_data->users)->where('id', $this->authUserId)->first()?->athletes){
                return $competition;
            }

            $isAllAthletesSubmitted =collect($authUserAthletes)->filter(function(AthleteValueObject $athlete):bool{
                return $athlete->id !== null;
            })->count() > 5;

            $competition->forecast->isAllAthletesSubmitted = $isAllAthletesSubmitted;

            return $competition;
        });

        return view('forecasts.summary.show-user-event', compact('event', 'user'));
    }
}
