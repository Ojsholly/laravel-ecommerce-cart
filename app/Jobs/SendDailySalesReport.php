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

        $totalItems = Order::completed()
            ->whereDate('orders.created_at', $this->date)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');

        $stats = [
            'total_orders' => $orderStats->total_orders ?? 0,
            'total_revenue' => $orderStats->total_revenue ?? '0.00',
            'total_items' => $totalItems ?: 0,
        ];

        $recentOrders = (clone $query)->latest()
            ->limit(10)
            ->select('id', 'order_number', 'total', 'created_at')
            ->get();

        $topProductsData = Order::completed()
            ->whereDate('orders.created_at', $this->date)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_id, sum(order_items.quantity) as total_quantity, sum(order_items.quantity * order_items.price_snapshot) as total_sales')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $topProducts = $topProductsData->map(function ($item) {
            $orderItem = \App\Models\OrderItem::where('product_id', $item->product_id)
                ->whereHas('order', function ($query) {
                    $query->completed()->whereDate('created_at', $this->date);
                })
                ->first();

            $snapshot = $orderItem ? $orderItem->product_snapshot : [];

            return [
                'name' => $snapshot['name'] ?? 'Unknown Product',
                'quantity' => $item->total_quantity,
                'revenue' => number_format((float) $item->total_sales, 2),
            ];
        });

        Mail::to($adminEmail)->send(new DailySalesReport($stats, $recentOrders, $topProducts, $this->date));
    }
}
