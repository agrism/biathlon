<?php

namespace App\Http\Controllers\Twitter;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SetAthleteController extends Controller
{
    public function __invoke(Request $request, int|string $id, BiathlonTweetService $tweetService): View|Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $tweet = Tweet::with('mentionedAthlete')->find($id);
        if (!$tweet) {
            return response('<div class="text-xs text-slate-400 italic py-1">Post not found</div>', 404);
        }

        $query = $request->input('athlete_name');

        if ($query === null || trim((string)$query) === '' || in_array(strtolower(trim((string)$query)), ['none', 'clear', 'remove', 'null', '0', '-1'])) {
            // Explicitly unset athlete
            $tweet->mentioned_athlete_id = 0;
            $tweet->save();
        } else {
            $athlete = $tweetService->findAthleteByAdminQuery($query);
            if ($athlete) {
                $tweet->mentioned_athlete_id = $athlete->id;
                $tweet->save();
            }
        }

        // Reload relationship for fresh view rendering
        $tweet->load('mentionedAthlete');

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
        Cache::forget('tweet_athlete_id_' . $tweet->id);

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        return view('twitter.partials.single-card', compact('tweet'));
    }
}
