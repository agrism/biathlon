<?php

namespace App\Http\Controllers\Twitter;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FetchController extends Controller
{
    public function __invoke(Request $request, BiathlonTweetService $service): JsonResponse
    {
        $before = Tweet::count();
        $startTime = microtime(true);
        $summary = [];

        $service->syncTweets(function (string $event, array $data) use (&$summary) {
            if ($event === 'finished') {
                $summary[] = $data['result'];
            }
        });

        $after = Tweet::count();
        $duration = round(microtime(true) - $startTime, 2);

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'duration_seconds' => $duration,
            'tweets_before' => $before,
            'tweets_after' => $after,
            'newly_added' => max(0, $after - $before),
            'providers' => $summary,
        ]);
    }
}
