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
    public const PER_PAGE = 24;

    protected Client $client;
    protected TranslationService $translationService;

    /**
     * Supported Twitter handles (scraped first)
     */
    protected array $twitterHandles = [
        'penaltyloop',
        'biathstats',
        'biathlonworld',
        'ibu_newsroom',
        'BiathlonLivefr',
        'NordicMag',
    ];

    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
    ];

    public function __construct(?TranslationService $translationService = null)
    {
        $this->translationService = $translationService ?? app(TranslationService::class);
        $this->client = new Client([
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json, application/xml, text/xml, text/html, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]
        ]);
    }

    /**
     * Prepare translation data for a tweet content if non-English
     *
     * @param string $content
     * @return array{translated_content: ?string, source_language: ?string}
     */
    public function detectAndTranslate(string $content): array
    {
        $translation = $this->translationService->translateToEnglish($content);
        if ($translation) {
            return [
                'translated_content' => $translation['translated_text'],
                'source_language' => $translation['source_language'],
            ];
        }

        return [
            'translated_content' => null,
            'source_language' => 'en',
        ];
    }

    /**
     * Get or calculate translation attributes for a tweet
     *
     * @param string $tweetId
     * @param string $content
     * @return array{translated_content: ?string, source_language: ?string}
     */
    public function getTranslationAttributes(string $tweetId, string $content): array
    {
        $existing = Tweet::query()->where('tweet_id', $tweetId)->first(['translated_content', 'source_language']);
        if ($existing && $existing->source_language !== null) {
            return [
                'translated_content' => $existing->translated_content,
                'source_language' => $existing->source_language,
            ];
        }

        return $this->detectAndTranslate($content);
    }

    /**
     * Extract Open Graph or Twitter Card preview image from URL if tweet has no uploaded media
     */
    public function extractOpenGraphImageFromUrl(string $url): ?string
    {
        try {
            $cacheKey = 'og_img_' . md5($url);
            return Cache::remember($cacheKey, 86400 * 7, function () use ($url) {
                $res = $this->client->get($url, [
                    'timeout' => 6,
                    'http_errors' => false,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ],
                ]);

                if ($res->getStatusCode() === 200) {
                    $html = (string) $res->getBody();
                    if (preg_match('/<meta[^>]+property=[\'"]og:image[\'"][^>]+content=[\'"]([^\'"]+)[\'"]/i', $html, $m) ||
                        preg_match('/<meta[^>]+content=[\'"]([^\'"]+)[\'"][^>]+property=[\'"]og:image[\'"]/i', $html, $m)) {
                        $img = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (str_starts_with($img, '//')) $img = 'https:' . $img;
                        if (str_starts_with($img, 'http')) return $img;
                    }
                    if (preg_match('/<meta[^>]+name=[\'"]twitter:image[\'"][^>]+content=[\'"]([^\'"]+)[\'"]/i', $html, $m) ||
                        preg_match('/<meta[^>]+content=[\'"]([^\'"]+)[\'"][^>]+name=[\'"]twitter:image[\'"]/i', $html, $m)) {
                        $img = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (str_starts_with($img, '//')) $img = 'https:' . $img;
                        if (str_starts_with($img, 'http')) return $img;
                    }
                }

                return null;
            });
        } catch (\Throwable $e) {
            Log::info("Could not extract OG image from {$url}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Extract Open Graph or Twitter Card preview image from text content if tweet has URLs
     */
    public function extractOpenGraphImageFromContent(string $content): ?string
    {
        if (preg_match('~https?://[^\s<"\']+~i', $content, $match)) {
            $url = rtrim($match[0], '.,;:!?');
            if (stripos($url, 'x.com') !== false || stripos($url, 'twitter.com') !== false) {
                return null;
            }

            return $this->extractOpenGraphImageFromUrl($url);
        }

        return null;
    }

    /**
     * Extract card image from Twitter card payload
     */
    protected function extractCardImageFromPayload(array $card): ?string
    {
        $bv = $card['binding_values'] ?? [];
        $candidates = [
            $bv['photo_image_full_size_original']['image_value']['url'] ?? null,
            $bv['photo_image_full_size_large']['image_value']['url'] ?? null,
            $bv['photo_image_full_size_large']['image_value']['url_https'] ?? null,
            $bv['summary_photo_image_large']['image_value']['url'] ?? null,
            $bv['summary_photo_image_large']['image_value']['url_https'] ?? null,
            $bv['thumbnail_image_original']['image_value']['url'] ?? null,
            $bv['thumbnail_image_large']['image_value']['url'] ?? null,
            $bv['thumbnail_image_large']['image_value']['url_https'] ?? null,
            $bv['photo_image_full_size']['image_value']['url'] ?? null,
            $bv['thumbnail_image']['image_value']['url'] ?? null,
            $bv['thumbnail_image']['image_value']['url_https'] ?? null,
            $bv['promo_image']['image_value']['url_https'] ?? null,
        ];

        foreach ($candidates as $c) {
            if (!empty($c) && is_string($c)) {
                return $c;
            }
        }

        return null;
    }

    /**
     * Deeply extract media from a tweet's JSON payload (photos, mediaDetails, cards, quoted tweets)
     */
    public function extractMediaFromTweetPayload(array $data): array
    {
        $media = [];

        // 1. Root photos
        if (!empty($data['photos']) && is_array($data['photos'])) {
            foreach ($data['photos'] as $p) {
                if (!empty($p['url']) && !in_array($p['url'], $media)) $media[] = $p['url'];
            }
        }

        // 2. Root media details
        if (!empty($data['mediaDetails']) && is_array($data['mediaDetails'])) {
            foreach ($data['mediaDetails'] as $m) {
                if (!empty($m['media_url_https']) && !in_array($m['media_url_https'], $media)) {
                    $media[] = $m['media_url_https'];
                }
            }
        }

        // 3. Root card images
        $cardImg = $this->extractCardImageFromPayload($data['card'] ?? []);
        if ($cardImg && !in_array($cardImg, $media)) {
            $media[] = $cardImg;
        }

        // 4. Quoted tweet media & cards
        if (!empty($data['quoted_tweet']) && is_array($data['quoted_tweet'])) {
            $qt = $data['quoted_tweet'];
            if (!empty($qt['photos']) && is_array($qt['photos'])) {
                foreach ($qt['photos'] as $p) {
                    if (!empty($p['url']) && !in_array($p['url'], $media)) $media[] = $p['url'];
                }
            }
            if (!empty($qt['mediaDetails']) && is_array($qt['mediaDetails'])) {
                foreach ($qt['mediaDetails'] as $m) {
                    if (!empty($m['media_url_https']) && !in_array($m['media_url_https'], $media)) {
                        $media[] = $m['media_url_https'];
                    }
                }
            }
            $qtCardImg = $this->extractCardImageFromPayload($qt['card'] ?? []);
            if ($qtCardImg && !in_array($qtCardImg, $media)) {
                $media[] = $qtCardImg;
            }

            // Quoted tweet external URLs
            if (empty($media) && !empty($qt['entities']['urls'])) {
                foreach ($qt['entities']['urls'] as $u) {
                    $url = $u['expanded_url'] ?? $u['url'] ?? '';
                    if ($url && !str_contains($url, 'x.com') && !str_contains($url, 'twitter.com') && !str_contains($url, 't.co')) {
                        $og = $this->extractOpenGraphImageFromUrl($url);
                        if ($og && !in_array($og, $media)) {
                            $media[] = $og;
                            break;
                        }
                    }
                }
            }
        }

        // 5. Root external URLs
        if (empty($media) && !empty($data['entities']['urls'])) {
            foreach ($data['entities']['urls'] as $u) {
                $url = $u['expanded_url'] ?? $u['url'] ?? '';
                if ($url && !str_contains($url, 'x.com') && !str_contains($url, 'twitter.com') && !str_contains($url, 't.co')) {
                    $og = $this->extractOpenGraphImageFromUrl($url);
                    if ($og && !in_array($og, $media)) {
                        $media[] = $og;
                        break;
                    }
                }
            }
        }

        return $media;
    }

    /**
     * Deeply search and extract media for a tweet (syndication, quoted tweets, twitter cards, and OpenGraph)
     */
    public function enrichTweetMedia(Tweet $tweet): ?array
    {
        $rawId = str_replace('tw_', '', $tweet->tweet_id);
        if (is_numeric($rawId)) {
            try {
                $res = $this->client->get("https://cdn.syndication.twimg.com/tweet-result?id={$rawId}&token=1", [
                    'timeout' => 6,
                    'http_errors' => false,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                        'Referer' => 'https://platform.twitter.com/',
                    ]
                ]);

                if ($res->getStatusCode() === 200) {
                    $data = json_decode((string)$res->getBody(), true);
                    if ($data) {
                        $media = $this->extractMediaFromTweetPayload($data);
                        if (!empty($media)) {
                            return $media;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        // Fallback: extract OG image from any URLs in tweet content
        $og = $this->extractOpenGraphImageFromContent($tweet->content);
        if ($og) {
            return [$og];
        }

        return null;
    }

    /**
     * Backfill missing media URLs by performing deep inspection (quoted tweets, cards, and OpenGraph)
     */
    public function backfillMissingMediaUrls(int $limit = 100): int
    {
        $tweets = Tweet::query()
            ->whereNull('media_urls')
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();

        $count = 0;
        foreach ($tweets as $tweet) {
            $media = $this->enrichTweetMedia($tweet);
            if (!empty($media)) {
                $tweet->update([
                    'media_urls' => $media,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Translate any existing tweets in the database that have not been checked for translation yet
     */
    public function translateUntranslatedTweets(int $limit = 50): int
    {
        $tweets = Tweet::query()
            ->whereNull('source_language')
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();

        $count = 0;
        foreach ($tweets as $tweet) {
            $trans = $this->detectAndTranslate($tweet->content);
            $tweet->update([
                'translated_content' => $trans['translated_content'],
                'source_language' => $trans['source_language'],
            ]);
            $count++;
        }

        return $count;
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

        if ($ibuNewsroomRss = env('IBU_NEWSROOM_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom IBU Newsroom RSS Bridge',
                'url' => $ibuNewsroomRss,
                'delay' => 0,
            ];
        }

        if ($biathlonLiveFrRss = env('BIATHLONLIVEFR_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom BiathlonLivefr RSS Bridge',
                'url' => $biathlonLiveFrRss,
                'delay' => 0,
            ];
        }

        if ($nordicMagRss = env('NORDICMAG_RSS_URL')) {
            $providers[] = [
                'type' => 'rss',
                'name' => 'Custom NordicMag RSS Bridge',
                'url' => $nordicMagRss,
                'delay' => 0,
            ];
        }

        $providers[] = [
            'type' => 'penaltyloop_rss',
            'name' => 'PenaltyLoop.com Blog RSS Feed',
            'delay' => 0,
        ];

        if (filter_var(env('ENABLE_BLUESKY_FEED', false), FILTER_VALIDATE_BOOLEAN)) {
            $providers[] = [
                'type' => 'bluesky',
                'name' => 'Bluesky Live Stream (@penaltyloop.bsky.social)',
                'delay' => 0,
            ];
        }

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

        $query = Tweet::query();

        $isAdmin = (bool) auth()->user()?->isAdmin();
        if (!$isAdmin) {
            $query->where('should_hide', false);
        }

        return $query
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get latest cached tweets from database
     */
    public function getLatestTweets(int $limit = 12): Collection
    {
        $isAdmin = (bool) auth()->user()?->isAdmin();
        $cacheKey = 'biathlon_latest_tweets_' . $limit . ($isAdmin ? '_admin' : '');

        return Cache::remember($cacheKey, 300, function () use ($limit, $isAdmin) {
            $query = Tweet::query();
            if (!$isAdmin) {
                $query->where('should_hide', false);
            }

            $tweets = $query
                ->orderByDesc('published_at')
                ->take($limit)
                ->get();

            if ($tweets->isEmpty()) {
                $this->syncTweets();
                $query = Tweet::query();
                if (!$isAdmin) {
                    $query->where('should_hide', false);
                }
                $tweets = $query
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

        // Backfill / translate any remaining untranslated tweets in DB
        $this->translateUntranslatedTweets(50);
        // Backfill / extract Open Graph preview images for tweets that contain article links
        $this->backfillMissingMediaUrls(100);

        Cache::forget('biathlon_latest_tweets_6');
        Cache::forget('biathlon_latest_tweets_12');
        Cache::forget('biathlon_latest_tweets_3');
        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        return Tweet::count();
    }

    /**
     * Run Puppeteer runner script for a given argument array
     */
    protected function runPuppeteerScraper(array $args): ?array
    {
        $scriptPath = base_path('scripts/fetch-tweets-puppeteer.js');
        if (!file_exists($scriptPath)) {
            return null;
        }

        try {
            $nodeBinary = env('NODE_BINARY', 'node');
            $command = array_merge([$nodeBinary, $scriptPath], $args);

            $process = new \Symfony\Component\Process\Process(
                $command,
                base_path(),
                [
                    'PUPPETEER_WS_ENDPOINT' => env('PUPPETEER_WS_ENDPOINT', ''),
                    'PUPPETEER_EXECUTABLE_PATH' => env('PUPPETEER_EXECUTABLE_PATH', ''),
                    'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
                ],
                null,
                45
            );

            $process->run();

            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $data = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            } else {
                Log::warning('Puppeteer scraper stderr: ' . substr($process->getErrorOutput(), 0, 300));
            }
        } catch (\Throwable $e) {
            Log::warning('Error running Puppeteer scraper: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Parse and sync real-time tweets from Twitter/X syndication widget endpoint for a given handle
     */
    protected function syncFromTwitterSyndication(string $handle = 'penaltyloop'): int
    {
        $synced = 0;

        // 1. Try Headless Puppeteer with stealth headers first
        $puppeteerResult = $this->runPuppeteerScraper(["--handle={$handle}"]);
        if ($puppeteerResult && !empty($puppeteerResult['results'][$handle]['items'])) {
            $items = $puppeteerResult['results'][$handle]['items'];
            foreach ($items as $item) {
                $translationData = $this->getTranslationAttributes($item['tweet_id'], $item['content']);
                $mediaUrls = $item['media_urls'] ?? null;
                if (empty($mediaUrls)) {
                    $ogImg = $this->extractOpenGraphImageFromContent($item['content']);
                    if ($ogImg) {
                        $mediaUrls = [$ogImg];
                    }
                }

                Tweet::query()->updateOrCreate(
                    ['tweet_id' => $item['tweet_id']],
                    [
                        'author_name' => $item['author_name'] ?? ucfirst($handle),
                        'author_handle' => $item['author_handle'] ?? $handle,
                        'author_avatar' => $item['author_avatar'] ?? null,
                        'content' => $item['content'],
                        'translated_content' => $translationData['translated_content'],
                        'source_language' => $translationData['source_language'],
                        'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
                        'likes_count' => (int)($item['likes_count'] ?? 0),
                        'retweets_count' => (int)($item['retweets_count'] ?? 0),
                        'tweet_url' => $item['tweet_url'] ?? 'https://x.com/' . $handle,
                        'published_at' => isset($item['published_at']) ? Carbon::parse($item['published_at']) : now(),
                    ]
                );
                $synced++;
            }

            if ($synced > 0) {
                return $synced;
            }
        }

        // 2. Direct HTTP Fallback with Platform Referer Header
        try {
            $ua = $this->userAgents[array_rand($this->userAgents)];
            $res = $this->client->get('https://syndication.twitter.com/srv/timeline-profile/screen-name/' . $handle, [
                'headers' => [
                    'User-Agent' => $ua,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => 'https://platform.twitter.com/',
                    'Origin' => 'https://platform.twitter.com',
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
                        if (empty($mediaUrls)) {
                            $ogImg = $this->extractOpenGraphImageFromContent($text);
                            if ($ogImg) {
                                $mediaUrls = [$ogImg];
                            }
                        }

                        $tweetId = 'tw_' . $idStr;
                        $translationData = $this->getTranslationAttributes($tweetId, $text);

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => $user['name'] ?? ucfirst($handle),
                                'author_handle' => $user['screen_name'] ?? $handle,
                                'author_avatar' => $user['profile_image_url_https'] ?? null,
                                'content' => $text,
                                'translated_content' => $translationData['translated_content'],
                                'source_language' => $translationData['source_language'],
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
            $ua = $this->userAgents[array_rand($this->userAgents)];
            $res = $this->client->get($url, [
                'headers' => [
                    'User-Agent' => $ua,
                    'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                ]
            ]);
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

                        $translationData = $this->getTranslationAttributes($tweetId, $text);

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Biathlon News',
                                'author_handle' => 'biathlon',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $text,
                                'translated_content' => $translationData['translated_content'],
                                'source_language' => $translationData['source_language'],
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
     * Check if a tweet/post with matching or substantially identical content already exists
     */
    protected function isDuplicateContent(string $content, ?string $authorHandle = null, ?Carbon $publishedAt = null, ?string $excludeTweetId = null): bool
    {
        $normalizedIncoming = $this->normalizeContentForComparison($content);
        if (mb_strlen($normalizedIncoming) < 10) {
            return false;
        }

        $query = Tweet::query();
        if ($excludeTweetId) {
            $query->where('tweet_id', '!=', $excludeTweetId);
        }
        if ($authorHandle) {
            $query->where('author_handle', $authorHandle);
        }
        if ($publishedAt) {
            $query->whereBetween('published_at', [
                $publishedAt->copy()->subDays(4),
                $publishedAt->copy()->addDays(4),
            ]);
        } else {
            $query->orderByDesc('published_at')->take(50);
        }

        $candidates = $query->get(['tweet_id', 'content']);

        foreach ($candidates as $candidate) {
            $normalizedCandidate = $this->normalizeContentForComparison($candidate->content);
            if ($normalizedIncoming === $normalizedCandidate) {
                return true;
            }

            // Fuzzy similarity check for cross-posted content with minor formatting differences
            similar_text($normalizedIncoming, $normalizedCandidate, $percent);
            if ($percent >= 85.0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize text for robust cross-platform duplicate detection
     */
    protected function normalizeContentForComparison(string $text): string
    {
        // Replace smart quotes and special typographical symbols
        $text = str_replace(["’", "‘", "`", "“", "”", "„"], ["'", "'", "'", '"', '"', '"'], $text);
        // Strip URLs
        $text = preg_replace('~https?://\S+~i', '', $text);
        // Normalize whitespace and newlines
        $text = preg_replace('/\s+/', ' ', trim($text));
        // Lowercase
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * Sync from PenaltyLoop.com official blog RSS (with Puppeteer WAF bypass)
     */
    protected function syncFromPenaltyLoopRss(): int
    {
        $synced = 0;
        $rssUrl = 'https://penaltyloop.com/feed/';

        // 1. Try Puppeteer stealth scraper to easily pass Cloudflare/Wordfence
        $puppeteerResult = $this->runPuppeteerScraper(["--rss={$rssUrl}"]);
        if ($puppeteerResult && !empty($puppeteerResult['results']['rss']['items'])) {
            $items = $puppeteerResult['results']['rss']['items'];
            foreach ($items as $item) {
                $pubDate = isset($item['published_at']) ? Carbon::parse($item['published_at']) : now();
                $tweetId = $item['tweet_id'];

                if ($this->isDuplicateContent($item['content'], 'penaltyloop', $pubDate, $tweetId)) {
                    continue;
                }

                $translationData = $this->getTranslationAttributes($tweetId, $item['content']);

                Tweet::query()->updateOrCreate(
                    ['tweet_id' => $tweetId],
                    [
                        'author_name' => $item['author_name'] ?? 'Penalty Loop',
                        'author_handle' => $item['author_handle'] ?? 'penaltyloop',
                        'author_avatar' => $item['author_avatar'] ?? 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                        'content' => $item['content'],
                        'translated_content' => $translationData['translated_content'],
                        'source_language' => $translationData['source_language'],
                        'media_urls' => $item['media_urls'] ?? null,
                        'likes_count' => 0,
                        'retweets_count' => 0,
                        'tweet_url' => $item['tweet_url'] ?? 'https://penaltyloop.com',
                        'published_at' => $pubDate,
                    ]
                );
                $synced++;
            }

            if ($synced > 0) {
                return $synced;
            }
        }

        // 2. Direct HTTP Fallback
        try {
            $ua = $this->userAgents[array_rand($this->userAgents)];
            $res = $this->client->get($rssUrl, [
                'headers' => [
                    'User-Agent' => $ua,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ]
            ]);
            if ($res->getStatusCode() === 200) {
                $body = (string)$res->getBody();
                $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = trim((string)$item->title);
                        $contentNs = $item->children('http://purl.org/rss/1.0/modules/content/');
                        $encodedContent = isset($contentNs->encoded) ? (string)$contentNs->encoded : (string)$item->description;
                        $content = $this->extractCleanArticleContent($encodedContent, $title);

                        $link = trim((string)$item->link);
                        $slug = basename(parse_url($link, PHP_URL_PATH));
                        $tweetId = 'article_' . ($slug ?: md5($link));
                        $pubDate = (string)$item->pubDate ? Carbon::parse((string)$item->pubDate) : now();

                        if ($this->isDuplicateContent($content, 'penaltyloop', $pubDate, $tweetId)) {
                            continue;
                        }

                        $mediaUrls = [];
                        if (isset($item->enclosure) && !empty($item->enclosure['url'])) {
                            $mediaUrls[] = (string)$item->enclosure['url'];
                        }

                        // Check media:content and media:thumbnail
                        $mediaNs = $item->children('http://search.yahoo.com/mrss/');
                        if (isset($mediaNs->content)) {
                            foreach ($mediaNs->content as $mc) {
                                if (isset($mc->attributes()->url)) {
                                    $mediaUrls[] = (string)$mc->attributes()->url;
                                }
                            }
                        }
                        if (isset($mediaNs->thumbnail)) {
                            foreach ($mediaNs->thumbnail as $mt) {
                                if (isset($mt->attributes()->url)) {
                                    $mediaUrls[] = (string)$mt->attributes()->url;
                                }
                            }
                        }

                        // Check content:encoded for embedded <img> tags
                        if (!empty($encodedContent)) {
                            preg_match_all('/<img[^>]+(?:src|data-orig-file)=["\']([^"\']+)["\']/i', $encodedContent, $imgMatches);
                            if (!empty($imgMatches[1])) {
                                foreach ($imgMatches[1] as $imgUrl) {
                                    $imgUrl = html_entity_decode($imgUrl);
                                    if (!str_contains($imgUrl, 'pixel') && !str_contains($imgUrl, 'smilies') && !str_contains($imgUrl, 's.w.org') && !str_contains($imgUrl, 'emoji') && !in_array($imgUrl, $mediaUrls)) {
                                        $mediaUrls[] = $imgUrl;
                                    }
                                }
                            }
                        }

                        $translationData = $this->getTranslationAttributes($tweetId, $content);

                        Tweet::query()->updateOrCreate(
                            ['tweet_id' => $tweetId],
                            [
                                'author_name' => 'Penalty Loop',
                                'author_handle' => 'penaltyloop',
                                'author_avatar' => 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                                'content' => $content,
                                'translated_content' => $translationData['translated_content'],
                                'source_language' => $translationData['source_language'],
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
     * Convert HTML tables, emoji images, and block elements from RSS into clean readable text
     */
    public function extractCleanArticleContent(string $rawHtml, string $title): string
    {
        // 1. Convert WordPress emoji images (<img alt="🥇" ...>) to raw emoji
        $html = preg_replace('/<img[^>]+alt=[\'"]([^\'"]+)[\'"][^>]*>/i', '$1', $rawHtml);

        // 2. Convert table structures (e.g. <tr><td>Nation</td><td>Medals</td></tr>) into clean text rows
        $html = preg_replace_callback('/<tr[^>]*>(.*?)<\/tr>/is', function ($m) {
            preg_match_all('/<(?:td|th)[^>]*>(.*?)<\/(?:td|th)>/is', $m[1], $tds);
            if (!empty($tds[1])) {
                $cells = array_map(function ($c) {
                    $c = preg_replace('/<img[^>]+alt=[\'"]([^\'"]+)[\'"][^>]*>/i', '$1', $c);
                    return trim(strip_tags($c));
                }, $tds[1]);
                $cells = array_filter($cells, fn($c) => $c !== '');
                if (count($cells) === 2) {
                    return $cells[0] . ': ' . $cells[1] . "\n";
                }
                return implode(' | ', $cells) . "\n";
            }
            return "\n";
        }, $html);

        // 3. Convert block level elements to newlines
        $html = preg_replace('/<\/(?:p|div|h[1-6]|li|figure|table)>/i', "\n", $html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        // 4. Strip remaining HTML tags and decode entities
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 5. Normalize whitespace and newlines
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace("/\n[ \t]+/u", "\n", $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);
        $text = trim($text);

        // 6. Avoid repeating title if text starts with it
        $cleanTitle = html_entity_decode(trim($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($text, $cleanTitle)) {
            $text = trim(substr($text, strlen($cleanTitle)));
        }

        // Limit excerpt to reasonable length (~350 chars) while keeping clean sentence cutoff
        if (mb_strlen($text) > 350) {
            $truncated = mb_substr($text, 0, 350);
            $lastNewline = mb_strrpos($truncated, "\n");
            $lastPeriod = mb_strrpos($truncated, '. ');
            $cutoff = max($lastNewline !== false ? $lastNewline : 0, $lastPeriod !== false ? $lastPeriod + 1 : 0);
            if ($cutoff > 200) {
                $text = mb_substr($truncated, 0, $cutoff);
            } else {
                $text = $truncated . '...';
            }
        }

        return "📝 " . $cleanTitle . ($text ? "\n" . $text : '');
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

                    $uri = $post['uri'] ?? '';
                    $parts = explode('/', $uri);
                    $rkey = end($parts);
                    $tweetId = 'post_' . $rkey;
                    $createdAt = Carbon::parse($record['createdAt']);

                    // Deduplication check: skip if matching content was already imported from X.com
                    if ($this->isDuplicateContent($text, 'penaltyloop', $createdAt, $tweetId)) {
                        continue;
                    }

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

                    $translationData = $this->getTranslationAttributes($tweetId, $text);

                    Tweet::query()->updateOrCreate(
                        ['tweet_id' => $tweetId],
                        [
                            'author_name' => $post['author']['displayName'] ?? 'Penalty Loop',
                            'author_handle' => 'penaltyloop',
                            'author_avatar' => $post['author']['avatar'] ?? 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                            'content' => $text,
                            'translated_content' => $translationData['translated_content'],
                            'source_language' => $translationData['source_language'],
                            'media_urls' => !empty($mediaUrls) ? $mediaUrls : null,
                            'likes_count' => (int)($post['likeCount'] ?? 0),
                            'retweets_count' => (int)($post['repostCount'] ?? 0),
                            'tweet_url' => 'https://x.com/penaltyloop',
                            'published_at' => $createdAt,
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

    public function normalizeAthleteLookupKey(string $s): string
    {
        $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s) ?: strtolower($s);
        $s = str_replace(['oe', 'ae', 'aa', 'ø', 'æ', 'å'], ['o', 'a', 'a', 'o', 'a', 'a'], $s);
        return preg_replace('/[^a-z0-9]/', '', $s);
    }

    /**
     * Cache and retrieve all biathlon athletes who have valid portrait photos
     */
    public function getAthletesWithPhotos(): Collection
    {
        return Cache::remember('athletes_with_photos_list', 3600 * 24, function () {
            return \App\Models\Athlete::query()
                ->whereNotNull('photo_uri')
                ->where('photo_uri', '!=', '')
                ->get(['id', 'given_name', 'family_name', 'nat', 'photo_uri', 'ibu_id']);
        });
    }

    /**
     * Build and cache a high-speed O(1) hash map index for instantaneous athlete matching
     */
    public function getAthletesNameIndex(): array
    {
        return Cache::remember('athletes_name_index_v3', 3600 * 24, function () {
            $athletes = $this->getAthletesWithPhotos();

            $fullNames = [];
            $initialNames = [];
            $surnames = [];
            $byId = [];

            $stopWords = [
                'french', 'france', 'german', 'germany', 'norwegian', 'norway', 'swedish', 'sweden',
                'italian', 'italy', 'finnish', 'finland', 'czech', 'polish', 'austrian', 'austria',
                'swiss', 'switzerland', 'estonian', 'estonia', 'latvian', 'latvia', 'ukrainian', 'ukraine',
                'canadian', 'canada', 'american', 'usa', 'cup', 'summer', 'winter', 'world',
                'sprint', 'pursuit', 'individual', 'mass', 'relay', 'stage', 'season', 'race',
                'globe', 'men', 'women', 'team', 'junior', 'best', 'gold', 'silver', 'bronze',
                'start', 'finish', 'point', 'total', 'rank', 'time', 'shot', 'short', 'long',
                'young', 'king', 'fast', 'white', 'black', 'brown', 'green', 'rose', 'hall',
                'gross', 'horn', 'brand', 'cross', 'post', 'wolf', 'graf', 'clarke', 'williams',
                'smith', 'miller', 'jones', 'page', 'today', 'news', 'press', 'podcast', 'story',
                'action', 'round', 'look', 'good', 'great', 'open', 'championship', 'national'
            ];

            foreach ($athletes as $athlete) {
                $byId[$athlete->id] = $athlete;

                $given = trim((string)$athlete->given_name);
                $family = trim((string)$athlete->family_name);

                $givenNorm = $this->normalizeAthleteLookupKey($given);
                $familyNorm = $this->normalizeAthleteLookupKey($family);

                if (strlen($givenNorm) >= 2 && strlen($familyNorm) >= 2) {
                    // Given + Family (e.g. "ericperrot")
                    $fullNames[$givenNorm . $familyNorm] = $athlete->id;

                    // Family + Given (e.g. "perroteric")
                    $fullNames[$familyNorm . $givenNorm] = $athlete->id;

                    // Initials (e.g. "jtboe", "eperrot")
                    $givenParts = explode(' ', strtolower($given));
                    $initialsAll = preg_replace('/[^a-z]/', '', implode('', array_map(fn($w) => substr($w, 0, 1), $givenParts)));
                    if (!empty($initialsAll)) {
                        $initialNames[$initialsAll . $familyNorm] = $athlete->id;
                        $initialNames[substr($initialsAll, 0, 1) . $familyNorm] = $athlete->id;
                    }
                }

                // Standalone Surnames
                if ((strlen($familyNorm) >= 4 || in_array($familyNorm, ['boe', 'botn'])) && !in_array($familyNorm, $stopWords)) {
                    if (!isset($surnames[$familyNorm])) {
                        $surnames[$familyNorm] = $athlete->id;
                    }
                }
            }

            return [
                'full_names' => $fullNames,
                'initial_names' => $initialNames,
                'surnames' => $surnames,
                'by_id' => $byId,
            ];
        });
    }

    public function getAthleteById(int $id): ?\App\Models\Athlete
    {
        $index = $this->getAthletesNameIndex();
        return $index['by_id'][$id] ?? \App\Models\Athlete::find($id);
    }

    /**
     * Find the first mentioned athlete in tweet content to render their official IBU / BiathlonWorld photo
     */
    public function findMentionedAthleteInText(?string $text): ?\App\Models\Athlete
    {
        if (empty($text)) {
            return null;
        }

        $index = $this->getAthletesNameIndex();
        $fullNames = $index['full_names'];
        $initialNames = $index['initial_names'];
        $surnames = $index['surnames'];
        $byId = $index['by_id'];

        $cleanText = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (!preg_match_all('/[\p{L}\p{N}\-]+/u', $cleanText, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $tokens = [];
        foreach ($matches[0] as $match) {
            $tokens[] = [
                'norm' => $this->normalizeAthleteLookupKey($match[0]),
                'offset' => $match[1],
            ];
        }
        $count = count($tokens);

        // TIER 1: Full Names (3-gram or 2-gram combinations)
        $tier1MatchId = null;
        $tier1Pos = PHP_INT_MAX;

        for ($i = 0; $i < $count; $i++) {
            // 3-word n-gram (e.g. Quentin Fillon Maillet)
            if ($i + 2 < $count) {
                $k3 = $tokens[$i]['norm'] . $tokens[$i+1]['norm'] . $tokens[$i+2]['norm'];
                if (isset($fullNames[$k3])) {
                    $pos = $tokens[$i]['offset'];
                    if ($pos < $tier1Pos) {
                        $tier1Pos = $pos;
                        $tier1MatchId = $fullNames[$k3];
                    }
                }
            }

            // 2-word n-gram (e.g. Eric Perrot)
            if ($i + 1 < $count) {
                $k2 = $tokens[$i]['norm'] . $tokens[$i+1]['norm'];
                if (isset($fullNames[$k2])) {
                    $pos = $tokens[$i]['offset'];
                    if ($pos < $tier1Pos) {
                        $tier1Pos = $pos;
                        $tier1MatchId = $fullNames[$k2];
                    }
                }
            }
        }

        if ($tier1MatchId !== null && isset($byId[$tier1MatchId])) {
            return $byId[$tier1MatchId];
        }

        // TIER 2: Initialed Names (e.g. J.T.Boe, S.H.Laegreid, E.Perrot)
        $tier2MatchId = null;
        $tier2Pos = PHP_INT_MAX;

        if (preg_match_all('/(?:[A-Z]\.){1,3}\s*([A-Za-zÀ-ÿ\-]+)/u', $cleanText, $initMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($initMatches[0] as $idx => $matchTuple) {
                $fullPattern = $matchTuple[0];
                $offset = $matchTuple[1];
                $famRaw = $initMatches[1][$idx][0];
                $famNorm = $this->normalizeAthleteLookupKey($famRaw);
                $initials = preg_replace('/[^a-z]/', '', strtolower(substr($fullPattern, 0, strpos($fullPattern, $famRaw))));

                $candKey = $initials . $famNorm;
                if (isset($initialNames[$candKey])) {
                    if ($offset < $tier2Pos) {
                        $tier2Pos = $offset;
                        $tier2MatchId = $initialNames[$candKey];
                    }
                }
            }
        }

        if ($tier2MatchId !== null && isset($byId[$tier2MatchId])) {
            return $byId[$tier2MatchId];
        }

        // TIER 3: Distinctive Surnames (1-word tokens)
        $tier3MatchId = null;
        $tier3Pos = PHP_INT_MAX;

        for ($i = 0; $i < $count; $i++) {
            $wordNorm = $tokens[$i]['norm'];
            if (isset($surnames[$wordNorm])) {
                $pos = $tokens[$i]['offset'];
                if ($pos < $tier3Pos) {
                    $tier3Pos = $pos;
                    $tier3MatchId = $surnames[$wordNorm];
                }
            }
        }

        if ($tier3MatchId !== null && isset($byId[$tier3MatchId])) {
            return $byId[$tier3MatchId];
        }

        return null;
    }
}
