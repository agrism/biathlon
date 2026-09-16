<?php

namespace App\Http\Controllers\Twitter;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ToggleHideController extends Controller
{
    public function __invoke(Request $request, int|string $id): View
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $tweet = Tweet::query()->findOrFail($id);
        $tweet->should_hide = !$tweet->should_hide;
        $tweet->save();

        // Flush relevant caches
        Cache::forget('biathlon_latest_tweets_6');
        Cache::forget('biathlon_latest_tweets_12');
        Cache::forget('biathlon_latest_tweets_3');
        Cache::forget('biathlon_latest_tweets_6_admin');
        Cache::forget('biathlon_latest_tweets_12_admin');
        Cache::forget('biathlon_latest_tweets_3_admin');
        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        return view('twitter.partials.hide-toggle', compact('tweet'));
    }
}
