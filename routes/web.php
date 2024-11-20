<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Http\Controllers\Events\IndexController::class)->name('home');
Route::get('/events/{id}', \App\Http\Controllers\Events\ShowController::class)->name('events.show');
Route::get('/competitions/{id}', \App\Http\Controllers\Competitions\ShowController::class)->name('competitions.show');
Route::get('/athletes/{id}', \App\Http\Controllers\Athletes\ShowController::class)->name('athletes.show');

