<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Cron & Command Status Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }
        .container { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0 0; font-size: 13px; color: #94a3b8; }
        .content { padding: 32px; }
        .section-title { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 24px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; }
        .card { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .cmd-name { font-weight: 700; font-size: 14px; color: #0f172a; font-family: monospace; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #e0f2fe; color: #0369a1; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .stat-box { background: #f1f5f9; border-radius: 10px; padding: 12px; text-align: center; }
        .stat-number { font-size: 20px; font-weight: 800; color: #0f172a; }
        .stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 12px; color: #94a3b8; text-align: center; }
        ul { margin: 6px 0; padding-left: 20px; font-size: 13px; color: #334155; }
        li { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎯 Biathlon System & Cron Report</h1>
            <p>Execution Summary for {{ $reportData['generated_at'] }} (Past 24 Hours)</p>
        </div>

        <div class="content">
            <!-- Platform Overview -->
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-number">{{ $reportData['stats']['total_tweets'] ?? 0 }}</div>
                    <div class="stat-label">Total Tweets</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $reportData['stats']['total_forecasts'] ?? 0 }}</div>
                    <div class="stat-label">Contests</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $reportData['stats']['total_athletes'] ?? 0 }}</div>
                    <div class="stat-label">Athletes DB</div>
                </div>
            </div>

            <!-- High-Frequency Commands (Every minute / 5 min) -->
            <div class="section-title">⚡ High-Frequency Cron Routines</div>

            <!-- app:read-forecast-results-command -->
            <div class="card">
                <div class="card-header">
                    <span class="cmd-name">app:read-forecast-results-command</span>
                    <span class="badge badge-green">Every 1 Minute</span>
                </div>
                <ul>
                    <li><strong>Status:</strong> Active & Running continuously</li>
                    <li><strong>Completed Forecasts in 24h:</strong> {{ $reportData['forecasts_completed_today'] ?? 0 }}</li>
                    <li><strong>Points / Awards Calculated:</strong> {{ $reportData['awards_calculated_today'] ?? 0 }}</li>
                </ul>
            </div>

            <!-- app:read-competition-results -->
            <div class="card">
                <div class="card-header">
                    <span class="cmd-name">app:read-competition-results</span>
                    <span class="badge badge-green">Every 5 Minutes</span>
                </div>
                <ul>
                    <li><strong>Status:</strong> Active (Syncing official IBU results & split times)</li>
                    <li><strong>Race Results Recorded Today:</strong> {{ $reportData['results_handled_today'] ?? 0 }}</li>
                </ul>
            </div>

            <!-- Periodic Commands -->
            <div class="section-title">🕒 Periodic & Daily Jobs</div>

            <!-- app:sync-biathlon-tweets -->
            <div class="card">
                <div class="card-header">
                    <span class="cmd-name">app:sync-biathlon-tweets</span>
                    <span class="badge badge-blue">Every 4 Hours</span>
                </div>
                <ul>
                    <li><strong>Status:</strong> Multi-provider synchronization operational</li>
                    <li><strong>New / Synced Posts in 24h:</strong> {{ $reportData['tweets_synced_today'] ?? 0 }}</li>
                    <li><strong>Current Breakdown:</strong>
                        @foreach($reportData['tweet_handles'] ?? [] as $handle => $cnt)
                            <span style="display:inline-block; margin-right:8px; font-weight:600;">{{ '@' . $handle }}: {{ $cnt }}</span>
                        @endforeach
                    </li>
                </ul>
            </div>

            <!-- app:generate-missing-forecasts & app:read-athletes -->
            <div class="card">
                <div class="card-header">
                    <span class="cmd-name">Daily Maintenance Jobs</span>
                    <span class="badge badge-blue">Daily</span>
                </div>
                <ul>
                    <li><code>app:generate-missing-forecasts</code>: Pre-generates prediction contests for 3-month horizon</li>
                    <li><code>app:read-athletes</code>: Refreshes athlete biographies, accuracy & speed stats</li>
                </ul>
            </div>

            @if(!empty($reportData['recent_warnings']))
                <div class="section-title" style="color: #e11d48;">⚠️ Log Notices & Warnings</div>
                <div class="card" style="background: #fff1f2; border-color: #fecdd3;">
                    <ul style="color: #9f1239;">
                        @foreach($reportData['recent_warnings'] as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            Automated status monitor for <strong>biatlons.kilograms.lv</strong> &bull; Sent to <strong>{{ $reportData['recipient'] }}</strong>
        </div>
    </div>
</body>
</html>
