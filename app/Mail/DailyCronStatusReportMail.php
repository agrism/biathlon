<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyCronStatusReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reportData
    ) {}

    public function envelope(): Envelope
    {
        $date = now()->format('d M Y');
        $hasErrors = !empty($this->reportData['errors']);
        $statusPrefix = $hasErrors ? '⚠️ [Issues Detected]' : '✅ [Healthy]';

        return new Envelope(
            subject: "{$statusPrefix} Biathlon Daily Cron & Command Status Report - {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-cron-report',
        );
    }
}
