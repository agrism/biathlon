<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class RepairTweetMediaCommand extends Command
{
    protected $signature = 'app:repair-tweet-media {--limit=100 : Maximum number of tweets to inspect and repair}';
    protected $description = 'Repairs expired Twitter card images (pbs.twimg.com/card_img) by extracting canonical OpenGraph images from the original articles';

    public function handle(BiathlonTweetService $service): int
    {
        $this->info('🔍 Scanning for tweets with ephemeral or broken card images...');

        $limit = (int) $this->option('limit');
        $tweets = Tweet::query()
            ->where('media_urls', 'like', '%pbs.twimg.com/card_img%')
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();

        $this->line("Found <fg=yellow>{$tweets->count()}</> tweets with ephemeral Twitter card images.");

        if ($tweets->isEmpty()) {
            $this->info('✅ All tweet media URLs are in good shape!');
            return 0;
        }

        $repaired = 0;
        $cleared = 0;
        $client = new Client([
            'timeout' => 8,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Referer' => 'https://platform.twitter.com/',
            ],
        ]);

        foreach ($tweets as $tweet) {
            $this->line(sprintf('Checking tweet <fg=cyan>#%d</> (@%s)...', $tweet->id, $tweet->author_handle));
            $newMedia = null;

            // 1. Try extracting canonical OG image from URLs in content
            $og = $service->extractOpenGraphImageFromContent($tweet->content);
            if ($og) {
                $newMedia = [$og];
            }

            // 2. Try extracting from syndication API entities
            if (!$newMedia) {
                $rawId = str_replace('tw_', '', $tweet->tweet_id);
                if (is_numeric($rawId)) {
                    try {
                        $res = $client->get("https://cdn.syndication.twimg.com/tweet-result?id={$rawId}&token=1");
                        if ($res->getStatusCode() === 200) {
                            $data = json_decode((string) $res->getBody(), true);
                            $urls = array_merge(
                                $data['entities']['urls'] ?? [],
                                $data['quoted_tweet']['entities']['urls'] ?? []
                            );
                            foreach ($urls as $u) {
                                $expanded = $u['expanded_url'] ?? '';
                                if ($expanded && !str_contains($expanded, 'x.com') && !str_contains($expanded, 'twitter.com')) {
                                    $foundOg = $service->extractOpenGraphImageFromUrl($expanded);
                                    if ($foundOg) {
                                        $newMedia = [$foundOg];
                                        break;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        $this->warn("  Warning while fetching syndication: " . $e->getMessage());
                    }
                }
            }

            if (!empty($newMedia)) {
                $tweet->update(['media_urls' => $newMedia]);
                $repaired++;
                $this->line("  <fg=green>✔ Replaced with canonical article image:</> {$newMedia[0]}");
            } else {
                // Remove expired broken card_img so frontend falls back to clean brand/athlete visual
                $tweet->update(['media_urls' => null]);
                $cleared++;
                $this->line("  <fg=yellow>⚠ No article image found; removed broken card_img</>");
            }
        }

        $this->info("✨ Repair completed! Repaired: {$repaired}, Cleared broken: {$cleared}");
        return 0;
    }
}
