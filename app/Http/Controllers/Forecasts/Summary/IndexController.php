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
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{

    protected LinkHelper $linkHelper;

    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $this->linkHelper = $linkHelper;

        $this->registerBread('Summary');

        $season = Season::query()
            ->with(['events'=> function( $q){
                $q->with('competitions.forecast.awards.user')->where('level', 1);
            }])
            ->where('name', '2425')
            ->first();

        $users = User::query()->get();

        $data = [];

        /** @var User $user */
        foreach ($users as $user){
            $data['users'][$user->name] = [
                'name' => $user->name,
                'id' => $user->id,
                'events' => [],
            ];
        }

        $data['events'][] = 'User';

        /** @var Event $event */
        foreach ($season->events as $event){

            $data['events'][] = $event->title();


            $userAwardsInEvent = [];

            /** @var EventCompetition $competition */
            foreach ($event->competitions->sortBy('start_time') as $competition){
                /** @var ForecastAward $award */
                foreach ($competition->forecast->awards as $award) {
                    $prev =  $userAwardsInEvent[$award->user->name][$award->type->value] ?? 0;
                    $userAwardsInEvent[$award->user->name][$award->type->value] = $prev + $award->points;
                }
            }

            /** @var User $user */
            foreach ($users as $user) {
                if($userAwardsInEvent[$user->name] ?? null){
                    $data['users'][$user->name]['events'][] = [
                        'regular' => $regular = $userAwardsInEvent[$user->name][AwardPointEnum::REGULAR_POINT->value] ?? 0,
                        'bonus' => $bonus = $userAwardsInEvent[$user->name][AwardPointEnum::BONUS_POINT->value] ?? 0,
                        'eventId' => $event->id,
                    ];
                } else {
                    $data['users'][$user->name]['events'][] = [
                        'regular' => $regular = 0,
                        'bonus' => $bonus = 0,
                        'eventId' => $event->id,
                    ];
                }


                $key = sprintf('%s.%s.%s.%s', 'users', $user->name,'total', AwardPointEnum::REGULAR_POINT->value);
                data_set($data, $key,data_get($data, $key, 0) + $regular);

                $key = sprintf('%s.%s.%s.%s', 'users', $user->name, 'total',AwardPointEnum::BONUS_POINT->value);
                data_set($data, $key,data_get($data, $key, 0) + $bonus);

                $key = sprintf('%s.%s.%s.%s', 'users', $user->name, 'total','total');
                data_set($data, $key,data_get($data, $key, 0) + $bonus + $regular);

            }
        }

        $key = sprintf('%s.%s', 'total', 'total');

        usort($data['users'], function ($a, $b) use ($key){
            return data_get($a, $key, 0) < data_get($b, $key, 0);
        });

        $data['events'][] = 'Points';
        $data['events'][] = 'Bonus';
        $data['events'][] = 'Total';

        foreach ($data['users'][0]['events'] ?? [] as $eventIndex => $event){
            $maxPoints = 0;
            $winnerUserIndexes = [];
            foreach ($data['users'] ?? [] as $userIndex => $user){
                $userPoints = ($user['events'][$eventIndex]['regular'] ?? 0) + ($user['events'][$eventIndex]['bonus'] ?? 0);

                if($userPoints === 0){
                    continue;
                } elseif($userPoints == $maxPoints){
                    $winnerUserIndexes[] = $userIndex;
                } elseif($userPoints > $maxPoints){
                    $winnerUserIndexes = [$userIndex];
                }

                $maxPoints = max($userPoints, $maxPoints);
            }

            foreach ($winnerUserIndexes as $winnerUserIndex){
                $data['users'][$winnerUserIndex]['events'][$eventIndex]['winner'] = true;
            }
        }

        return view('forecasts.summary.index', compact('data'));
    }
}
