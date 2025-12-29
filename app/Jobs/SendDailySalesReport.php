<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $date
    ) {}

    public function handle(): void
    {
        $adminEmail = config('mail.admin_email', env('MAIL_ADMIN_EMAIL'));

        if (! $adminEmail) {
            return;
        }

        $query = Order::completed()->whereDate('created_at', $this->date);

        $stats = [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total'),
            'total_items' => \DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->whereDate('orders.created_at', $this->date)
                ->sum('order_items.quantity'),
        ];

        $recentOrders = $query->latest()
            ->limit(10)
            ->select('id', 'order_number', 'total', 'created_at')
            ->get();

        Mail::to($adminEmail)->send(new DailySalesReport($stats, $recentOrders, $this->date));
    }
}
