<?php

namespace App\Console\Commands;

use App\Services\BiathlonTweetService;
use Illuminate\Console\Command;

class SyncPenaltyLoopTweetsCommand extends Command
{
    protected $signature = 'app:sync-penaltyloop-tweets';
    protected $description = 'Alias for app:sync-biathlon-tweets';

    public function handle(BiathlonTweetService $service): int
    {
        $this->call('app:sync-biathlon-tweets');

        return self::SUCCESS;
    }
}
