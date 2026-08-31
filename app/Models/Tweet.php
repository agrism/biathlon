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

        // Strip words starting with #
        $content = preg_replace('/(^|\s)#[^\s]+/u', '', $content);

        $content = e(trim($content));

        // Convert URLs to generic external links
        $content = preg_replace(
            '~(https?://[^\s<]+)~',
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-sky-600 hover:underline font-semibold break-all">$1</a>',
            $content
        );

        // Format @mentions as styled badges
        $content = preg_replace(
            '/(^|\s)@([A-Za-z0-9_]+)/',
            '$1<span class="text-sky-600 font-bold">@$2</span>',
            $content
        );

        return nl2br($content);
    }
}
