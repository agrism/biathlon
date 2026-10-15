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

        $seasons = [];

        $loopOneEvent = Event::query()->where('event_remote_id', 'SB2627SIBUL1MU')->first();
        if ($loopOneEvent) {
            $seasons[] = [
                'id' => 's_loop_one',
                'title' => 'Loop One Festival Munich 2026 — Pre-Season Special',
                'subtitle' => 'Super Sprint Exhibition Tournament • Olympiapark Munich (17–18 Oct 2026)',
                'icon' => '⚡',
                'badge' => 'Pre-Season Show',
                'gradient' => 'from-violet-950 via-slate-900 to-sky-950',
                'icon_bg' => 'bg-violet-500/20 border-violet-400/30 text-violet-300',
                'data' => $this->getEventSummaryData(eventId: $loopOneEvent->id),
            ];
        }

        // Only show season 26/27 starting from Monday, 19 October 2026 (after Loop One Festival)
        if (now('Europe/Riga')->gte('2026-10-19 00:00:00')) {
            $seasons[] = [
                'id' => 's2627',
                'title' => 'LaVita IBU World Cup Biathlon, season 26/27',
                'subtitle' => 'Official World Cup Season Matrix',
                'icon' => '🏆',
                'data' => $this->getSeasonData(season: '2627'),
            ];
        }

        $seasons[] = [
            'id' => 's2526',
            'title' => 'BMW IBU World Cup Biathlon, season 25/26',
            'subtitle' => 'Official World Cup Season Matrix',
            'icon' => '🏆',
            'data' => $this->getSeasonData(season: '2526'),
        ];

        $seasons[] = [
            'id' => 'id2425',
            'title' => 'BMW IBU World Cup Biathlon, season 24/25',
            'subtitle' => 'Official World Cup Season Matrix',
            'icon' => '🏆',
            'data' => $this->getSeasonData(season: '2425'),
        ];

        return view('forecasts.summary.index', compact('seasons'));
    }

    protected function getEventSummaryData(int $eventId): array
    {
        $event = Event::query()
            ->with(['competitions' => function ($q) {
                $q->with('forecast.awards.user')->orderBy('start_time');
            }])
            ->find($eventId);

        if (!$event) {
            return [];
        }

        $users = User::query()->get();
        $data = [];

        foreach ($users as $user) {
            $data['users'][$user->name] = [
                'name' => $user->name,
                'id' => $user->id,
                'events' => [],
            ];
        }

        $data['events'][] = 'User';

        foreach ($event->competitions as $competition) {
            // Keep ONLY the Finals (Saturday & Sunday Women's and Men's Finals)
            if (!str_contains($competition->description, 'Final') && !str_contains($competition->short_description, 'Final')) {
                continue;
            }

            $day = $competition->start_time ? $competition->start_time->tz('Europe/Riga')->format('D d.m') : '';
            $baseName = $competition->short_description ?: $competition->description;
            $compName = $baseName . ($day ? ' (' . $day . ')' : '');
            $data['events'][] = $compName;

            $userAwardsInComp = [];
            foreach ($competition->forecast->awards ?? [] as $award) {
                $prev = $userAwardsInComp[$award->user->name][$award->type->value] ?? 0;
                $userAwardsInComp[$award->user->name][$award->type->value] = $prev + $award->points;
            }

            foreach ($users as $user) {
                if (isset($userAwardsInComp[$user->name])) {
                    $regular = $userAwardsInComp[$user->name][AwardPointEnum::REGULAR_POINT->value] ?? 0;
                    $bonus = $userAwardsInComp[$user->name][AwardPointEnum::BONUS_POINT->value] ?? 0;
                } else {
                    $regular = 0;
                    $bonus = 0;
                }

                $data['users'][$user->name]['events'][] = [
                    'regular' => $regular,
                    'bonus' => $bonus,
                    'eventId' => $event->id,
                    'competitionId' => $competition->id,
                    'forecastId' => $competition->forecast?->id,
                ];

                $key = sprintf('users.%s.total.%s', $user->name, AwardPointEnum::REGULAR_POINT->value);
                data_set($data, $key, data_get($data, $key, 0) + $regular);

                $key = sprintf('users.%s.total.%s', $user->name, AwardPointEnum::BONUS_POINT->value);
                data_set($data, $key, data_get($data, $key, 0) + $bonus);

                $key = sprintf('users.%s.total.total', $user->name);
                data_set($data, $key, data_get($data, $key, 0) + $bonus + $regular);
            }
        }

        $key = 'total.total';
        usort($data['users'], function ($a, $b) use ($key) {
            $totalA = data_get($a, $key, 0);
            $totalB = data_get($b, $key, 0);
            if ($totalA === $totalB) {
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            }
            return $totalB <=> $totalA;
        });

        $data['events'][] = 'Points';
        $data['events'][] = 'Bonus';
        $data['events'][] = 'Total';
        $data['events'][] = 'Diff';

        foreach ($data['users'][0]['events'] ?? [] as $compIndex => $compItem) {
            $maxPoints = 0;
            $winnerUserIndexes = [];
            foreach ($data['users'] ?? [] as $userIndex => $user) {
                $userPoints = ($user['events'][$compIndex]['regular'] ?? 0) + ($user['events'][$compIndex]['bonus'] ?? 0);
                if ($userPoints === 0) {
                    continue;
                } elseif ($userPoints == $maxPoints) {
                    $winnerUserIndexes[] = $userIndex;
                } elseif ($userPoints > $maxPoints) {
                    $winnerUserIndexes = [$userIndex];
                }
                $maxPoints = max($userPoints, $maxPoints);
            }

            foreach ($winnerUserIndexes as $winnerUserIndex) {
                $data['users'][$winnerUserIndex]['events'][$compIndex]['winner'] = true;
            }
        }

        $maxTotalPoints = 0;
        foreach ($data['users'] as $user) {
            $total = data_get($user, 'total.total', 0);
            if ($total > $maxTotalPoints) {
                $maxTotalPoints = $total;
            }
        }

        $seasonStarted = $maxTotalPoints > 0;
        $data['season_started'] = $seasonStarted;

        $currentRank = 1;
        $prevPoints = null;

        foreach ($data['users'] as $index => $user) {
            $userTotal = data_get($user, 'total.total', 0);

            if ($prevPoints !== null && $userTotal < $prevPoints) {
                $currentRank = $index + 1;
            }
            $prevPoints = $userTotal;

            $diff = $seasonStarted ? max(0, $maxTotalPoints - $userTotal) : 0;
            $isLeader = $seasonStarted && ($userTotal === $maxTotalPoints);

            data_set($data, 'users.'.$index.'.rank', $currentRank);
            data_set($data, 'users.'.$index.'.is_leader', $isLeader);
            data_set($data, 'users.'.$index.'.total.diff', $diff);
        }

        return $data;
    }

    protected function getSeasonData(string $season): array
    {
        $season = Season::query()
            ->with(['events'=> function( $q){
                $q->with('competitions.forecast.awards.user')->where('level', 1)->orderBy('start_date');
            }])
            ->where('name', $season)
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
                foreach ($competition->forecast->awards ?? [] as $award) {
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
            $totalA = data_get($a, $key, 0);
            $totalB = data_get($b, $key, 0);
            if ($totalA === $totalB) {
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            }
            return $totalB <=> $totalA;
        });

        $data['events'][] = 'Points';
        $data['events'][] = 'Bonus';
        $data['events'][] = 'Total';
        $data['events'][] = 'Diff';

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

        // Add point diff, rank, and leader status
        $maxTotalPoints = 0;
        foreach ($data['users'] as $user) {
            $total = data_get($user, 'total.total', 0);
            if ($total > $maxTotalPoints) {
                $maxTotalPoints = $total;
            }
        }

        $seasonStarted = $maxTotalPoints > 0;
        $data['season_started'] = $seasonStarted;

        $currentRank = 1;
        $prevPoints = null;

        foreach ($data['users'] as $index => $user) {
            $userTotal = data_get($user, 'total.total', 0);

            if ($prevPoints !== null && $userTotal < $prevPoints) {
                $currentRank = $index + 1;
            }
            $prevPoints = $userTotal;

            $diff = $seasonStarted ? max(0, $maxTotalPoints - $userTotal) : 0;
            $isLeader = $seasonStarted && ($userTotal === $maxTotalPoints);

            data_set($data, 'users.'.$index.'.rank', $currentRank);
            data_set($data, 'users.'.$index.'.is_leader', $isLeader);
            data_set($data, 'users.'.$index.'.total.diff', $diff);
        }

        return $data;
    }
}
