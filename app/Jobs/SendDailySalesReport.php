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
        $adminEmail = config('mail.admin_email');

        if (! $adminEmail) {
            return;
        }

        $query = Order::completed()->whereDate('created_at', $this->date);

        $orderStats = (clone $query)->selectRaw('count(*) as total_orders, sum(total) as total_revenue')->first();

        $stats = [
            'total_orders' => $orderStats->total_orders ?? 0,
            'total_revenue' => $orderStats->total_revenue ?? '0.00',
            'total_items' => (clone $query)->join('order_items', 'orders.id', '=', 'order_items.order_id')->sum('order_items.quantity') ?? 0,
        ];

        $recentOrders = (clone $query)->latest()
            ->limit(10)
            ->select('id', 'order_number', 'total', 'created_at')
            ->get();

        Mail::to($adminEmail)->send(new DailySalesReport($stats, $recentOrders, $this->date));
    }
}
