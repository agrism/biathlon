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
            'timeout' => 12,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'application/json, application/xml, text/xml, text/html, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]
        ]);
    }

    public const PER_PAGE = 4;

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
        return Cache::remember('penalty_loop_latest_tweets_' . $limit, 300, function () use ($limit) {
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
     * Sync live standalone posts & articles from Penalty Loop's streams
     */
    public function syncTweets(): int
    {
        // 1. Sync real-time tweets from Twitter/X syndication widget feed
        $this->syncFromTwitterSyndication();

        // 2. Sync from Custom Twitter/X RSS Bridge (if set in env)
        if ($customRssUrl = env('PENALTYLOOP_TWITTER_RSS_URL') ?: env('PENALTYLOOP_RSS_URL')) {
            $this->syncFromRssFeed($customRssUrl);
        }

        // 3. Sync from PenaltyLoop.com Official RSS Feed
        $this->syncFromPenaltyLoopRss();

        // 4. Sync from Bluesky live stream
        $this->syncFromBluesky();

        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        return Tweet::count();
    }

    /**
     * Parse and sync real-time tweets from Twitter/X syndication widget endpoint
     */
    protected function syncFromTwitterSyndication(): void
    {
        try {
            $res = $this->client->get('https://syndication.twitter.com/srv/timeline-profile/screen-name/penaltyloop', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
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

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => 'tw_' . $idStr],
                            [
                                'author_name' => $user['name'] ?? 'Penalty Loop',
                                'author_handle' => $user['screen_name'] ?? 'penaltyloop',
                                'author_avatar' => $user['profile_image_url_https'] ?? 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $text,
                                'media_urls' => $mediaUrls,
                                'likes_count' => (int)($tweet['favorite_count'] ?? 0),
                                'retweets_count' => (int)($tweet['retweet_count'] ?? 0),
                                'tweet_url' => 'https://x.com/penaltyloop/status/' . $idStr,
                                'published_at' => $createdAt,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing from Twitter syndication widget: ' . $e->getMessage());
        }
    }

    /**
     * Sync from an arbitrary RSS / Atom XML Feed (e.g. RSS.app, RSS-Bridge, Nitter)
     */
    protected function syncFromRssFeed(string $url): void
    {
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

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Penalty Loop',
                                'author_handle' => 'penaltyloop',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $text,
                                'likes_count' => 0,
                                'retweets_count' => 0,
                                'tweet_url' => trim((string)$item->link) ?: 'https://x.com/penaltyloop',
                                'published_at' => $pubDate,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing from custom RSS feed: ' . $e->getMessage());
        }
    }

    /**
     * Sync from PenaltyLoop.com official blog RSS
     */
    protected function syncFromPenaltyLoopRss(): void
    {
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

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Penalty Loop',
                                'author_handle' => 'penaltyloop',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $content,
                                'likes_count' => 0,
                                'retweets_count' => 0,
                                'tweet_url' => $link ?: 'https://penaltyloop.com',
                                'published_at' => $pubDate,
                            ]
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing from PenaltyLoop.com RSS: ' . $e->getMessage());
        }
    }

    /**
     * Sync from Bluesky live stream
     */
    protected function syncFromBluesky(): void
    {
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
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error syncing live Bluesky feed: ' . $e->getMessage());
        }
    }
}
