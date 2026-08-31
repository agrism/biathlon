<?php

namespace App\Http\Controllers;

use App\Helpers\LinkHelper;
use App\Models\Event;
use App\Models\Season;
use App\Services\PenaltyLoopTweetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    public function __invoke(Request $request, LinkHelper $linkHelper, PenaltyLoopTweetService $tweetService): View
    {
        $this->registerBread('Home');

        $season = Season::query()->where('name', '2526')->first();

        $event = Event::query()
            ->where('season_id', $season?->id)
            ->where('level', 1)
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->first();

        $tweets = $tweetService->getPagedTweets(perPage: 1);

        return view('index', compact('tweets', 'season', 'event'));
    }
}
