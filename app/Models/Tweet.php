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
 * @property ?array $media_urls
 * @property int $likes_count
 * @property int $retweets_count
 * @property ?string $tweet_url
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
        'media_urls',
        'likes_count',
        'retweets_count',
        'tweet_url',
        'published_at',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'published_at' => 'datetime',
        'likes_count' => 'integer',
        'retweets_count' => 'integer',
    ];

    public function getFormattedContent(): string
    {
        $content = $this->content;

        // Clean double protocols if any
        $content = preg_replace('~https?://https?://~i', 'https://', $content);

        $content = e(trim($content));

        // Convert URLs (both https?:// and domain paths like penaltyloop.com/..., www.example.com, etc.) to clickable links
        $content = preg_replace_callback(
            '~(https?://[^\s<]+|(?:www\.|[a-zA-Z0-9-]+\.(?:com|org|net|lv|no|de|fr|info|io|co|me|tv|social))(?:\/[^\s<]*)?)~i',
            function ($matches) {
                $url = $matches[1];
                $cleanUrl = rtrim($url, '.,;:!?');
                $href = (stripos($cleanUrl, 'http://') === 0 || stripos($cleanUrl, 'https://') === 0)
                    ? $cleanUrl
                    : 'https://' . $cleanUrl;

                return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="text-sky-600 hover:underline font-semibold break-all">' . $url . '</a>';
            },
            $content
        );

        // Format @mentions as styled badges
        $content = preg_replace(
            '/(^|\s)@([A-Za-z0-9_]+)/',
            '$1<span class="text-sky-600 font-bold">@$2</span>',
            $content
        );

        // Format #hashtags as styled tags (keeping hashtags intact)
        $content = preg_replace(
            '/(^|\s)#([A-Za-z0-9_]+)/u',
            '$1<span class="text-sky-600 font-semibold">#$2</span>',
            $content
        );

        return nl2br($content);
    }
}
