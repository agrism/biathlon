<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TweetSyncReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $syncData
    ) {}

    public function envelope(): Envelope
    {
        $time = now()->format('H:i d M Y');
        $newCount = $this->syncData['newly_added'] ?? 0;
        $diffText = $newCount > 0 ? "+{$newCount} New Posts" : "0 New (Up to date)";

        return new Envelope(
            subject: "🐦 Biathlon Tweet Sync Report [{$diffText}] - {$time}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tweet-sync-report',
        );
    }
}
