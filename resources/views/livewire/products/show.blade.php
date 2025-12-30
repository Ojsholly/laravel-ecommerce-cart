<?php

use App\Models\Product;
use App\Services\CartService;
use App\Services\PriceCalculationService;
use App\Services\WishlistService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public Product $product;
    public int $quantity = 1;
    public bool $isInWishlist = false;

    public function mount(): void
    {
        $this->loadWishlistStatus();
    }

    public function loadWishlistStatus(): void
    {
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());
        $this->isInWishlist = $wishlist->items->contains('product_id', $this->product->id);
    }

    private function priceService(): PriceCalculationService
    {
        return app(PriceCalculationService::class);
    }

    public function addToCart(): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());

        try {
            $cartService->addProduct($cart, $this->product, $this->quantity);
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: "{$this->product->name} added to cart!", type: 'success');
            $this->quantity = 1;
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function toggleWishlist(): void
    {
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());

        $added = $wishlistService->toggleProduct($wishlist, $this->product);
        $this->loadWishlistStatus();
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', 
            message: $added ? "{$this->product->name} added to wishlist!" : "{$this->product->name} removed from wishlist!",
            type: 'success'
        );
    }

    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->product->stock_quantity) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
}; ?>

<div>
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('products.index') }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
                Products
            </a>
            <span class="text-zinc-400">/</span>
            <span class="text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                @if($product->primary_image)
                    <img 
                        src="{{ $product->primary_image['url'] }}" 
                        alt="{{ $product->primary_image['alt'] }}" 
                        class="h-full w-full object-cover"
                    />
                @else
                    <div class="flex h-full items-center justify-center text-zinc-400">
                        <flux:icon.photo class="size-24" />
                    </div>
                @endif
            </div>

            @if(count($product->sorted_images) > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->sorted_images as $image)
                        <div class="aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800 cursor-pointer hover:opacity-75 transition-opacity">
                            <img 
                                src="{{ $image['url'] }}" 
                                alt="{{ $image['alt'] }}" 
                                class="h-full w-full object-cover"
                            />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h1>
                    
                    <div class="mt-4 flex items-center gap-3">
                        @if($product->isLowStock())
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                Only {{ $product->stock_quantity }} left
                            </span>
                        @elseif($product->isOutOfStock())
                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                Out of Stock
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                In Stock
                            </span>
                        @endif
                    </div>
                </div>

                <button 
                    wire:click="toggleWishlist" 
                    class="p-2 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    @if($isInWishlist)
                        <svg class="size-8 text-red-500 fill-current" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    @else
                        <svg class="size-8 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    @endif
                </button>
            </div>

            <div class="mt-6">
                <div class="text-4xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $this->priceService()->formatPrice($product->price) }}
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Description</h2>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $product->description }}</p>
            </div>

            <div class="mt-8 space-y-4">
                @if(!$product->isOutOfStock())
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Quantity</label>
                        <div class="flex items-center gap-3">
                            <button 
                                wire:click="decrementQuantity"
                                class="flex items-center justify-center w-10 h-10 rounded-lg border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                :disabled="$quantity <= 1"
                            >
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            
                            <input 
                                type="number" 
                                wire:model="quantity"
                                min="1"
                                max="{{ $product->stock_quantity }}"
                                class="w-20 text-center rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-lg font-semibold"
                            />
                            
                            <button 
                                wire:click="incrementQuantity"
                                class="flex items-center justify-center w-10 h-10 rounded-lg border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                :disabled="$quantity >= $product->stock_quantity"
                            >
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <flux:button 
                        wire:click="addToCart" 
                        variant="primary" 
                        class="w-full py-4 text-lg"
                    >
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Add to Cart
                    </flux:button>
                @else
                    <flux:button variant="ghost" class="w-full py-4 text-lg" disabled>
                        Out of Stock
                    </flux:button>
                @endif
            </div>

            <div class="mt-8 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Product Details</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-zinc-600 dark:text-zinc-400">Availability</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">
                            @if($product->isOutOfStock())
                                Out of Stock
                            @else
                                {{ $product->stock_quantity }} units
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-600 dark:text-zinc-400">SKU</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->uuid }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
