<?php

use App\Models\Product;
use App\Services\CartService;
use App\Services\PriceCalculationService;
use App\Services\WishlistService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public array $quantities = [];
    public array $wishlistProductIds = [];
    public array $cartQuantities = [];
    public string $search = '';

    public function mount(): void
    {
        $this->loadWishlistItems();
        $this->loadCartQuantities();
    }

    public function loadWishlistItems(): void
    {
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());
        $this->wishlistProductIds = $wishlist->items->pluck('product_id')->toArray();
    }

    #[On('cart-updated')]
    public function loadCartQuantities(): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());
        $this->cartQuantities = $cart->items->pluck('quantity', 'product_id')->toArray();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Product::query();
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                  ->orWhere('description', 'ilike', "%{$this->search}%");
            });
        }

        return [
            'products' => $query->latest()->paginate(12),
            'priceService' => app(PriceCalculationService::class),
            'cartCount' => app(CartService::class)->getOrCreateCart(auth()->user())->item_count,
        ];
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());

        try {
            // Add 1 item on first click
            $cartService->addProduct($cart, $product, 1);
            $this->loadCartQuantities();
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: "{$product->name} added to cart!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function incrementCartQuantity(int $productId): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());
        $item = $cart->items->firstWhere('product_id', $productId);

        if ($item) {
            try {
                $cartService->updateQuantity($item, $item->quantity + 1);
                $this->loadCartQuantities();
                $this->dispatch('cart-updated');
            } catch (\Exception $e) {
                $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            }
        }
    }

    public function decrementCartQuantity(int $productId): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());
        $item = $cart->items->firstWhere('product_id', $productId);

        if ($item) {
            try {
                if ($item->quantity > 1) {
                    $cartService->updateQuantity($item, $item->quantity - 1);
                } else {
                    $cartService->removeItem($item);
                }
                $this->loadCartQuantities();
                $this->dispatch('cart-updated');
            } catch (\Exception $e) {
                $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            }
        }
    }

    public function toggleWishlist(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());

        $added = $wishlistService->toggleProduct($wishlist, $product);
        $this->loadWishlistItems();
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', 
            message: $added ? "{$product->name} added to wishlist!" : "{$product->name} removed from wishlist!",
            type: 'success'
        );
    }

    public function isInWishlist(int $productId): bool
    {
        return in_array($productId, $this->wishlistProductIds);
    }
}; ?>

<div>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Products</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Browse our collection of amazing products</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input 
                        type="search" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search products..."
                        class="w-64 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 pl-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <svg class="absolute left-3 top-2.5 size-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                
                <a href="{{ route('cart.index') }}" wire:navigate class="relative inline-flex items-center justify-center rounded-lg bg-zinc-800 dark:bg-zinc-700 px-4 py-2 text-white hover:bg-zinc-900 dark:hover:bg-zinc-600 transition-colors">
                    <svg class="size-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium">Cart</span>
                    @if($cartCount > 0)
                        <span class="ml-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach($products as $product)
            <div class="group relative flex flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 hover:shadow-lg transition-shadow">
                <div class="relative aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                    @if($product->primary_image)
                        <img 
                            src="{{ $product->primary_image['url'] }}" 
                            alt="{{ $product->primary_image['alt'] }}" 
                            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    @else
                        <div class="flex h-full items-center justify-center text-zinc-400">
                            <flux:icon.photo class="size-12" />
                        </div>
                    @endif
                    
                    <button 
                        wire:click="toggleWishlist({{ $product->id }})" 
                        class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm hover:bg-white dark:hover:bg-zinc-800 transition-colors shadow-sm"
                    >
                        @if($this->isInWishlist($product->id))
                            <svg class="size-4 text-red-500 fill-current" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        @else
                            <svg class="size-4 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        @endif
                    </button>

                    @if($product->isLowStock())
                        <div class="absolute top-2 left-2">
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                {{ $product->stock_quantity }} left
                            </span>
                        </div>
                    @elseif($product->isOutOfStock())
                        <div class="absolute top-2 left-2">
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                Out of Stock
                            </span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-1 flex-col p-3">
                    <a href="{{ route('products.show', $product) }}" wire:navigate class="text-sm font-medium text-zinc-900 dark:text-zinc-100 line-clamp-2 min-h-[2.5rem] hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        {{ $product->name }}
                    </a>
                    
                    <div class="mt-2 space-y-2">
                        <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $priceService->formatPrice($product->price) }}
                        </div>
                        
                        @if(!$product->isOutOfStock())
                            @if(isset($cartQuantities[$product->id]) && $cartQuantities[$product->id] > 0)
                                {{-- Product is in cart - show quantity controls --}}
                                <div class="flex items-center gap-2">
                                    <button 
                                        wire:click="decrementCartQuantity({{ $product->id }})"
                                        class="flex items-center justify-center w-8 h-8 rounded bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors"
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    
                                    <span class="text-base font-semibold text-zinc-900 dark:text-zinc-100 min-w-[2rem] text-center">
                                        {{ $cartQuantities[$product->id] }}
                                    </span>
                                    
                                    <button 
                                        wire:click="incrementCartQuantity({{ $product->id }})"
                                        class="flex items-center justify-center w-8 h-8 rounded bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors"
                                        @if($cartQuantities[$product->id] >= $product->stock_quantity) disabled @endif
                                    >
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                    
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400">
                                        ({{ $cartQuantities[$product->id] }} {{ $cartQuantities[$product->id] == 1 ? 'item' : 'items' }} added)
                                    </span>
                                </div>
                            @else
                                {{-- Product not in cart - show add button --}}
                                <button 
                                    wire:click="addToCart({{ $product->id }})" 
                                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-zinc-800 dark:bg-zinc-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-900 dark:hover:bg-zinc-600 transition-colors"
                                >
                                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Add to cart
                                </button>
                            @endif
                        @else
                            <button 
                                disabled
                                class="w-full flex items-center justify-center gap-2 rounded-lg bg-zinc-200 dark:bg-zinc-700 px-4 py-2.5 text-sm font-medium text-zinc-400 cursor-not-allowed"
                            >
                                Out of Stock
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
