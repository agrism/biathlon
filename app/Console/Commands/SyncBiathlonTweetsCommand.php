<?php

namespace App\Console\Commands;

use App\Services\BiathlonTweetService;
use Illuminate\Console\Command;

class SyncBiathlonTweetsCommand extends Command
{
    protected $signature = 'app:sync-biathlon-tweets';
    protected $description = 'Syncs latest biathlon tweets, metrics, and insights from @penaltyloop, @biathstats, and @biathlonworld into the database';

    public function handle(BiathlonTweetService $service): int
    {
        $this->info('Syncing latest biathlon tweets and telemetry across all providers...');
        $count = $service->syncTweets();
        $this->info("Successfully synced. Total tweets stored: {$count}");

        return self::SUCCESS;
    }
}
