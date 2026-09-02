<?php

namespace App\Services;

use App\Models\Tweet;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BiathlonTweetService
{
    public const PER_PAGE = 4;

    protected Client $client;

    /**
     * Supported Twitter handles
     */
    protected array $twitterHandles = [
        'penaltyloop',
        'biathstats',
        'biathlonworld',
    ];

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json, application/xml, text/xml, text/html, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]
        ]);
    }

    /**
     * Get the full list of configured providers and their details
     */
    public function getProviders(): array
    {
        $providers = [];

        foreach ($this->twitterHandles as $handle) {
            $providers[] = [
                'type' => 'twitter',
                'name' => "Twitter / X (@{$handle})",
                'handle' => $handle,
                'delay' => rand(10, 15),
            ];
        }

        if ($customRssUrl = env('PENALTYLOOP_TWITTER_RSS_URL') ?: env('PENALTYLOOP_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom PenaltyLoop RSS Bridge',
                'url' => $customRssUrl,
                'delay' => 0,
            ];
        }

        if ($biathstatsRss = env('BIATHSTATS_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom BiathStats RSS Bridge',
                'url' => $biathstatsRss,
                'delay' => 0,
            ];
        }

        if ($biathlonworldRss = env('BIATHLONWORLD_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom BiathlonWorld RSS Bridge',
                'url' => $biathlonworldRss,
                'delay' => 0,
            ];
        }

        $providers[] = [
            'type' => 'penaltyloop_rss',
            'name' => 'PenaltyLoop.com Blog RSS Feed',
            'delay' => 0,
        ];

        $providers[] = [
            'type' => 'bluesky',
            'name' => 'Bluesky Live Stream (@penaltyloop.bsky.social)',
            'delay' => 0,
        ];

        return $providers;
    }

    /**
     * Get paginated tweets for infinite scroll
     */
    public function getPagedTweets(?int $perPage = null)
    {
        $perPage = $perPage ?? self::PER_PAGE;

        if (Tweet::count() === 0) {
            $this->syncTweets();
        }

        return Tweet::query()
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get latest cached tweets from database
     */
    public function getLatestTweets(int $limit = 12): Collection
    {
        return Cache::remember('biathlon_latest_tweets_' . $limit, 300, function () use ($limit) {
            $tweets = Tweet::query()
                ->orderByDesc('published_at')
                ->take($limit)
                ->get();

            if ($tweets->isEmpty()) {
                $this->syncTweets();
                $tweets = Tweet::query()
                    ->orderByDesc('published_at')
                    ->take($limit)
                    ->get();
            }

            return $tweets;
        });
    }

    /**
     * Sync live standalone posts & articles from all supported biathlon providers
     */
    public function syncTweets(?callable $stepCallback = null): int
    {
        $providers = $this->getProviders();

        foreach ($providers as $index => $provider) {
            $delay = ($index > 0 && ($provider['delay'] ?? 0) > 0) ? (int)$provider['delay'] : 0;

            if ($stepCallback) {
                $stepCallback('waiting', [
                    'index' => $index + 1,
                    'total' => count($providers),
                    'provider' => $provider,
                    'delay' => $delay,
                ]);
            }

            if ($delay > 0) {
                sleep($delay);
            }

            $beforeCount = Tweet::count();
            $startTime = microtime(true);

            if ($stepCallback) {
                $stepCallback('starting', [
                    'index' => $index + 1,
                    'total' => count($providers),
                    'provider' => $provider,
                    'before_count' => $beforeCount,
                ]);
            }

            // Sync according to provider type
            $syncedCount = match ($provider['type']) {
                'twitter' => $this->syncFromTwitterSyndication($provider['handle']),
                'rss' => $this->syncFromRssFeed($provider['url']),
                'penaltyloop_rss' => $this->syncFromPenaltyLoopRss(),
                'bluesky' => $this->syncFromBluesky(),
                default => 0,
            };

            $afterCount = Tweet::count();
            $newlyAdded = max(0, $afterCount - $beforeCount);
            $duration = round(microtime(true) - $startTime, 2);

            $result = [
                'provider' => $provider,
                'before_count' => $beforeCount,
                'after_count' => $afterCount,
                'newly_added' => $newlyAdded,
                'synced_items' => $syncedCount,
                'duration' => $duration,
            ];

            if ($stepCallback) {
                $stepCallback('finished', [
                    'index' => $index + 1,
                    'total' => count($providers),
                    'result' => $result,
                ]);
            }
        }

        Cache::forget('biathlon_latest_tweets_6');
        Cache::forget('biathlon_latest_tweets_12');
        Cache::forget('biathlon_latest_tweets_3');
        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        return Tweet::count();
    }

    /**
     * Parse and sync real-time tweets from Twitter/X syndication widget endpoint for a given handle
     */
    protected function syncFromTwitterSyndication(string $handle = 'penaltyloop'): int
    {
        $synced = 0;

        try {
            $ua = $this->userAgents[array_rand($this->userAgents)];
            $res = $this->client->get('https://syndication.twitter.com/srv/timeline-profile/screen-name/' . $handle, [
                'headers' => [
                    'User-Agent' => $ua,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ]
            ]);

            if ($res->getStatusCode() === 200) {
                $html = (string)$res->getBody();
                if (preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
                    $json = json_decode($matches[1], true);
                    $entries = $json['props']['pageProps']['timeline']['entries'] ?? [];

                    foreach ($entries as $entry) {
                        $tweet = $entry['content']['tweet'] ?? null;
                        if (!$tweet) {
                            continue;
                        }

                        $text = $tweet['full_text'] ?? ($tweet['text'] ?? '');
                        if (empty(trim($text))) {
                            continue;
                        }

                        // Expand t.co links with full expanded URLs
                        $urls = $tweet['entities']['urls'] ?? [];
                        foreach ($urls as $urlEntity) {
                            if (!empty($urlEntity['url']) && !empty($urlEntity['expanded_url'])) {
                                $text = str_replace($urlEntity['url'], $urlEntity['expanded_url'], $text);
                            }
                        }

                        $idStr = $tweet['id_str'] ?? ($tweet['id'] ?? null);
                        if (!$idStr) {
                            continue;
                        }

                        $user = $tweet['user'] ?? [];
                        $createdAt = isset($tweet['created_at']) ? Carbon::parse($tweet['created_at']) : now();

                        $mediaUrls = [];
                        if (isset($tweet['extended_entities']['media'])) {
                            foreach ($tweet['extended_entities']['media'] as $media) {
                                if (isset($media['media_url_https'])) {
                                    $mediaUrls[] = $media['media_url_https'];
                                }
                            }
                        }
                        if (empty($mediaUrls) && isset($tweet['entities']['media'])) {
                            foreach ($tweet['entities']['media'] as $media) {
                                if (isset($media['media_url_https'])) {
                                    $mediaUrls[] = $media['media_url_https'];
                                }
                            }
                        }

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => 'tw_' . $idStr],
                            [
                                'author_name' => $user['name'] ?? ucfirst($handle),
                                'author_handle' => $user['screen_name'] ?? $handle,
                                'author_avatar' => $user['profile_image_url_https'] ?? null,
                                'content' => $text,
                                'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
                                'likes_count' => (int)($tweet['favorite_count'] ?? 0),
                                'retweets_count' => (int)($tweet['retweet_count'] ?? 0),
                                'tweet_url' => 'https://x.com/' . ($user['screen_name'] ?? $handle) . '/status/' . $idStr,
                                'published_at' => $createdAt,
                            ]
                        );

                        $synced++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Error syncing from Twitter syndication widget for @{$handle}: " . $e->getMessage());
        }

        return $synced;
    }

    /**
     * Sync from an arbitrary RSS / Atom XML Feed (e.g. RSS.app, RSS-Bridge, Nitter)
     */
    protected function syncFromRssFeed(string $url): int
    {
        $synced = 0;

        try {
            $res = $this->client->get($url);
            if ($res->getStatusCode() === 200) {
                $body = (string)$res->getBody();
                $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = trim((string)$item->title);
                        $desc = strip_tags(trim((string)$item->description));
                        $text = $desc ?: $title;

                        if (empty($text)) {
                            continue;
                        }

                        $guid = trim((string)$item->guid) ?: trim((string)$item->link);
                        $tweetId = 'rss_' . md5($guid);
                        $pubDate = (string)$item->pubDate ? Carbon::parse((string)$item->pubDate) : now();

                        $mediaUrls = [];
                        if (isset($item->enclosure) && !empty($item->enclosure['url'])) {
                            $mediaUrls[] = (string)$item->enclosure['url'];
                        }

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Biathlon News',
                                'author_handle' => 'biathlon',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $text,
                                'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
                                'likes_count' => 0,
                                'retweets_count' => 0,
                                'tweet_url' => trim((string)$item->link) ?: 'https://x.com',
                                'published_at' => $pubDate,
                            ]
                        );

                        $synced++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing from custom RSS feed: ' . $e->getMessage());
        }

        return $synced;
    }

    /**
     * Sync from PenaltyLoop.com official blog RSS
     */
    protected function syncFromPenaltyLoopRss(): int
    {
        $synced = 0;

        try {
            $res = $this->client->get('https://penaltyloop.com/feed/');
            if ($res->getStatusCode() === 200) {
                $body = (string)$res->getBody();
                $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = trim((string)$item->title);
                        $desc = strip_tags(trim((string)$item->description));
                        $content = "📝 " . $title . ($desc ? "\n" . substr($desc, 0, 240) . '...' : '');

                        $link = trim((string)$item->link);
                        $slug = basename(parse_url($link, PHP_URL_PATH));
                        $tweetId = 'article_' . ($slug ?: md5($link));
                        $pubDate = (string)$item->pubDate ? Carbon::parse((string)$item->pubDate) : now();

                        $mediaUrls = [];
                        if (isset($item->enclosure) && !empty($item->enclosure['url'])) {
                            $mediaUrls[] = (string)$item->enclosure['url'];
                        }

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Penalty Loop',
                                'author_handle' => 'penaltyloop',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $content,
                                'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
                                'likes_count' => 0,
                                'retweets_count' => 0,
                                'tweet_url' => $link ?: 'https://penaltyloop.com',
                                'published_at' => $pubDate,
                            ]
                        );

                        $synced++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing from PenaltyLoop.com RSS: ' . $e->getMessage());
        }

        return $synced;
    }

    /**
     * Sync from Bluesky live stream
     */
    protected function syncFromBluesky(): int
    {
        $synced = 0;

        try {
            $res = $this->client->get('https://public.api.bsky.app/xrpc/app.bsky.feed.getAuthorFeed?actor=penaltyloop.bsky.social&limit=50&filter=posts_no_replies');
            if ($res->getStatusCode() === 200) {
                $data = json_decode((string)$res->getBody(), true);
                $feed = $data['feed'] ?? [];

                foreach ($feed as $item) {
                    if (isset($item['reply'])) {
                        continue;
                    }

                    $post = $item['post'] ?? [];
                    $record = $post['record'] ?? [];
                    $text = $record['text'] ?? '';
                    if (empty(trim($text))) {
                        continue;
                    }

                    // Expand truncated Bluesky URLs using embed URI and rich text link facets
                    $replacements = [];
                    if (!empty($post['embed']['external']['uri'])) {
                        $fullUri = $post['embed']['external']['uri'];
                        $domain = parse_url($fullUri, PHP_URL_HOST);
                        if ($domain) {
                            $replacements[$domain] = $fullUri;
                        }
                    }

                    foreach ($record['facets'] ?? [] as $facet) {
                        foreach ($facet['features'] ?? [] as $feature) {
                            if (($feature['$type'] ?? '') === 'app.bsky.richtext.facet#link' && !empty($feature['uri'])) {
                                $fullUri = $feature['uri'];
                                $domain = parse_url($fullUri, PHP_URL_HOST);
                                if ($domain) {
                                    $replacements[$domain] = $fullUri;
                                }
                            }
                        }
                    }

                    foreach ($replacements as $domain => $fullUri) {
                        $text = preg_replace('~(?:https?://)?' . preg_quote($domain, '~') . '/[^\s]+(?:\.\.\.)?~i', $fullUri, $text);
                    }
                    $text = preg_replace('~https?://https?://~i', 'https://', $text);

                    // Extract high-resolution media images
                    $mediaUrls = [];
                    if (!empty($post['embed']['images'])) {
                        foreach ($post['embed']['images'] as $img) {
                            if (!empty($img['fullsize'])) {
                                $mediaUrls[] = $img['fullsize'];
                            } elseif (!empty($img['thumb'])) {
                                $mediaUrls[] = $img['thumb'];
                            }
                        }
                    }
                    if (!empty($post['embed']['media']['images'])) {
                        foreach ($post['embed']['media']['images'] as $img) {
                            if (!empty($img['fullsize'])) {
                                $mediaUrls[] = $img['fullsize'];
                            } elseif (!empty($img['thumb'])) {
                                $mediaUrls[] = $img['thumb'];
                            }
                        }
                    }
                    if (empty($mediaUrls) && !empty($post['embed']['external']['thumb'])) {
                        $mediaUrls[] = $post['embed']['external']['thumb'];
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
                            'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
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
            Log::warning('Error syncing live Bluesky feed: ' . $e->getMessage());
        }

        return $synced;
    }
}
