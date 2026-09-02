<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Biathlon Tweet Sync Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 24px; }
        .container { max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0 0; font-size: 13px; color: #94a3b8; }
        .content { padding: 32px; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 24px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .stat-box { background: #f8fafc; border-radius: 10px; padding: 12px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-number { font-size: 22px; font-weight: 800; color: #0f172a; }
        .stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .provider-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        .provider-table th { text-align: left; padding: 10px 12px; background: #f1f5f9; color: #475569; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .provider-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-gray { background: #f1f5f9; color: #64748b; }
        .tweet-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 10px; }
        .tweet-meta { font-size: 11px; color: #64748b; margin-bottom: 4px; font-weight: 600; display: flex; justify-content: space-between; }
        .tweet-text { font-size: 13px; color: #1e293b; margin: 0; white-space: pre-wrap; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🐦 Biathlon Tweet & Social Ingestion Report</h1>
            <p>Execution Time: {{ $syncData['executed_at'] }} &bull; Runtime: {{ $syncData['total_duration'] }}s</p>
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
                            <td>{{ $prov['duration'] }}s</td>
                            <td>{{ $prov['before_count'] }} &rarr; {{ $prov['after_count'] }}</td>
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
            <p style="font-size: 13px; color: #475569; margin-top: 4px;">
                @foreach($syncData['handle_counts'] ?? [] as $handle => $count)
                    <span style="display:inline-block; margin-right: 12px; font-weight: 600;">
                        <span style="color: #0284c7;">{{ '@' . $handle }}</span>: {{ $count }}
                    </span>
                @endforeach
                <span style="display:inline-block; font-weight: 600; color: #64748b;">
                    (With Images: {{ $syncData['with_media_count'] ?? 0 }})
                </span>
            </p>

            <!-- Latest Synced Tweets Snippets -->
            <div class="section-title">📝 Latest Synced Posts in Feed</div>
            @forelse($syncData['recent_tweets'] ?? [] as $tweet)
                <div class="tweet-card">
                    <div class="tweet-meta">
                        <span><strong>{{ $tweet->author_name }}</strong> ({{ '@' . $tweet->author_handle }})</span>
                        <span>{{ $tweet->published_at ? $tweet->published_at->format('d M Y H:i') : '' }}</span>
                    </div>
                    <p class="tweet-text">{{ Str::limit($tweet->content, 180) }}</p>
                    @if(!empty($tweet->media_urls))
                        <div style="margin-top: 6px; font-size: 11px; color: #0284c7; font-weight: 600;">
                            📷 {{ count($tweet->media_urls) }} media image(s) attached
                        </div>
                    @endif
                </div>
            @empty
                <p style="font-size: 12px; color: #94a3b8;">No posts found.</p>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="footer">
            Automated sync execution report for <strong>biatlons.kilograms.lv</strong> &bull; Sent to <strong>{{ $syncData['recipient'] }}</strong>
        </div>
    </div>
</body>
</html>
