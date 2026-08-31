<?php

namespace App\Http\Controllers\Athletes;

use App\Http\Controllers\Controller;
use App\Models\Athlete;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultsController extends Controller
{
    public function __invoke(Request $request, string $id): View
    {
        $athlete = Athlete::query()->where(is_numeric($id) ? 'id' : 'ibu_id', $id)->first();

        if (!$athlete) {
            $athlete = Athlete::query()->where('ibu_id', $id)->orWhere('id', $id)->firstOrFail();
        }

        $results = $athlete->results()
            ->join('event_competitions', 'event_competitions.id', '=', 'event_competition_results.event_competition_id')
            ->select('event_competition_results.*')
            ->with(['competition.event'])
            ->orderByDesc('event_competitions.start_time')
            ->orderByDesc('event_competition_results.id')
            ->paginate(10);

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        return view('athletes.partials.result-rows', compact('athlete', 'results'));
    }
}
