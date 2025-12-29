<?php

namespace App\Jobs;

use App\Mail\LowStockNotification;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendLowStockNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product
    ) {}

    public function handle(): void
    {
        $adminEmail = config('mail.admin_email', env('MAIL_ADMIN_EMAIL'));

        if (! $adminEmail) {
            return;
        }

        Mail::to($adminEmail)->send(new LowStockNotification($this->product));
    }
}
