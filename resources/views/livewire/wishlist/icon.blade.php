<?php

use App\Services\WishlistService;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public int $itemCount = 0;

    public function mount(): void
    {
        $this->loadWishlistCount();
    }

    #[On('wishlist-updated')]
    public function loadWishlistCount(): void
    {
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist(auth()->user());
        $this->itemCount = $wishlist->item_count;
    }
}; ?>

<div>
    <flux:tooltip :content="__('Wishlist')" position="bottom">
        <a href="{{ route('wishlist.index') }}" wire:navigate class="relative inline-flex items-center">
            <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="heart" :label="__('Wishlist')" />
            @if($itemCount > 0)
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                    {{ $itemCount > 9 ? '9+' : $itemCount }}
                </span>
            @endif
        </a>
    </flux:tooltip>
</div>
