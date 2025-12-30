<?php

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public int $itemCount = 0;

    public function mount(): void
    {
        $this->loadCartCount();
    }

    #[On('cart-updated')]
    public function loadCartCount(): void
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart(auth()->user());
        $this->itemCount = $cart->item_count;
    }
}; ?>

<div class="relative">
    <a href="{{ route('cart.index') }}" wire:navigate class="relative inline-flex items-center justify-center p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
        <svg class="size-6 text-zinc-700 dark:text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        @if($itemCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">
                {{ $itemCount > 9 ? '9+' : $itemCount }}
            </span>
        @endif
    </a>
</div>
