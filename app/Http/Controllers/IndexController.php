<?php

namespace App\Http\Controllers;

use App\Helpers\LinkHelper;
use App\Models\Event;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{

    protected LinkHelper $linkHelper;
    public function __invoke(Request $request, LinkHelper $linkHelper): View
    {
        $season = Season::query()->where('name', '2526')->first();

        $event = Event::query()
            ->where('season_id', $season?->id)
            ->where('start_date', '>', now())
            ->first();

        if($event){
            return view('index', compact('event', 'season'));
        }

        return app(\App\Http\Controllers\Events\IndexController::class)( $request, $linkHelper);
    }
}
