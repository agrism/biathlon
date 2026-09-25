<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TranslateTweetsCommand extends Command
{
    protected $signature = 'app:translate-tweets {--force : Force re-evaluating and translating all non-English tweets} {--limit=100 : Maximum number of tweets to process}';
    protected $description = 'Translate non-English tweets to English using neural translation';

    public function handle(BiathlonTweetService $tweetService, TranslationService $translationService): int
    {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');

        $this->info("Scanning tweets for translation (force=" . ($force ? 'true' : 'false') . ", limit={$limit})...");

        $query = Tweet::query();

        if ($force) {
            $query->where(function ($q) {
                $q->whereNull('translated_content')
                  ->orWhereNull('source_language')
                  ->orWhere('source_language', '!=', 'en');
            });
        } else {
            $query->whereNull('translated_content')
                  ->where(function ($q) {
                      $q->whereNull('source_language')
                        ->orWhere('source_language', '!=', 'en');
                  });
        }

        $tweets = $query->orderByDesc('published_at')->take($limit)->get();
        $this->info("Found {$tweets->count()} candidate tweets to process.");

        $translatedCount = 0;
        $englishCount = 0;

        foreach ($tweets as $tweet) {
            $this->line("Processing tweet #{$tweet->id}: " . mb_substr($tweet->content, 0, 60) . '...');

            $trans = $translationService->translateToEnglish($tweet->content);
            if ($trans) {
                $tweet->translated_content = $trans['translated_text'];
                $tweet->source_language = $trans['source_language'];
                $tweet->save();
                $this->info(" -> Translated [{$trans['source_language']}]: " . mb_substr($trans['translated_text'], 0, 60) . '...');
                $translatedCount++;
            } else {
                $heuristic = $translationService->detectLanguageHeuristic($tweet->content);
                if (!$heuristic) {
                    $tweet->source_language = 'en';
                    $tweet->save();
                    $englishCount++;
                } else {
                    $tweet->source_language = $heuristic;
                    $tweet->save();
                }
            }

            // Small pause between translation requests to be gentle on free API
            usleep(150000); // 150ms
        }

        $this->info("Completed. Translated: {$translatedCount}, Marked English: {$englishCount}.");

        // Clear tweet cache
        Cache::forget('biathlon_latest_tweets_6');
        Cache::forget('biathlon_latest_tweets_12');
        Cache::forget('biathlon_latest_tweets_3');
        Cache::forget('biathlon_latest_tweets_6_admin');
        Cache::forget('biathlon_latest_tweets_12_admin');
        Cache::forget('biathlon_latest_tweets_3_admin');
        Cache::forget('penalty_loop_latest_tweets_6');
        Cache::forget('penalty_loop_latest_tweets_12');
        Cache::forget('penalty_loop_latest_tweets_3');

        return Command::SUCCESS;
    }
}
