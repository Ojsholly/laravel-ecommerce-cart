<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $stats,
        public Collection $recentOrders,
        public string $date
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Daily Sales Report - {$this->date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-sales-report',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
