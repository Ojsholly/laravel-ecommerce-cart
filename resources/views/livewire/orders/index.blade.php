<?php

use App\Models\Order;
use App\Services\PriceCalculationService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'orders' => Order::where('user_id', auth()->id())
                ->with('items')
                ->latest()
                ->paginate(10),
            'priceService' => app(PriceCalculationService::class),
        ];
    }
}; ?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">My Orders</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">View and track your order history</p>
            </div>
        </div>
    </div>

    @if($orders->isEmpty())
        <flux:card class="text-center py-12">
            <div class="flex flex-col items-center gap-4">
                <flux:icon.shopping-bag class="size-16 text-zinc-400" />
                <flux:heading size="lg">No orders yet</flux:heading>
                <flux:subheading>Start shopping to see your orders here</flux:subheading>
                <flux:button href="{{ route('products.index') }}" wire:navigate variant="primary" class="mt-4">
                    Browse Products
                </flux:button>
            </div>
        </flux:card>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <flux:card>
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <flux:heading size="lg">Order #{{ $order->order_number }}</flux:heading>
                                <flux:badge :color="$order->status->color()" size="sm">
                                    {{ $order->status->label() }}
                                </flux:badge>
                            </div>
                            
                            <div class="flex flex-wrap gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-4" />
                                    <span>{{ $order->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:icon.shopping-bag class="size-4" />
                                    <span>{{ $order->items->count() }} {{ $order->items->count() == 1 ? 'item' : 'items' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <flux:icon.currency-dollar class="size-4" />
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $priceService->formatPrice($order->total) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <flux:button 
                                href="{{ route('orders.show', $order) }}" 
                                wire:navigate 
                                variant="primary"
                                size="sm"
                            >
                                View Details
                            </flux:button>
                        </div>
                    </div>

                    {{-- Order Items Preview --}}
                    <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="flex gap-2 overflow-x-auto">
                            @foreach($order->items->take(4) as $item)
                                <div class="flex-shrink-0">
                                    <div class="h-16 w-16 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        @if($item->product_snapshot['primary_image'] ?? null)
                                            <img 
                                                src="{{ $item->product_snapshot['primary_image']['url'] }}" 
                                                alt="{{ $item->product_snapshot['primary_image']['alt'] }}" 
                                                class="h-full w-full object-cover"
                                            />
                                        @else
                                            <div class="flex h-full items-center justify-center text-zinc-400">
                                                <flux:icon.photo class="size-6" />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if($order->items->count() > 4)
                                <div class="flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    +{{ $order->items->count() - 4 }}
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
