<?php

use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Contr;

Route::get('/', Contr\Events\IndexController::class)->name('home');
Route::get('/events/{id}', Contr\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}', Contr\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes/{id}', Contr\Athletes\ShowController::class)->name('athletes.show');
Route::get('/forecasts/', Contr\Forecasts\IndexController::class)->name('forecasts.index');
Route::get('/forecasts/summary', Contr\Forecasts\Summary\IndexController::class)->name('forecasts.summary.index');
Route::get('/forecasts/{id}', Contr\Forecasts\ShowController::class)->name('forecasts.show');
Route::get('/forecasts/{id}/select-athlete/{place}/place', Contr\Forecasts\SelectAthleteController::class)->name('forecasts.select-athlete');



Route::group([
    'prefix' => 'private',
//    'middleware' => 'auth:web',
    'middleware' => AuthMiddleware::class,
], function(){
    Route::get('/forecasts/{id}/select-athlete/{place}/place/{athlete}/submit', Contr\Forecasts\SubmitSelectedAthleteController::class)->name('forecasts.select-athlete.submit');

    Route::get('/',Contr\Private\IndexController::class)->name('private.index');
    Route::get('/profile', Contr\Private\ProfileController::class)->name('private.profile');

    Route::get('/favorites/athletes/{id}/toggle', Contr\Favorites\AthleteToggleController::class)->name('favorites.toggle');

});
