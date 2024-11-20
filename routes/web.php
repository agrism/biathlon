<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Contr;

Route::get('/', Contr\Events\IndexController::class)->name('home');
Route::get('/events/{id}', Contr\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}', Contr\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes/{id}', Contr\Athletes\ShowController::class)->name('athletes.show');

