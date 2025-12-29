<?php

use App\Models\Order;
use App\Services\PriceCalculationService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public Order $order;
    public $priceService;

    public function mount(): void
    {
        if ($this->order->user_id !== auth()->id()) {
            abort(403);
        }

        $this->order->load('items.product');
        $this->priceService = app(PriceCalculationService::class);
    }
}; ?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">Order Confirmation</flux:heading>
        <flux:subheading>Order #{{ $order->order_number }}</flux:subheading>

        <x-slot:actions>
            <flux:badge :color="$order->status->color()" size="lg">
                {{ $order->status->label() }}
            </flux:badge>
        </x-slot:actions>
    </flux:header>

    <div class="mb-6 rounded-lg bg-green-50 p-6 dark:bg-green-900/20">
        <div class="flex items-center gap-3">
            <flux:icon.check-circle class="size-8 text-green-600 dark:text-green-400" />
            <div>
                <flux:heading size="lg" class="text-green-900 dark:text-green-100">Thank you for your order!</flux:heading>
                <flux:text class="text-green-700 dark:text-green-300">Your order has been placed successfully.</flux:text>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Order Items</flux:heading>

                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 border-b border-zinc-200 pb-4 last:border-0 dark:border-zinc-700">
                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                @if($item->product_snapshot['primary_image'] ?? null)
                                    <img 
                                        src="{{ $item->product_snapshot['primary_image']['url'] }}" 
                                        alt="{{ $item->product_snapshot['primary_image']['alt'] }}" 
                                        class="h-full w-full object-cover"
                                    />
                                @endif
                            </div>

                            <div class="flex flex-1 justify-between">
                                <div>
                                    <flux:heading size="sm">{{ $item->product_name }}</flux:heading>
                                    <flux:text class="mt-1 text-sm">Quantity: {{ $item->quantity }}</flux:text>
                                    <flux:text class="mt-1 text-sm">{{ $priceService->formatPrice($item->price_snapshot) }} each</flux:text>
                                </div>
                                <flux:text class="font-bold">{{ $priceService->formatPrice($item->getSubtotal()) }}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">Order Information</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="font-medium">Order Number</flux:text>
                        <flux:text>{{ $order->order_number }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="font-medium">Order Date</flux:text>
                        <flux:text>{{ $order->created_at->format('M d, Y h:i A') }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="font-medium">Status</flux:text>
                        <flux:badge :color="$order->status->color()" size="sm">
                            {{ $order->status->label() }}
                        </flux:badge>
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="lg:col-span-1">
            <flux:card class="sticky top-4">
                <flux:heading size="lg" class="mb-4">Order Summary</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text>Subtotal</flux:text>
                        <flux:text>{{ $priceService->formatPrice($order->subtotal) }}</flux:text>
                    </div>

                    @if($order->pricing_breakdown)
                        @foreach($order->pricing_breakdown as $key => $item)
                            <div class="flex justify-between text-sm">
                                <flux:text>{{ $item['label'] }}</flux:text>
                                <flux:text>{{ $priceService->formatPrice($item['amount']) }}</flux:text>
                            </div>
                        @endforeach
                    @endif
                </div>

                <flux:separator class="my-4" />

                <div class="flex justify-between text-lg font-bold">
                    <flux:text>Total</flux:text>
                    <flux:text>{{ $priceService->formatPrice($order->total) }}</flux:text>
                </div>

                <flux:button 
                    href="{{ route('products.index') }}" 
                    wire:navigate 
                    variant="primary" 
                    class="mt-6 w-full"
                >
                    Continue Shopping
                </flux:button>
            </flux:card>
        </div>
    </div>
</div>
