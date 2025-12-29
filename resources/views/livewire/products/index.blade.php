<?php

use App\Models\Product;
use App\Services\CartService;
use App\Services\PriceCalculationService;
use App\Services\WishlistService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'products' => Product::inStock()->latest()->paginate(12),
            'priceService' => app(PriceCalculationService::class),
        ];
    }

    public function addToCart(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());

        try {
            $cartService->addProduct($cart, $product, 1);
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: "{$product->name} added to cart!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function toggleWishlist(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());

        $added = $wishlistService->toggleProduct($wishlist, $product);
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', 
            message: $added ? "{$product->name} added to wishlist!" : "{$product->name} removed from wishlist!",
            type: 'success'
        );
    }
}; ?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">Products</flux:heading>
        <flux:subheading>Browse our collection of amazing products</flux:subheading>
    </flux:header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($products as $product)
            <flux:card class="flex flex-col">
                <div class="aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    @if($product->primary_image)
                        <img 
                            src="{{ $product->primary_image['url'] }}" 
                            alt="{{ $product->primary_image['alt'] }}" 
                            class="h-full w-full object-cover"
                        />
                    @else
                        <div class="flex h-full items-center justify-center text-zinc-400">
                            <flux:icon.photo class="size-16" />
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-1 flex-col">
                    <flux:heading size="lg" class="line-clamp-2">{{ $product->name }}</flux:heading>
                    <flux:subheading class="mt-1 line-clamp-2">{{ $product->description }}</flux:subheading>

                    <div class="mt-4 flex items-center justify-between">
                        <flux:text class="text-lg font-bold">{{ $priceService->formatPrice($product->price) }}</flux:text>
                        
                        @if($product->isLowStock())
                            <flux:badge color="yellow" size="sm">Low Stock</flux:badge>
                        @elseif($product->isOutOfStock())
                            <flux:badge color="red" size="sm">Out of Stock</flux:badge>
                        @else
                            <flux:badge color="green" size="sm">In Stock</flux:badge>
                        @endif
                    </div>

                    <div class="mt-4 flex gap-2">
                        <flux:button 
                            wire:click="addToCart({{ $product->id }})" 
                            variant="primary" 
                            class="flex-1"
                            :disabled="$product->isOutOfStock()"
                        >
                            <flux:icon.shopping-cart class="size-5" />
                            Add to Cart
                        </flux:button>
                        
                        <flux:button 
                            wire:click="toggleWishlist({{ $product->id }})" 
                            variant="ghost"
                        >
                            <flux:icon.heart class="size-5" />
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
