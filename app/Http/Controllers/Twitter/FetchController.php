<?php

namespace App\Http\Controllers\Twitter;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FetchController extends Controller
{
    protected LinkHelper $linkHelper;
    public function __invoke(Request $request): View
    {
        if (Cache::has('tweets')) {
            return response()->json(Cache::get('tweets'));
        }


        $bearerToken = env('TWITTER_BEARER_TOKEN', 'AAAAAAAAAAAAAAAAAAAAAO0myQEAAAAA4J0Q1RsWWvy0HUxtOlO%2FPqujDLY%3Dy10jIQcly7iBTzjWmEmRy0j3jf2VwLpwmZpNyfSy4i5hJrvZ2r');
        $userId = env('TWITTER_USER_ID', '5diena13'); // The user whose tweets you want to fetch

        $bearerToken = env('TWITTER_BEARER_TOKEN');
        $userId = '5diena13'; // Your user ID

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.twitter.com/2/users/{$userId}/tweets", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken
                ],
                'query' => [
                    'max_results' => 5, // Reduced from 10 to 5 to help with rate limits
                    'tweet.fields' => 'created_at'
                ],
                // Add retry and backoff strategy
                'retry_after' => true,
                'connect_timeout' => 10,
                'http_errors' => true,
                'timeout' => 30
            ]);

            $data = $response->getBody();

            Cache::put('tweets', $data, now()->addMinutes(15));

            return response()->json($data);

        } catch (\Exception $e) {
            // Handle rate limit
            $resetTime = $e->getResponse()->getHeader('x-rate-limit-reset')[0] ?? null;
            $waitTime = $resetTime ? ($resetTime - time()) : 900; // 15 minutes default

            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $waitTime
            ], 429);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch tweets',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
