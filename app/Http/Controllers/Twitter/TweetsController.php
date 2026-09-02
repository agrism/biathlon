<?php

namespace App\Http\Controllers\Twitter;

use App\Http\Controllers\Controller;
use App\Services\PenaltyLoopTweetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TweetsController extends Controller
{
    public function __invoke(Request $request, PenaltyLoopTweetService $service): View
    {
        $tweets = $service->getPagedTweets();

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        return view('twitter.partials.tweet-cards', compact('tweets'));
    }
}
