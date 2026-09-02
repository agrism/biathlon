<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biathlon Tweet Sync Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.65; color: #0f172a; background-color: #f1f5f9; margin: 0; padding: 24px 12px; }
        .container { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 18px; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04); overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 28px 32px; border-bottom: 3px solid #0284c7; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.3px; color: #ffffff; }
        .header p { margin: 6px 0 0 0; font-size: 14px; color: #cbd5e1; }
        .content { padding: 32px; }
        .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.75px; color: #475569; margin-top: 28px; margin-bottom: 14px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 26px; }
        .stat-box { background: #f8fafc; border-radius: 12px; padding: 16px 12px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-number { font-size: 26px; font-weight: 800; line-height: 1.2; margin-bottom: 4px; }
        .stat-label { font-size: 12px; color: #475569; text-transform: uppercase; font-weight: 700; letter-spacing: 0.3px; }
        .provider-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px; }
        .provider-table th { text-align: left; padding: 12px 14px; background: #f8fafc; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 13px; }
        .provider-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 9999px; font-size: 12px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tweet-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 14px; }
        .tweet-meta { font-size: 13px; color: #475569; margin-bottom: 8px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 6px; }
        .tweet-text { font-size: 14.5px; line-height: 1.65; color: #1e293b; margin: 0; white-space: pre-wrap; font-weight: 400; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; font-size: 13px; color: #64748b; text-align: center; }
        @media only screen and (max-width: 600px) {
            .content { padding: 20px 16px !important; }
            .header { padding: 20px 18px !important; }
            .stat-grid { grid-template-columns: 1fr !important; gap: 10px !important; }
            .provider-table th, .provider-table td { padding: 8px 10px !important; font-size: 13px !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🐦 Biathlon Tweet & Social Ingestion Report</h1>
            <p>Execution Time: <strong>{{ $syncData['executed_at'] }}</strong> &bull; Runtime: <strong>{{ $syncData['total_duration'] }}s</strong></p>
        </div>

        <div class="content">
            <!-- Top Metric Counters -->
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-number" style="color: #0284c7;">{{ $syncData['db_before'] }}</div>
                    <div class="stat-label">Tweets Before</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #16a34a;">{{ $syncData['db_after'] }}</div>
                    <div class="stat-label">Tweets After</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="{{ $syncData['newly_added'] > 0 ? 'color: #16a34a;' : 'color: #64748b;' }}">
                        {{ $syncData['newly_added'] > 0 ? '+' . $syncData['newly_added'] : '0' }}
                    </div>
                    <div class="stat-label">Newly Added</div>
                </div>
            </div>

            <!-- Provider Breakdown Table -->
            <div class="section-title">📋 Sync Step Details</div>
            <table class="provider-table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Duration</th>
                        <th>DB Before &rarr; After</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($syncData['providers_summary'] ?? [] as $prov)
                        <tr>
                            <td>
                                <strong>{{ $prov['provider']['name'] ?? 'Provider' }}</strong>
                            </td>
                            <td><span style="font-family: monospace; font-weight: 600;">{{ $prov['duration'] }}s</span></td>
                            <td>{{ $prov['before_count'] }} &rarr; <strong>{{ $prov['after_count'] }}</strong></td>
                            <td>
                                @if(($prov['newly_added'] ?? 0) > 0)
                                    <span class="badge badge-green">+{{ $prov['newly_added'] }} added</span>
                                @else
                                    <span class="badge badge-gray">Up to date</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Breakdown by Handle -->
            <div class="section-title">📊 Total Stored Breakdown by Handle</div>
            <p style="font-size: 14px; color: #334155; margin-top: 6px; line-height: 1.8;">
                @foreach($syncData['handle_counts'] ?? [] as $handle => $count)
                    <span style="display:inline-block; margin-right: 14px; font-weight: 600;">
                        <span style="color: #0284c7;">{{ '@' . $handle }}</span>: <strong style="color: #0f172a;">{{ $count }}</strong>
                    </span>
                @endforeach
                <span style="display:inline-block; font-weight: 600; color: #475569;">
                    &bull; (With Images: <strong style="color: #0f172a;">{{ $syncData['with_media_count'] ?? 0 }}</strong>)
                </span>
            </p>

            <!-- Latest Synced Tweets Snippets -->
            <div class="section-title">📝 Latest Synced Posts in Feed</div>
            @forelse($syncData['recent_tweets'] ?? [] as $tweet)
                <div class="tweet-card">
                    <div class="tweet-meta">
                        <span><strong style="color: #0f172a;">{{ $tweet->author_name }}</strong> <span style="color: #0284c7;">({{ '@' . $tweet->author_handle }})</span></span>
                        <span style="font-size: 12px; color: #64748b;">{{ $tweet->published_at ? $tweet->published_at->format('d M Y H:i') : '' }}</span>
                    </div>
                    <p class="tweet-text">{{ Str::limit($tweet->content, 220) }}</p>
                    @if(!empty($tweet->media_urls))
                        <div style="margin-top: 8px; font-size: 12px; color: #0284c7; font-weight: 700;">
                            📷 {{ count($tweet->media_urls) }} media photo(s) attached
                        </div>
                    @endif
                </div>
            @empty
                <p style="font-size: 14px; color: #94a3b8;">No posts found.</p>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="footer">
            Automated sync execution report for <strong>biatlons.kilograms.lv</strong> &bull; Sent to <strong>{{ $syncData['recipient'] }}</strong>
        </div>
    </div>
</body>
</html>
