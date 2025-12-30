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

<div class="relative">
    <a href="{{ route('wishlist.index') }}" wire:navigate class="relative inline-flex items-center justify-center p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
        <svg class="size-6 text-zinc-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        @if($itemCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">
                {{ $itemCount > 9 ? '9+' : $itemCount }}
            </span>
        @endif
    </a>
</div>
