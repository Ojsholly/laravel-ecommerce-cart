<?php

use App\Services\PriceCalculationService;
use App\Services\WishlistService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public $wishlist;

    public function mount(): void
    {
        $this->loadWishlist();
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

    public function removeItem(int $itemId): void
    {
        $wishlistService = app(WishlistService::class);
        $item = $this->wishlist->items->firstWhere('id', $itemId);

        if (!$item) {
            return;
        }

        $wishlistService->removeProduct($item);
        $this->loadWishlist();
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', message: 'Item removed from wishlist!', type: 'success');
    }

    public function moveToCart(int $itemId): void
    {
        $wishlistService = app(WishlistService::class);
        $item = $this->wishlist->items->firstWhere('id', $itemId);

        if (!$item) {
            return;
        }

        try {
            $wishlistService->moveToCart($item, auth()->user());
            $this->loadWishlist();
            $this->dispatch('wishlist-updated');
            $this->dispatch('cart-updated');
            $this->dispatch('notify', message: "{$item->product->name} moved to cart!", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }
}; ?>

<div>
    <flux:header class="mb-6">
        <flux:heading size="xl">Wishlist</flux:heading>
        <flux:subheading>Your saved items</flux:subheading>
    </flux:header>

    @if($wishlist->items->isEmpty())
        <flux:card class="text-center py-12">
            <div class="flex flex-col items-center gap-4">
                <flux:icon.heart class="size-16 text-zinc-400" />
                <flux:heading size="lg">Your wishlist is empty</flux:heading>
                <flux:subheading>Save items you love for later</flux:subheading>
                <flux:button href="{{ route('products.index') }}" wire:navigate variant="primary" class="mt-4">
                    Browse Products
                </flux:button>
            </div>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($wishlist->items as $item)
                <flux:card class="flex flex-col">
                    <div class="aspect-square overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        @if($item->product->primary_image)
                            <img 
                                src="{{ $item->product->primary_image['url'] }}" 
                                alt="{{ $item->product->primary_image['alt'] }}" 
                                class="h-full w-full object-cover"
                            />
                        @else
                            <div class="flex h-full items-center justify-center text-zinc-400">
                                <flux:icon.photo class="size-16" />
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-1 flex-col">
                        <flux:heading size="lg" class="line-clamp-2">{{ $item->product->name }}</flux:heading>
                        <flux:subheading class="mt-1 line-clamp-2">{{ $item->product->description }}</flux:subheading>

                        <div class="mt-4 flex items-center justify-between">
                            <flux:text class="text-lg font-bold">{{ $this->priceService()->formatPrice($item->product->price) }}</flux:text>
                            
                            @if($item->product->isLowStock())
                                <flux:badge color="yellow" size="sm">Low Stock</flux:badge>
                            @elseif($item->product->isOutOfStock())
                                <flux:badge color="red" size="sm">Out of Stock</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">In Stock</flux:badge>
                            @endif
                        </div>

                        <div class="mt-4 flex gap-2">
                            <flux:button 
                                wire:click="moveToCart({{ $item->id }})" 
                                variant="primary" 
                                class="flex-1"
                                :disabled="$item->product->isOutOfStock()"
                            >
                                <flux:icon.shopping-cart class="size-5" />
                                Move to Cart
                            </flux:button>
                            
                            <flux:button 
                                wire:click="removeItem({{ $item->id }})" 
                                variant="ghost"
                                color="red"
                            >
                                <flux:icon.trash class="size-5" />
                            </flux:button>
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
