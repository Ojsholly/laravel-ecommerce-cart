<?php

namespace App\Jobs;

use App\Mail\DailySalesReport;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendDailySalesReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $date
    ) {}

    public function handle(): void
    {
        $adminEmail = $this->getAdminEmail();

        if (! $adminEmail) {
            return;
        }

        $stats = $this->calculateStats();
        $recentOrders = $this->getRecentOrders();
        $topProducts = $this->getTopSellingProducts();

        Mail::to($adminEmail)->send(new DailySalesReport($stats, $recentOrders, $topProducts, $this->date));
    }

    private function getAdminEmail(): ?string
    {
        return config('mail.admin_email');
    }

    private function getCompletedOrdersQuery(): Builder
    {
        return Order::completed()->whereDate('created_at', $this->date);
    }

    private function calculateStats(): array
    {
        $orderStats = $this->getCompletedOrdersQuery()
            ->selectRaw('count(*) as total_orders, sum(total) as total_revenue')
            ->first();

        $totalItems = $this->getTotalItemsSold();

        return [
            'total_orders' => $orderStats->total_orders ?? 0,
            'total_revenue' => $orderStats->total_revenue ?? '0.00',
            'total_items' => $totalItems,
        ];
    }

    private function getTotalItemsSold(): int
    {
        $totalItems = Order::completed()
            ->whereDate('orders.created_at', $this->date)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');

        return $totalItems ?: 0;
    }

    private function getRecentOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getCompletedOrdersQuery()
            ->latest()
            ->limit(10)
            ->select('id', 'order_number', 'total', 'created_at')
            ->get();
    }

    private function getTopSellingProducts(): Collection
    {
        $topProductsData = $this->getTopProductsData();

        return $topProductsData->map(fn ($item) => $this->formatTopProduct($item));
    }

    private function getTopProductsData(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::completed()
            ->whereDate('orders.created_at', $this->date)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_id, sum(order_items.quantity) as total_quantity, sum(order_items.quantity * order_items.price_snapshot) as total_sales')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();
    }

    private function formatTopProduct(object $item): array
    {
        $snapshot = $this->getProductSnapshot($item->product_id);

        return [
            'name' => $snapshot['name'] ?? 'Unknown Product',
            'quantity' => $item->total_quantity,
            'revenue' => number_format((float) $item->total_sales, 2),
        ];
    }

    private function getProductSnapshot(int $productId): array
    {
        $orderItem = OrderItem::where('product_id', $productId)
            ->whereHas('order', function (Builder $query) {
                /** @phpstan-ignore method.notFound */
                $query->completed()->whereDate('created_at', $this->date);
            })
            ->first();

        return $orderItem ? $orderItem->product_snapshot : [];
    }
}
