<?php

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PriceCalculationService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public $cart;
    public $pricing;
    public $priceService;

    public function mount(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->getOrCreateCart(auth()->user());

        if ($this->cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $this->priceService = app(PriceCalculationService::class);
        $this->pricing = $this->priceService->calculateOrderPricing($this->cart->items);
    }

    public function placeOrder(): void
    {
        try {
            $checkoutService = app(CheckoutService::class);
            $order = $checkoutService->processCheckout($this->cart);

            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: 'Order placed successfully!', type: 'success');
            $this->redirect(route('orders.show', $order), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }
}; ?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">Checkout</flux:heading>
        <flux:subheading>Review your order and complete purchase</flux:subheading>
    </flux:header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Order Items</flux:heading>

                <div class="space-y-4">
                    @foreach($cart->items as $item)
                        <div class="flex gap-4 border-b border-zinc-200 pb-4 last:border-0 dark:border-zinc-700">
                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                @if($item->product->primary_image)
                                    <img 
                                        src="{{ $item->product->primary_image['url'] }}" 
                                        alt="{{ $item->product->primary_image['alt'] }}" 
                                        class="h-full w-full object-cover"
                                    />
                                @endif
                            </div>

                            <div class="flex flex-1 justify-between">
                                <div>
                                    <flux:heading size="sm">{{ $item->product->name }}</flux:heading>
                                    <flux:text class="mt-1 text-sm">Quantity: {{ $item->quantity }}</flux:text>
                                    <flux:text class="mt-1 text-sm">{{ $priceService->formatPrice($item->product->price) }} each</flux:text>
                                </div>
                                <flux:text class="font-bold">{{ $priceService->formatPrice($item->getSubtotal()) }}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <div class="lg:col-span-1">
            <flux:card class="sticky top-4">
                <flux:heading size="lg" class="mb-4">Order Summary</flux:heading>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text>Subtotal</flux:text>
                        <flux:text>{{ $priceService->formatPrice($pricing->subtotal) }}</flux:text>
                    </div>

                    @foreach($pricing->breakdown as $key => $item)
                        <div class="flex justify-between text-sm">
                            <flux:text>{{ $item['label'] }}</flux:text>
                            <flux:text>{{ $priceService->formatPrice($item['amount']) }}</flux:text>
                        </div>
                    @endforeach
                </div>

                <flux:separator class="my-4" />

                <div class="flex justify-between text-lg font-bold">
                    <flux:text>Total</flux:text>
                    <flux:text>{{ $priceService->formatPrice($pricing->total) }}</flux:text>
                </div>

                <flux:button 
                    wire:click="placeOrder" 
                    variant="primary" 
                    class="mt-6 w-full"
                    wire:confirm="Are you sure you want to place this order?"
                >
                    Place Order
                </flux:button>

                <flux:button 
                    href="{{ route('cart.index') }}" 
                    wire:navigate 
                    variant="ghost" 
                    class="mt-2 w-full"
                >
                    Back to Cart
                </flux:button>

                <div class="mt-4 rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                    <flux:text class="text-xs text-zinc-600 dark:text-zinc-400">
                        By placing this order, you agree to our terms and conditions.
                    </flux:text>
                </div>
            </flux:card>
        </div>
    </div>
</div>
