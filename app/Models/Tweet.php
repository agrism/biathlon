<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tweet_id
 * @property string $author_name
 * @property string $author_handle
 * @property ?string $author_avatar
 * @property string $content
 * @property ?string $translated_content
 * @property ?string $source_language
 * @property ?array $media_urls
 * @property int $likes_count
 * @property int $retweets_count
 * @property ?string $tweet_url
 * @property bool $should_hide
 * @property Carbon $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Tweet extends Model
{
    protected $fillable = [
        'tweet_id',
        'author_name',
        'author_handle',
        'author_avatar',
        'content',
        'translated_content',
        'source_language',
        'media_urls',
        'likes_count',
        'retweets_count',
        'tweet_url',
        'should_hide',
        'published_at',
    ];

    protected $attributes = [
        'should_hide' => false,
    ];

    protected $casts = [
        'media_urls' => 'array',
        'published_at' => 'datetime',
        'likes_count' => 'integer',
        'retweets_count' => 'integer',
        'should_hide' => 'boolean',
    ];

    public function hasTranslation(): bool
    {
        return !empty($this->translated_content)
            && !empty($this->source_language)
            && strtolower($this->source_language) !== 'en';
    }

    public function getFormattedContent(): string
    {
        return $this->formatText($this->content);
    }

    public function getFormattedTranslatedContent(): string
    {
        return $this->formatText($this->translated_content ?: $this->content);
    }

    protected ?\App\Models\Athlete $cachedMentionedAthlete = null;
    protected bool $mentionedAthleteChecked = false;

    /**
     * Find first mentioned athlete in tweet content to render official IBU / BiathlonWorld photo
     */
    public function findFirstMentionedAthlete(): ?\App\Models\Athlete
    {
        if ($this->mentionedAthleteChecked) {
            return $this->cachedMentionedAthlete;
        }

        $this->mentionedAthleteChecked = true;

        if ($this->id) {
            $athleteId = \Illuminate\Support\Facades\Cache::remember(
                "tweet_athlete_id_v3_{$this->id}",
                86400 * 7,
                fn() => app(\App\Services\BiathlonTweetService::class)->findMentionedAthleteInText($this->content)?->id
            );

            if ($athleteId) {
                $this->cachedMentionedAthlete = app(\App\Services\BiathlonTweetService::class)->getAthleteById($athleteId);
            }
        } else {
            $this->cachedMentionedAthlete = app(\App\Services\BiathlonTweetService::class)->findMentionedAthleteInText($this->content);
        }

        return $this->cachedMentionedAthlete;
    }

    protected function formatText(?string $raw): string
    {
        if (empty($raw)) {
            return '';
        }

        // 1. Decode HTML entities multiple times (handles &#8217;, &#8220;, &amp;, &quot;, etc.)
        $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $decoded = str_replace(
            ['&#8217;', '&#8216;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#038;', '&amp;', '&nbsp;', '&quot;', '&apos;'],
            ["’", "‘", "“", "”", "–", "—", "&", "&", " ", '"', "'"],
            $decoded
        );

        // Clean double protocols if any
        $decoded = preg_replace('~https?://https?://~i', 'https://', $decoded);

        // 2. Expand squashed table fragments from scraped web articles
        $decoded = preg_replace('/Nations Medal Table\s*Nations Medals/iu', "🏆 Nations Medal Table:\n", $decoded);
        $decoded = preg_replace('/Athletes \([^)]+\) Medal Table\s*Athlete Medals/iu', "\n🏅 Athletes Medal Table:\n", $decoded);
        $decoded = preg_replace('/([🥇🥈🥉]+)\s+([A-ZÀ-ÖØ-ß][a-zA-ZÀ-ÿ\s\-\.]{2,30})(?=\s+[🥇🥈🥉])/u', "$1\n• $2", $decoded);

        $lines = explode("\n", trim($decoded));
        $formattedLines = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Article Header: 📝 Title
            if ($index === 0 && preg_match('/^📝\s*(.*)$/u', $trimmed, $m)) {
                $titleText = e(trim($m[1]));
                $formattedLines[] = '<div class="font-bold text-slate-900 text-sm mb-1.5 pb-1 border-b border-slate-100 flex items-center gap-1.5"><span class="text-sky-600 text-xs">📝</span><span>' . $titleText . '</span></div>';
                continue;
            }

            // Escape line content
            $escaped = e($trimmed);

            // Convert URLs to clickable links with shortened visible text
            $escaped = preg_replace_callback(
                '~(https?://[^\s<]+|(?:www\.|[a-zA-Z0-9-]+\.(?:com|org|net|lv|no|de|fr|info|io|co|me|tv|social))(?:\/[^\s<]*)?)~i',
                function ($matches) {
                    $url = $matches[1];
                    $cleanUrl = rtrim($url, '.,;:!?');
                    $href = (stripos($cleanUrl, 'http://') === 0 || stripos($cleanUrl, 'https://') === 0)
                        ? $cleanUrl
                        : 'https://' . $cleanUrl;

                    $displayUrl = preg_replace('~^https?://(?:www\.)?~i', '', $cleanUrl);
                    if (mb_strlen($displayUrl) > 34) {
                        $displayUrl = mb_substr($displayUrl, 0, 31) . '…';
                    }

                    return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" title="' . $href . '" class="text-sky-600 hover:underline font-semibold">' . $displayUrl . '</a>';
                },
                $escaped
            );

            // Format @mentions as styled badges
            $escaped = preg_replace(
                '/(^|\s)@([A-Za-z0-9_]+)/',
                '$1<span class="text-sky-600 font-bold">@$2</span>',
                $escaped
            );

            // Format #hashtags as styled tags
            $escaped = preg_replace(
                '/(^|\s)#([A-Za-z0-9_]+)/u',
                '$1<span class="text-sky-600 font-semibold">#$2</span>',
                $escaped
            );

            // Numbered Rankings (e.g. "1) Eric Perrot...", "1. Johannes...")
            if (preg_match('/^([1-9]|1[0-9]|20)[\)\.]\s*(.*)$/u', $trimmed, $rm)) {
                $rank = (int)$rm[1];
                $restEscaped = preg_replace('/^([1-9]|1[0-9]|20)[\)\.]\s*/u', '', $escaped);

                $badgeStyle = match ($rank) {
                    1 => 'bg-amber-100 text-amber-800 border border-amber-300 font-black',
                    2 => 'bg-slate-200 text-slate-800 border border-slate-300 font-black',
                    3 => 'bg-amber-700/15 text-amber-900 border border-amber-700/30 font-black',
                    default => 'bg-slate-100 text-slate-600 font-bold',
                };

                $formattedLines[] = '<div class="flex items-center gap-2 my-0.5 py-0.5 text-xs"><span class="inline-flex items-center justify-center w-5 h-5 rounded-[4px] ' . $badgeStyle . ' text-[10px] flex-shrink-0 shadow-2xs">' . $rank . '</span><span class="font-medium truncate">' . $restEscaped . '</span></div>';
                continue;
            }

            // Bullet List Items (e.g. "- Highlights...", "– Sunday...", "• Item...")
            if (preg_match('/^[-–—•]\s*(.*)$/u', $trimmed)) {
                $bulletContent = preg_replace('/^[-–—•]\s*/u', '', $escaped);
                $formattedLines[] = '<div class="flex items-start gap-1.5 my-0.5 text-xs"><span class="text-sky-500 font-bold leading-none mt-0.5 text-xs flex-shrink-0">•</span><span>' . $bulletContent . '</span></div>';
                continue;
            }

            // Country / Athlete Medals Row (e.g. "France: 🥇🥇🥇..." or "• France: 🥇...")
            if (preg_match('/^([A-ZÀ-ÖØ-ß][a-zA-ZÀ-ÿ\s\-\.]{2,30}):\s*([🥇🥈🥉\s]+)$/u', $trimmed, $mm)) {
                $nameEscaped = e(trim($mm[1]));
                $medals = trim($mm[2]);
                $formattedLines[] = '<div class="flex items-center justify-between gap-2 py-0.5 my-0.5 text-xs border-b border-slate-100 last:border-0"><span class="font-semibold text-slate-700">' . $nameEscaped . '</span><span class="tracking-widest flex-shrink-0">' . $medals . '</span></div>';
                continue;
            }

            // Section titles (e.g. "🏆 Nations Medal Table:", "🏅 Athletes Medal Table:")
            if (preg_match('/^(?:🏆|🏅|Dates:|Schedule:)/u', $trimmed)) {
                $formattedLines[] = '<div class="font-bold text-slate-800 text-xs mt-2 mb-1">' . $escaped . '</div>';
                continue;
            }

            $formattedLines[] = $escaped;
        }

        $result = '';
        foreach ($formattedLines as $i => $line) {
            if ($i > 0 && !str_starts_with($line, '<div') && !str_starts_with($formattedLines[$i - 1], '<div')) {
                $result .= "<br>\n";
            }
            $result .= $line;
        }

        return $result;
    }
}
