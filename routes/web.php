<?php

use App\Http\Middleware\AuthMiddleware;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Contr;

Route::get('/', Contr\IndexController::class)->name('home');
Route::get('/events', Contr\Events\IndexController::class)->name('events.index');
Route::get('/events/{id}', Contr\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}/{showContentOnly?}',
    Contr\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes', Contr\Athletes\IndexController::class)->name('athletes.index');
Route::get('/athletes/{id}', Contr\Athletes\ShowController::class)->name('athletes.show');
Route::get('/forecasts/', Contr\Forecasts\IndexController::class)->name('forecasts.index');
Route::get('/forecasts/summary', Contr\Forecasts\Summary\IndexController::class)->name('forecasts.summary.index');
Route::get('/forecasts/summary/{userId}/{eventId}/show',
    Contr\Forecasts\Summary\ShowUserEventController::class)->name('forecasts.summary.user-event');
Route::get('/forecasts/{id}/{showContentOnly?}', Contr\Forecasts\ShowController::class)->name('forecasts.show');
Route::get('/forecasts/{id}/select-athlete/{place}/place',
    Contr\Forecasts\SelectAthleteController::class)->name('forecasts.select-athlete');

//Route::get('/tweet', Contr\Twitter\IndexController::class)->name('twitter.index');
//Route::get('/api/tweets', Contr\Twitter\FetchController::class)->name('twitter.fetch');

Route::group([
    'prefix' => 'private',
//    'middleware' => 'auth:web',
    'middleware' => AuthMiddleware::class,
], function () {
    Route::get('/forecasts/{id}/select-athlete/{place}/place/{athlete}/submit',
        Contr\Forecasts\SubmitSelectedAthleteController::class)->name('forecasts.select-athlete.submit');
    Route::get('/forecasts/{id}/select-athlete/{place}/place/move/{direction}',
        Contr\Forecasts\MoveUpDownPlaceController::class)
        ->where('direction', 'up|down')
        ->name('forecasts.select-athlete.place.move.up-down');

    Route::get('/forecasts/{id}/select-athlete/{place}/place/hide', Contr\Forecasts\HidePlaceController::class)
        ->name('forecasts.select-athlete.place.hide');

    Route::get('/forecasts/{id}/submit-status', Contr\Forecasts\AuthUserSubmitStatusController::class)
        ->name('forecasts.submit-status');

    Route::get('/', Contr\Private\IndexController::class)->name('private.index');
    Route::get('/profile', Contr\Private\ProfileController::class)->name('private.profile');

    Route::get('/favorites/athletes/{id}/toggle',
        Contr\Favorites\AthleteToggleController::class)->name('favorites.toggle');
});


//Route::get('test', function (){
//    $a = (new \App\Services\BiathlonResultApi)->athlete('BTFRA11608199201');
//
//    dd($a->json());
//});
//
//Route::get('test2', function (){
//    $a = (new \App\Services\BiathlonResultApi)->athlete('BTITA20402199501');
//
//    dd($a->json());
//});
//
//Route::get('test3', function (){
//    $a = (new \App\Services\BiathlonResultApi)->athlete('BTNOR11605199301');
//
//    dump($a->json());
//    dump($a->json()['RNKS']);
//    dd($a->json()['Badges']);
//});
//
//Route::get('test2.gro', function (){
//    $a = (new \App\Services\BiathlonResultApi)->athlete('BTGER22503200401');
//
//    return $a->json();
//});
//
//Route::get('test2.grod', function (){
//    dump('GROTIAN Selina');
//
//    $a = (new \App\Services\BiathlonResultApi)->athlete('BTGER22503200401');
//
//    dd($a->json());
//});

Route::get('/test-dainis', function (Request $request){

    $dainisCalc = new \App\Helpers\Forecasts\ForecastDainisServiceHelper;
    $dainisCalc->overrideMatrix($request->all());

    $getRank = function (int $provided, array $all){
        rsort($all);
        $place = 0;
        foreach ($all as $item){
            $place +=1;
            if($provided !== intval($item)){
                continue;
            }
            break;

        }
        return $place;
    };


    $forecasts = \App\Models\Forecast::query()->where('status',
        \App\Enums\Forecast\ForecastStatusEnum::COMPLETED)->get();

    $return = [];

    $users = \App\Models\User::query()->get();

    $userTotalPoints = [];

    foreach ($users as $user) {
        $userTotalPoints[$user->name]['old'] = 0;
        $userTotalPoints[$user->name]['new'] = 0;
    }

//    dump($forecasts);
    foreach ($forecasts as $forecast) {
        /** @var \App\Models\Forecast $forecast */
//        dd($forecast->final_data);


        $returnItem = [];

        $returnItem['name'] = $forecast->competition->description.', at: '.$forecast->competition->start_time?->format('m/Y');
        $returnItem['url'] = route('forecasts.show', $forecast->id);


        foreach ($users as $user) {

            $dainisCalc->calculateUserPoints(forecast: $forecast, user: $user);

            $userTotalPoints[$user->name]['old'] += $oldRegular = $forecast->awards
                ->where('user_id', $user->id)
                ->where('type', \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT)->first()?->points;

            $userTotalPoints[$user->name]['old'] += $oldBonus = $forecast->awards
                ->where('user_id', $user->id)
                ->where('type', \App\Enums\Forecast\AwardPointEnum::BONUS_POINT)->first()?->points;

            $userTotalPoints[$user->name]['new'] += $newRegular = $dainisCalc->getMainPoints();
            $userTotalPoints[$user->name]['new'] += $newBonus = $dainisCalc->getBonusPoints();

            $returnItem['users'][] = [
                'name' => $user->name,
                'points' => [
                    [
                        'type' => $forecast->type->name,
                        'points' => [
                            'regular' => $oldRegular,
                            'bonus' => $oldBonus,
                            'total' => $oldRegular +  $oldBonus,
                        ],
                        'rank' => null,
                    ],
                    [
                        'type' => \App\Enums\Forecast\ForecastTypeEnum::FORECAST_DAINIS_SCHEMA->name,
                        'points' => [
                            'regular' => $newRegular,
                            'bonus' => $newBonus,
                            'total' => $newRegular + $newBonus,
                        ],
                        'rank' => null,
                        'rankIncrease' => null,
                    ]

                ]
            ];
        }

        $userOldPoints = [];
        $userNewPoints = [];

        foreach ($returnItem['users'] as $u){
            $userOldPoints[data_get($u, 'name')] = data_get($u, 'points.0.points.total', 0);
            $userNewPoints[data_get($u, 'name')] = data_get($u, 'points.1.points.total', 0);
        }

//        dd($userOldPoints, $userNewPoints);

        foreach ($returnItem['users'] as $i => $u){
            data_set($returnItem, 'users.'.$i.'.points.0.rank', $oldRank= $getRank(
                provided: data_get($userOldPoints, data_get($u, 'name')),
                all:  $userOldPoints),
            );

            data_set($returnItem, 'users.'.$i.'.points.1.rank', $newRank = $getRank(
                provided: data_get($userNewPoints, data_get($u, 'name')),
                all:  $userNewPoints),
            );

            data_set($returnItem, 'users.'.$i.'.points.1.rankIncrease', $oldRank - $newRank);
        }

        $return[] = $returnItem;
    }

    $return[] = $userTotalPoints;

    if($request->input('mode') == 'table' || true){


        echo '<form action="test-dainis">';
        echo '<input type="hidden" name="mode" value="table">';
        echo '<table>';
        echo '<thead><tr><th>Description</th><th>Individual points</th><th>Team points</th></tr></thead>';
        echo '<tbody>';

        foreach ([0,1,2,3,4,5] as $diff){
            echo '<tr style="background-color: #8bf1be"><td>regular, diff '.$diff.'</td><td><input name="regular[individual]['.$diff.']" type="text" value="'.data_get($dainisCalc->getMatrix(), 'regular.individual.'.$diff).'"></td><td><input name="regular[team]['.$diff.']" value="'.data_get($dainisCalc->getMatrix(), 'regular.team.'.$diff).'" type="text"></td></tr>';
        }

        foreach (['gold','silver','bronze'] as $diff){
            echo '<tr style="background-color: #e5e7eb"><td>Bonus '.$diff.'</td><td><input name="bonus[individual]['.$diff.']" type="text" value="'.data_get($dainisCalc->getMatrix(), 'bonus.individual.'.$diff).'"></td><td><input name="bonus[team]['.$diff.']" value="'.data_get($dainisCalc->getMatrix(), 'bonus.team.'.$diff).'" type="text"></td></tr>';
        }

        echo '<tr><td colspan="10" style="text-align: right"><a href="/test-dainis"><button type="button" style="margin-right: 5px">Reset to default</button></a><button>Submit updated matrix</button></td></tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
        echo '<hr>';




        echo <<<HTML
<style>
    table {
            border-collapse: collapse;
    }
    td:nth-child(n+2) {
        text-align: right;
    }
    td, th {
        padding: 5px;
        border: 1px solid black;
    }
    input {
        text-align: right;
    }
</style>
HTML;

        echo '<table>';
        echo '<thead><tr><th></th><th>Agris</th><th>Grey</th><th>Dainis</th><th>Andris</th><th>Total</th></tr></thead>';
        echo '<tbody>';
        $oldTotal =0;
        $oldTotal += $agrisOld = data_get($userTotalPoints, 'Agris.old');
        $oldTotal += $greyOld = data_get($userTotalPoints, 'Grey.old');
        $oldTotal += $dainisOld = data_get($userTotalPoints, 'Dainis.old');
        $oldTotal += $andrisOld = data_get($userTotalPoints, 'Andris.old');
        echo '<tr><td>Old sys</td><td>'.number_format($agrisOld).'</td><td>'.number_format($greyOld).'</td><td>'.number_format($dainisOld).'</td><td>'.number_format($andrisOld).'</td><td>'.number_format($oldTotal).'</td></tr>';
        $oldTotalPercent = 0;
        $oldTotalPercent += $agrisOldPerc = $agrisOld / $oldTotal * 100;
        $oldTotalPercent += $greyOldPerc = $greyOld / $oldTotal * 100;
        $oldTotalPercent += $dainisOldPerc = $dainisOld / $oldTotal * 100;
        $oldTotalPercent += $andrisOldPerc = $andrisOld / $oldTotal * 100;

        $all = [
            $agrisOld, $dainisOld, $greyOld, $andrisOld,
        ];
        echo '<tr><td>Old sys %</td><td>'.round($agrisOldPerc,2).'%</td><td>'.round($greyOldPerc, 2).'%</td><td>'.round($dainisOldPerc, 2).'%</td><td>'.round($andrisOldPerc,2).'%</td><td>'.round($oldTotalPercent,2).'%</td></tr>';
        echo '<tr><td>Old sys rank</td><td>'.$getRank(provided: $agrisOld,all:  $all).'</td><td>'.$getRank(provided: $greyOld,all:  $all).'</td><td>'.$getRank(provided: $dainisOld,all:  $all).'</td><td>'.$getRank(provided: $andrisOld,all:  $all).'</td><td></td></tr>';


        echo '<tr><td colspan="100">&nbsp;</td></tr>';

        $newTotal =0;
        $newTotal += $agrisNew = data_get($userTotalPoints, 'Agris.new');
        $newTotal += $greyNew = data_get($userTotalPoints, 'Grey.new');
        $newTotal += $dainisNew = data_get($userTotalPoints, 'Dainis.new');
        $newTotal += $andrisNew = data_get($userTotalPoints, 'Andris.new');
        echo '<tr><td>New sys</td><td>'.number_format($agrisNew).'</td><td>'.number_format($greyNew).'</td><td>'.number_format($dainisNew).'</td><td>'.number_format($andrisNew).'</td><td>'.number_format($newTotal).'</td></tr>';
        $newTotalPercent = 0;
        $newTotalPercent += $agrisNewPerc = $agrisNew / $newTotal * 100;
        $newTotalPercent += $greyNewPerc = $greyNew / $newTotal * 100;
        $newTotalPercent += $dainisNewPerc = $dainisNew / $newTotal * 100;
        $newTotalPercent += $andrisNewPerc = $andrisNew / $newTotal * 100;
        echo '<tr><td>New sys %</td><td>'.round($agrisNewPerc,2).'%</td><td>'.round($greyNewPerc, 2).'%</td><td>'.round($dainisNewPerc, 2).'%</td><td>'.round($andrisNewPerc,2).'%</td><td>'.round($newTotalPercent,2).'%</td></tr>';
        $all = [$agrisNew, $greyNew, $dainisNew, $andrisNew];
        echo '<tr><td>New sys rank</td><td>'.$getRank(provided: $agrisNew,all:  $all).'</td><td>'.$getRank(provided: $greyNew,all:  $all).'</td><td>'.$getRank(provided: $dainisNew,all:  $all).'</td><td>'.$getRank(provided: $andrisNew,all:  $all).'</td><td></td></tr>';
        echo '<tr><td colspan="100">&nbsp;</td></tr>';

        echo '<tr><td>%, delta (old-new)</td><td>'.round($agrisOldPerc-$agrisNewPerc,2).'%</td><td>'.round($greyOldPerc - $greyNewPerc, 2).'%</td><td>'.round($dainisOldPerc-$dainisNewPerc, 2).'%</td><td>'.round($andrisOldPerc-$andrisNewPerc,2).'%</td><td></td></tr>';


        echo '</tbody>';
        echo '</table>';


        // table2

        echo '<hr>';

        echo '<table>';
            echo '<thead>';
                echo '<tr>';
                foreach (['Agris', 'Grey', 'Dainis', 'Andris'] as $name){
                    echo '<th colspan="3">'.$name.'</th>';
                }
                echo '</tr>';
                echo '<tr>';
                foreach (['Agris', 'Grey', 'Dainis', 'Andris'] as $name){
                    echo '<th>old place</th>';
                    echo '<th>new place</th>';
                    echo '<th>up</th>';
                }
                echo '</tr>';
            echo '</thead>';

            echo '<tbody>';
            foreach ($return as $forecast){
                echo '<tr style="background-color: #c3f8dd;">';
                echo '<td colspan="100"><a href="'.(data_get($forecast, 'url')).'" target="_blank">'.data_get($forecast, 'name').'</a></td>';
                echo '</tr>';
                echo '<tr>';
                foreach (data_get($forecast, 'users', []) as $user){
                    echo '<td>'.data_get($user, 'points.0.rank').'</td>';
                    echo '<td>'.data_get($user, 'points.1.rank').'</td>';
                    $rankIncrease = data_get($user, 'points.1.rankIncrease');
                    echo '<td style="background-color: '.($rankIncrease > 0 ? 'green' : ($rankIncrease < 0 ? 'red': '')).'">'.data_get($user, 'points.1.rankIncrease').'</td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';
        echo '</table>';
    }
    echo '<hr>';

    echo '<pre>';
    echo json_encode($return, JSON_PRETTY_PRINT);
    echo '</pre>';



//    return $return;
});











