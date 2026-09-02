<?php

namespace App\Console\Commands;

use App\Enums\Forecast\ForecastStatusEnum;
use App\Mail\DailyCronStatusReportMail;
use App\Models\Athlete;
use App\Models\EventCompetitionResult;
use App\Models\Forecast;
use App\Models\ForecastAward;
use App\Models\Tweet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyCronReportCommand extends Command
{
    protected $signature = 'app:send-daily-cron-report {--email= : Override recipient email} {--dry-run : Only display output without sending email}';
    protected $description = 'Sends a daily status and health report for all cron commands and background routines to the admin email';

    public function handle(): int
    {
        $recipient = $this->option('email') ?: env('ADMIN_NOTIFICATION_EMAIL', '7924@inbox.lv');
        $this->info("Compiling daily cron report for {$recipient}...");

        $since = Carbon::now()->subHours(24);

        // 1. Forecasts completed today
        $forecastsCompletedToday = Forecast::query()
            ->where('status', ForecastStatusEnum::COMPLETED)
            ->where('updated_at', '>=', $since)
            ->count();

        // 2. Awards calculated today
        $awardsCalculatedToday = ForecastAward::query()
            ->where('created_at', '>=', $since)
            ->count();

        // 3. Race results handled today
        $resultsHandledToday = EventCompetitionResult::query()
            ->where('updated_at', '>=', $since)
            ->count();

        // 4. Tweets synced today
        $tweetsSyncedToday = Tweet::query()
            ->where('updated_at', '>=', $since)
            ->count();

        $tweetHandles = Tweet::query()
            ->selectRaw('author_handle, count(*) as count')
            ->groupBy('author_handle')
            ->pluck('count', 'author_handle')
            ->toArray();

        // 5. Global platform stats
        $stats = [
            'total_tweets' => Tweet::count(),
            'total_forecasts' => Forecast::count(),
            'total_athletes' => Athlete::count(),
            'total_users' => User::count(),
        ];

        // 6. Recent log warnings (last 50 lines scan)
        $recentWarnings = [];
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile), -100);
            foreach ($lines as $line) {
                if (str_contains($line, 'WARNING:') || str_contains($line, 'ERROR:')) {
                    $recentWarnings[] = substr(trim($line), 0, 150) . '...';
                }
            }
            $recentWarnings = array_slice(array_unique($recentWarnings), -5);
        }

        $reportData = [
            'generated_at' => now()->format('d M Y H:i:s T'),
            'recipient' => $recipient,
            'forecasts_completed_today' => $forecastsCompletedToday,
            'awards_calculated_today' => $awardsCalculatedToday,
            'results_handled_today' => $resultsHandledToday,
            'tweets_synced_today' => $tweetsSyncedToday,
            'tweet_handles' => $tweetHandles,
            'stats' => $stats,
            'recent_warnings' => $recentWarnings,
        ];

        $this->table(['Metric', 'Value'], [
            ['Recipient', $recipient],
            ['Forecasts Completed (24h)', $forecastsCompletedToday],
            ['Awards Calculated (24h)', $awardsCalculatedToday],
            ['Race Results Handled (24h)', $resultsHandledToday],
            ['Tweets Synced (24h)', $tweetsSyncedToday],
            ['Total Tweets in DB', $stats['total_tweets']],
            ['Total Forecasts in DB', $stats['total_forecasts']],
            ['Total Athletes in DB', $stats['total_athletes']],
        ]);

        if ($this->option('dry-run')) {
            $this->warn('Dry-run enabled: Email was not sent.');
            return self::SUCCESS;
        }

        try {
            Mail::to($recipient)->send(new DailyCronStatusReportMail($reportData));
            $this->info("✅ Daily cron status report successfully sent to {$recipient}");
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            Log::error("Failed to send daily cron status report: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
