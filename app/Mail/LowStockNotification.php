<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Low Stock Alert: {$this->product->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low-stock-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
