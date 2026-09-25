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

                Tweet::query()->updateOrCreate(
                    ['tweet_id' => $item['tweet_id']],
                    [
                        'author_name' => $item['author_name'] ?? ucfirst($handle),
                        'author_handle' => $item['author_handle'] ?? $handle,
                        'author_avatar' => $item['author_avatar'] ?? null,
                        'content' => $item['content'],
                        'translated_content' => $translationData['translated_content'],
                        'source_language' => $translationData['source_language'],
                        'media_urls' => $item['media_urls'] ?? null,
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
                        $desc = strip_tags(trim((string)$item->description));
                        $content = "📝 " . $title . ($desc ? "\n" . substr($desc, 0, 240) . '...' : '');

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
}
