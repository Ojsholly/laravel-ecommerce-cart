<?php

use App\Services\CartService;
use App\Services\PriceCalculationService;
use App\Services\WishlistService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public $cart;
    public $wishlist;
    public ?int $itemToRemove = null;
    public bool $showRemoveModal = false;

    public function mount(): void
    {
        $this->loadCart();
        $this->loadWishlist();
    }

    #[On('cart-updated')]
    public function loadCart(): void
    {
        $cartService = app(CartService::class);
        $this->cart = $cartService->getOrCreateCart(auth()->user());
    }

    #[On('wishlist-updated')]
    public function loadWishlist(): void
    {
        $wishlistService = app(WishlistService::class);
        $this->wishlist = $wishlistService->getOrCreateWishlist(auth()->user());
        $this->wishlist->load('items.product');
    }

    private function priceService(): PriceCalculationService
    {
        return app(PriceCalculationService::class);
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

    public function confirmRemoveItem(int $itemId): void
    {
        $this->itemToRemove = $itemId;
        $this->showRemoveModal = true;
    }

    public function removeItem(): void
    {
        if (!$this->itemToRemove) {
            return;
        }

        $cartService = app(CartService::class);
        $item = $this->cart->items->firstWhere('id', $this->itemToRemove);

        if (!$item) {
            return;
        }

        $cartService->removeItem($item);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Item removed from cart!', type: 'success');
        
        $this->itemToRemove = null;
        $this->showRemoveModal = false;
    }

    public function moveToWishlist(): void
    {
        if (!$this->itemToRemove) {
            return;
        }

        $cartService = app(CartService::class);
        $item = $this->cart->items->firstWhere('id', $this->itemToRemove);

        if (!$item) {
            return;
        }

        try {
            $cartService->moveToWishlist($item, auth()->user());
            $this->loadCart();
            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('notify', message: 'Item moved to wishlist! You can purchase it later.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
        
        $this->itemToRemove = null;
        $this->showRemoveModal = false;
    }

    public function cancelRemove(): void
    {
        $this->itemToRemove = null;
        $this->showRemoveModal = false;
    }

    public function clearCart(): void
    {
        $cartService = app(CartService::class);
        $cartService->clearCart($this->cart);
        $this->loadCart();
        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: 'Cart cleared!', type: 'success');
    }

    public function moveWishlistItemToCart(int $wishlistItemId): void
    {
        $wishlistService = app(WishlistService::class);
        $item = $this->wishlist->items->firstWhere('id', $wishlistItemId);

        if (!$item) {
            return;
        }

        try {
            $wishlistService->moveToCart($item, auth()->user());
            $this->loadCart();
            $this->loadWishlist();
            $this->dispatch('cart-updated');
            $this->dispatch('wishlist-updated');
            $this->dispatch('notify', message: 'Item moved to cart!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }
}; ?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Shopping Cart</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Review your items before checkout</p>
            </div>
            
            @if($cart->items->isNotEmpty())
                <flux:button wire:click="clearCart" wire:confirm="Are you sure you want to clear your cart?" variant="ghost" color="red">
                    Clear Cart
                </flux:button>
            @endif
        </div>
    </div>

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
                                        <flux:text class="mt-1 text-sm">{{ $this->priceService()->formatPrice($item->product->price) }} each</flux:text>
                                    </div>
                                    <flux:button 
                                        wire:click="confirmRemoveItem({{ $item->id }})" 
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
                                        {{ $this->priceService()->formatPrice($item->getSubtotal()) }}
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
                            <flux:text>{{ $this->priceService()->formatPrice($cart->getTotal()) }}</flux:text>
                        </div>
                    </div>

                    <flux:separator class="my-4" />

                    <div class="flex justify-between font-bold">
                        <flux:text>Total</flux:text>
                        <flux:text>{{ $this->priceService()->formatPrice($cart->getTotal()) }}</flux:text>
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

                {{-- Wishlist Section --}}
                @if($wishlist->items->isNotEmpty())
                    <flux:card class="mt-6 sticky top-4">
                        <div class="flex items-center justify-between mb-4">
                            <flux:heading size="lg">Wishlist ({{ $wishlist->item_count }})</flux:heading>
                            <a href="{{ route('wishlist.index') }}" wire:navigate class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                See All
                            </a>
                        </div>

                        <div class="space-y-3">
                            @foreach($wishlist->items->take(4) as $item)
                                <div class="flex gap-3 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                        @if($item->product->primary_image)
                                            <img 
                                                src="{{ $item->product->primary_image['url'] }}" 
                                                alt="{{ $item->product->primary_image['alt'] }}" 
                                                class="h-full w-full object-cover"
                                            />
                                        @else
                                            <div class="flex h-full items-center justify-center text-zinc-400">
                                                <flux:icon.photo class="size-6" />
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-1 flex-col justify-between min-w-0">
                                        <div>
                                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 line-clamp-1">
                                                {{ $item->product->name }}
                                            </h4>
                                            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                                                {{ $this->priceService()->formatPrice($item->product->price) }}
                                            </p>
                                        </div>
                                        
                                        @if($item->product->isOutOfStock())
                                            <span class="text-xs text-red-600 dark:text-red-400 font-medium">Out of Stock</span>
                                        @else
                                            <button 
                                                wire:click="moveWishlistItemToCart({{ $item->id }})"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline text-left font-medium"
                                            >
                                                Add to cart
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>
    @endif

    {{-- Remove Item Modal --}}
    <flux:modal :open="$showRemoveModal" wire:model="showRemoveModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove item from cart?</flux:heading>
                <flux:subheading class="mt-2">
                    Not ready to buy? Save it for later in your wishlist and grab it when you're ready! 💝
                </flux:subheading>
            </div>

            <div class="flex flex-col gap-3">
                <flux:button 
                    wire:click="moveToWishlist" 
                    variant="primary"
                    class="w-full"
                >
                    <svg class="size-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Move to Wishlist
                </flux:button>

                <flux:button 
                    wire:click="removeItem" 
                    variant="ghost"
                    color="red"
                    class="w-full"
                >
                    Remove Completely
                </flux:button>

                <flux:button 
                    wire:click="cancelRemove" 
                    variant="ghost"
                    class="w-full"
                >
                    Keep in Cart
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
