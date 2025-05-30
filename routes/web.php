<?php

use App\Http\Middleware\AuthMiddleware;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Contr;

Route::get('/', Contr\IndexController::class)->name('home');
Route::get('/events', Contr\Events\IndexController::class)->name('events.index');
Route::get('/events/{id}', Contr\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}/{showContentOnly?}', Contr\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes', Contr\Athletes\IndexController::class)->name('athletes.index');
Route::get('/athletes/{id}', Contr\Athletes\ShowController::class)->name('athletes.show');
Route::get('/forecasts/', Contr\Forecasts\IndexController::class)->name('forecasts.index');
Route::get('/forecasts/summary', Contr\Forecasts\Summary\IndexController::class)->name('forecasts.summary.index');
Route::get('/forecasts/summary/{userId}/{eventId}/show', Contr\Forecasts\Summary\ShowUserEventController::class)->name('forecasts.summary.user-event');
Route::get('/forecasts/{id}/{showContentOnly?}', Contr\Forecasts\ShowController::class)->name('forecasts.show');
Route::get('/forecasts/{id}/select-athlete/{place}/place', Contr\Forecasts\SelectAthleteController::class)->name('forecasts.select-athlete');

//Route::get('/tweet', Contr\Twitter\IndexController::class)->name('twitter.index');
//Route::get('/api/tweets', Contr\Twitter\FetchController::class)->name('twitter.fetch');

Route::group([
    'prefix' => 'private',
//    'middleware' => 'auth:web',
    'middleware' => AuthMiddleware::class,
], function(){
    Route::get('/forecasts/{id}/select-athlete/{place}/place/{athlete}/submit', Contr\Forecasts\SubmitSelectedAthleteController::class)->name('forecasts.select-athlete.submit');
    Route::get('/forecasts/{id}/select-athlete/{place}/place/move/{direction}', Contr\Forecasts\MoveUpDownPlaceController::class)
        ->where('direction', 'up|down')
        ->name('forecasts.select-athlete.place.move.up-down');

    Route::get('/forecasts/{id}/select-athlete/{place}/place/hide', Contr\Forecasts\HidePlaceController::class)
        ->name('forecasts.select-athlete.place.hide');

    Route::get('/forecasts/{id}/submit-status', Contr\Forecasts\AuthUserSubmitStatusController::class)
        ->name('forecasts.submit-status');

    Route::get('/',Contr\Private\IndexController::class)->name('private.index');
    Route::get('/profile', Contr\Private\ProfileController::class)->name('private.profile');

    Route::get('/favorites/athletes/{id}/toggle', Contr\Favorites\AthleteToggleController::class)->name('favorites.toggle');

});


Route::get('test', function (){
    $a = (new \App\Services\BiathlonResultApi)->athlete('BTFRA11608199201');

    dd($a->json());
});

Route::get('test2', function (){
    $a = (new \App\Services\BiathlonResultApi)->athlete('BTITA20402199501');

    dd($a->json());
});

Route::get('test3', function (){
    $a = (new \App\Services\BiathlonResultApi)->athlete('BTNOR11605199301');

    dump($a->json());
    dump($a->json()['RNKS']);
    dd($a->json()['Badges']);
});

Route::get('test2.gro', function (){
    $a = (new \App\Services\BiathlonResultApi)->athlete('BTGER22503200401');

    return $a->json();
});

Route::get('test2.grod', function (){
    dump('GROTIAN Selina');

    $a = (new \App\Services\BiathlonResultApi)->athlete('BTGER22503200401');

    dd($a->json());
});




Route::get('test4', function (){

    $event = Event::query()
        ->where('level', 1)
        ->where('start_date', '<', now())
        ->where('end_date', '>', now())
        ->with('competitions.results.athlete')
        ->first();

    dd($event->competitions->pluck('results.athlete'));
});









