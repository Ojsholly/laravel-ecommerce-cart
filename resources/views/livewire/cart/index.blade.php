<?php

use App\Services\CartService;
use App\Services\PriceCalculationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public $cart;
    public $priceService;

    public function mount(): void
    {
        $this->loadCart();
    }

    #[On('cart-updated')]
    public function loadCart(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->getOrCreateCart(auth()->user());
        $this->priceService = app(PriceCalculationService::class);
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $cartService = app(CartService::class);
        $item = $this->cart->items->firstWhere('id', $itemId);

        if (!$item) {
            return;
        }

        try {
            $cartService->updateQuantity($item, $quantity);
            $this->loadCart();
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: 'Cart updated!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function removeItem(int $itemId): void
    {
        $cartService = app(CartService::class);
        $item = $this->cart->items->firstWhere('id', $itemId);

        if (!$item) {
            return;
        }

        $cartService->removeItem($item);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Item removed from cart!', type: 'success');
    }

    public function clearCart(): void
    {
        $cartService = app(CartService::class);
        $cartService->clearCart($this->cart);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Cart cleared!', type: 'success');
    }
}; ?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">Shopping Cart</flux:heading>
        <flux:subheading>Review your items before checkout</flux:subheading>

        <x-slot:actions>
            @if($cart->items->isNotEmpty())
                <flux:button wire:click="clearCart" wire:confirm="Are you sure you want to clear your cart?" variant="ghost" color="red">
                    Clear Cart
                </flux:button>
            @endif
        </x-slot:actions>
    </flux:header>

    @if($cart->items->isEmpty())
        <flux:card class="text-center py-12">
            <div class="flex flex-col items-center gap-4">
                <flux:icon.shopping-cart class="size-16 text-zinc-400" />
                <flux:heading size="lg">Your cart is empty</flux:heading>
                <flux:subheading>Add some products to get started</flux:subheading>
                <flux:button href="{{ route('products.index') }}" wire:navigate variant="primary" class="mt-4">
                    Browse Products
                </flux:button>
            </div>
        </flux:card>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                @foreach($cart->items as $item)
                    <flux:card>
                        <div class="flex gap-4">
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                @if($item->product->primary_image)
                                    <img 
                                        src="{{ $item->product->primary_image['url'] }}" 
                                        alt="{{ $item->product->primary_image['alt'] }}" 
                                        class="h-full w-full object-cover"
                                    />
                                @else
                                    <div class="flex h-full items-center justify-center text-zinc-400">
                                        <flux:icon.photo class="size-8" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col">
                                <div class="flex justify-between">
                                    <div>
                                        <flux:heading size="lg">{{ $item->product->name }}</flux:heading>
                                        <flux:text class="mt-1 text-sm">{{ $priceService->formatPrice($item->product->price) }} each</flux:text>
                                    </div>
                                    <flux:button 
                                        wire:click="removeItem({{ $item->id }})" 
                                        variant="ghost" 
                                        size="sm"
                                        icon="x-mark"
                                    />
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <flux:button 
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                            variant="outline" 
                                            size="sm"
                                            icon="minus"
                                            :disabled="$item->quantity <= 1"
                                        />
                                        <flux:input 
                                            type="number" 
                                            value="{{ $item->quantity }}" 
                                            wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                            class="w-20 text-center"
                                            min="1"
                                        />
                                        <flux:button 
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                            variant="outline" 
                                            size="sm"
                                            icon="plus"
                                            :disabled="!$item->product->hasStock($item->quantity + 1)"
                                        />
                                    </div>

                                    <flux:text class="text-lg font-bold">
                                        {{ $priceService->formatPrice($item->getSubtotal()) }}
                                    </flux:text>
                                </div>

                                @if($item->product->isLowStock())
                                    <flux:badge color="yellow" size="sm" class="mt-2 w-fit">Only {{ $item->product->stock_quantity }} left</flux:badge>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <flux:card class="sticky top-4">
                    <flux:heading size="lg" class="mb-4">Order Summary</flux:heading>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <flux:text>Subtotal ({{ $cart->item_count }} items)</flux:text>
                            <flux:text>{{ $priceService->formatPrice($cart->getTotal()) }}</flux:text>
                        </div>
                    </div>

                    <flux:separator class="my-4" />

                    <div class="flex justify-between font-bold">
                        <flux:text>Total</flux:text>
                        <flux:text>{{ $priceService->formatPrice($cart->getTotal()) }}</flux:text>
                    </div>

                    <flux:button 
                        href="{{ route('checkout.index') }}" 
                        wire:navigate 
                        variant="primary" 
                        class="mt-6 w-full"
                    >
                        Proceed to Checkout
                    </flux:button>

                    <flux:button 
                        href="{{ route('products.index') }}" 
                        wire:navigate 
                        variant="ghost" 
                        class="mt-2 w-full"
                    >
                        Continue Shopping
                    </flux:button>
                </flux:card>
            </div>
        </div>
    @endif
</div>
