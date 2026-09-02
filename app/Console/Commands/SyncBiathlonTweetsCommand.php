<?php

namespace App\Console\Commands;

use App\Mail\TweetSyncReportMail;
use App\Models\Tweet;
use App\Services\BiathlonTweetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SyncBiathlonTweetsCommand extends Command
{
    protected $signature = 'app:sync-biathlon-tweets {--no-mail : Skip sending email report} {--email= : Override recipient email}';
    protected $description = 'Syncs latest biathlon tweets, metrics, and insights from configured providers into the database and emails a detailed report';

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
        $startTime = microtime(true);
        $providersSummary = [];

        $this->line("<fg=gray>Total tweets in database before sync: <fg=white;options=bold>{$totalBefore}</></>");
        $this->line(str_repeat('─', 65));

        $service->syncTweets(function (string $event, array $data) use (&$providersSummary) {
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
                $providersSummary[] = $res;
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
        $totalDuration = round(microtime(true) - $startTime, 2);

        $this->line(str_repeat('─', 65));
        $this->info("✨ All tweet providers synced successfully!");
        $this->line(sprintf(
            '📊 <fg=white;options=bold>Summary:</> DB tweets before: <fg=yellow;options=bold>%d</> | DB tweets after: <fg=green;options=bold>%d</> | Newly added: <fg=green;options=bold>+%d</> | Time: <fg=yellow;options=bold>%.2fs</>',
            $totalBefore,
            $totalAfter,
            $totalNew,
            $totalDuration
        ));
        $this->line('');

        // Prepare and send detailed email report
        if (!$this->option('no-mail')) {
            $recipient = $this->option('email') ?: env('ADMIN_NOTIFICATION_EMAIL', '7924@inbox.lv');

            $handleCounts = Tweet::query()
                ->selectRaw('author_handle, count(*) as count')
                ->groupBy('author_handle')
                ->pluck('count', 'author_handle')
                ->toArray();

            $withMediaCount = Tweet::whereNotNull('media_urls')->count();
            $recentTweets = Tweet::query()->orderByDesc('published_at')->take(4)->get();

            $syncData = [
                'executed_at' => now()->format('d M Y H:i:s T'),
                'recipient' => $recipient,
                'total_duration' => $totalDuration,
                'db_before' => $totalBefore,
                'db_after' => $totalAfter,
                'newly_added' => $totalNew,
                'providers_summary' => $providersSummary,
                'handle_counts' => $handleCounts,
                'with_media_count' => $withMediaCount,
                'recent_tweets' => $recentTweets,
            ];

            try {
                Mail::to($recipient)->send(new TweetSyncReportMail($syncData));
                $this->info("📬 Detailed sync report email sent to {$recipient}");
            } catch (\Exception $e) {
                $this->warn("⚠️ Could not send sync report email: " . $e->getMessage());
                Log::warning("Could not send tweet sync report email: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
