<?php

namespace App\Services;

use App\Models\Tweet;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PenaltyLoopTweetService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ]
        ]);
    }

    /**
     * Get paginated tweets for infinite scroll
     */
    public function getPagedTweets(int $perPage = 3)
    {
        if (Tweet::count() === 0) {
            $this->syncTweets();
        }

        return Tweet::query()
            ->where('content', 'not like', '%podcast%')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get latest cached tweets from database
     */
    public function getLatestTweets(int $limit = 12): Collection
    {
        return Cache::remember('penalty_loop_latest_tweets_' . $limit, 300, function () use ($limit) {
            $tweets = Tweet::query()
                ->where('content', 'not like', '%podcast%')
                ->orderByDesc('published_at')
                ->take($limit)
                ->get();

            if ($tweets->isEmpty()) {
                $this->syncTweets();
                $tweets = Tweet::query()
                    ->where('content', 'not like', '%podcast%')
                    ->orderByDesc('published_at')
                    ->take($limit)
                    ->get();
            }

            return $tweets;
        });
    }

    /**
     * Sync live standalone posts from Penalty Loop's official biathlon stream (excluding replies and podcasts)
     */
    public function syncTweets(): int
    {
        $synced = 0;

        try {
            $res = $this->client->get('https://public.api.bsky.app/xrpc/app.bsky.feed.getAuthorFeed?actor=penaltyloop.bsky.social&limit=50&filter=posts_no_replies');
            if ($res->getStatusCode() === 200) {
                $data = json_decode((string)$res->getBody(), true);
                $feed = $data['feed'] ?? [];

                foreach ($feed as $item) {
                    // Filter out comments / replies to show only main posts
                    if (isset($item['reply'])) {
                        continue;
                    }

                    $post = $item['post'] ?? [];
                    $record = $post['record'] ?? [];
                    $text = $record['text'] ?? '';
                    if (empty(trim($text))) {
                        continue;
                    }

                    // Skip tweets containing word "podcast" (case-insensitive)
                    if (stripos($text, 'podcast') !== false) {
                        continue;
                    }

                    $uri = $post['uri'] ?? '';
                    $parts = explode('/', $uri);
                    $rkey = end($parts);

                    Tweet::query()->updateOrCreate(
                        ['tweet_id' => 'post_' . $rkey],
                        [
                            'author_name' => $post['author']['displayName'] ?? 'Penalty Loop',
                            'author_handle' => 'penaltyloop',
                            'author_avatar' => $post['author']['avatar'] ?? 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                            'content' => $text,
                            'likes_count' => (int)($post['likeCount'] ?? 0),
                            'retweets_count' => (int)($post['repostCount'] ?? 0),
                            'tweet_url' => 'https://x.com/penaltyloop',
                            'published_at' => Carbon::parse($record['createdAt']),
                        ]
                    );

                    $synced++;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing live Penalty Loop feed: ' . $e->getMessage());
        }

        // Delete any existing posts in DB containing word podcast
        Tweet::query()->where('content', 'like', '%podcast%')->delete();

        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        return Tweet::count();
    }
}
