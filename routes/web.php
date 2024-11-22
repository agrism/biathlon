<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Contr;

Route::get('/', Contr\Events\IndexController::class)->name('home');
Route::get('/events/{id}', Contr\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}', Contr\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes/{id}', Contr\Athletes\ShowController::class)->name('athletes.show');

Route::group(['prefix' => 'private','middleware' => 'auth:web'], function(){
    Route::get('/',Contr\Private\IndexController::class)->name('private.index');
    Route::get('/profile', Contr\Private\ProfileController::class)->name('private.profile');
});
