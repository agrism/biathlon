<?php

namespace App\Console\Commands;

use App\Services\PenaltyLoopTweetService;
use Illuminate\Console\Command;

class SyncPenaltyLoopTweetsCommand extends Command
{
    protected $signature = 'app:sync-penaltyloop-tweets';
    protected $description = 'Syncs latest biathlon tweets and insights from @penaltyloop into the database';

    public function handle(PenaltyLoopTweetService $service): int
    {
        $this->info('Syncing Penalty Loop tweets...');
        $count = $service->syncTweets();
        $this->info("Successfully synced. Total tweets stored: {$count}");

        return self::SUCCESS;
    }
}
