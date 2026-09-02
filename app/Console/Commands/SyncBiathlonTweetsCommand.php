<?php

namespace App\Console\Commands;

use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use Illuminate\Console\Command;

class SyncBiathlonTweetsCommand extends Command
{
    protected $signature = 'app:sync-biathlon-tweets';
    protected $description = 'Syncs latest biathlon tweets, metrics, and insights from configured providers into the database';

    public function handle(BiathlonTweetService $service): int
    {
        $this->line('');
        $this->info('🚀 Starting Biathlon Social & Telemetry Synchronizer');
        $this->line('');

        $providers = $service->getProviders();
        $this->line('<fg=yellow;options=bold>📋 Configured Providers to Sync (' . count($providers) . ' total):</>');
        foreach ($providers as $idx => $prov) {
            $delayNote = ($idx > 0 && ($prov['delay'] ?? 0) > 0) ? " (random delay: 10-15s)" : "";
            $this->line(sprintf('  <fg=gray>%d.</> <fg=cyan;options=bold>%s</><fg=gray>%s</>', $idx + 1, $prov['name'], $delayNote));
        }
        $this->line('');

        $totalBefore = Tweet::count();
        $this->line("<fg=gray>Total tweets in database before sync: <fg=white;options=bold>{$totalBefore}</></>");
        $this->line(str_repeat('─', 65));

        $service->syncTweets(function (string $event, array $data) {
            $idx = $data['index'] ?? 1;
            $tot = $data['total'] ?? 1;
            $provider = $data['provider'] ?? ($data['result']['provider'] ?? []);
            $name = $provider['name'] ?? 'Provider';

            if ($event === 'waiting' && ($data['delay'] ?? 0) > 0) {
                $this->line(sprintf(
                    '⏳ <fg=yellow>[%d/%d]</> Waiting <fg=yellow;options=bold>%d seconds</> to start sync for <fg=cyan>%s</> (throttle protection)...',
                    $idx,
                    $tot,
                    $data['delay'],
                    $name
                ));
            } elseif ($event === 'starting') {
                $this->line(sprintf(
                    '▶️  <fg=blue>[%d/%d]</> Started syncing <fg=cyan;options=bold>%s</> | DB tweets before: <fg=white;options=bold>%d</>',
                    $idx,
                    $tot,
                    $name,
                    $data['before_count']
                ));
            } elseif ($event === 'finished') {
                $res = $data['result'];
                $new = $res['newly_added'];
                $diffText = $new > 0 ? "<fg=green;options=bold>+{$new} newly added</>" : '<fg=gray>0 newly added (up to date)</>';

                $this->line(sprintf(
                    '✅ <fg=green>[%d/%d]</> Finished syncing <fg=cyan>%s</> in <fg=yellow>%.2fs</> | DB tweets after: <fg=white;options=bold>%d</> (%s)',
                    $idx,
                    $tot,
                    $name,
                    $res['duration'],
                    $res['after_count'],
                    $diffText
                ));
                $this->line('');
            }
        });

        $totalAfter = Tweet::count();
        $totalNew = max(0, $totalAfter - $totalBefore);

        $this->line(str_repeat('─', 65));
        $this->info("✨ All tweet providers synced successfully!");
        $this->line(sprintf(
            '📊 <fg=white;options=bold>Summary:</> DB tweets before: <fg=yellow;options=bold>%d</> | DB tweets after: <fg=green;options=bold>%d</> | Newly added: <fg=green;options=bold>+%d</>',
            $totalBefore,
            $totalAfter,
            $totalNew
        ));
        $this->line('');

        return self::SUCCESS;
    }
}
