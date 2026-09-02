<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Cron & Command Status Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.65; color: #0f172a; background-color: #f1f5f9; margin: 0; padding: 24px 12px; }
        .container { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 18px; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04); overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 28px 32px; border-bottom: 3px solid #16a34a; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.3px; color: #ffffff; }
        .header p { margin: 6px 0 0 0; font-size: 14px; color: #cbd5e1; }
        .content { padding: 32px; }
        .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.75px; color: #475569; margin-top: 28px; margin-bottom: 14px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; }
        .card { background: #f8fafc; border-radius: 14px; padding: 18px; margin-bottom: 14px; border: 1px solid #e2e8f0; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .cmd-name { font-weight: 800; font-size: 14.5px; color: #0f172a; font-family: monospace; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 9999px; font-size: 12px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #e0f2fe; color: #0369a1; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 26px; }
        .stat-box { background: #f8fafc; border-radius: 12px; padding: 16px 12px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-number { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1.2; margin-bottom: 4px; }
        .stat-label { font-size: 12px; color: #475569; text-transform: uppercase; font-weight: 700; letter-spacing: 0.3px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; font-size: 13px; color: #64748b; text-align: center; }
        ul { margin: 8px 0; padding-left: 22px; font-size: 14.5px; color: #334155; line-height: 1.7; }
        li { margin-bottom: 6px; }
        @media only screen and (max-width: 600px) {
            .content { padding: 20px 16px !important; }
            .header { padding: 20px 18px !important; }
            .stat-grid { grid-template-columns: 1fr !important; gap: 10px !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎯 Biathlon System & Cron Report</h1>
            <p>Execution Summary for <strong>{{ $reportData['generated_at'] }}</strong> (Past 24 Hours)</p>
        </div>

        <div class="content">
            <!-- Platform Overview -->
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-number" style="color: #0284c7;">{{ $reportData['stats']['total_tweets'] ?? 0 }}</div>
                    <div class="stat-label">Total Tweets</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #16a34a;">{{ $reportData['stats']['total_forecasts'] ?? 0 }}</div>
                    <div class="stat-label">Contests</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #6366f1;">{{ $reportData['stats']['total_athletes'] ?? 0 }}</div>
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
                    <li><strong>Completed Forecasts in 24h:</strong> <strong style="color: #0f172a;">{{ $reportData['forecasts_completed_today'] ?? 0 }}</strong></li>
                    <li><strong>Points / Awards Calculated:</strong> <strong style="color: #0f172a;">{{ $reportData['awards_calculated_today'] ?? 0 }}</strong></li>
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
                    <li><strong>Race Results Recorded Today:</strong> <strong style="color: #0f172a;">{{ $reportData['results_handled_today'] ?? 0 }}</strong></li>
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
                    <li><strong>New / Synced Posts in 24h:</strong> <strong style="color: #0f172a;">{{ $reportData['tweets_synced_today'] ?? 0 }}</strong></li>
                    <li><strong>Current Breakdown:</strong>
                        @foreach($reportData['tweet_handles'] ?? [] as $handle => $cnt)
                            <span style="display:inline-block; margin-right: 12px; font-weight: 600;">
                                <span style="color: #0284c7;">{{ '@' . $handle }}</span>: {{ $cnt }}
                            </span>
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
