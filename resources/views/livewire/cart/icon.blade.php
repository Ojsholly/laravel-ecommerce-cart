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

<div>
    <flux:tooltip :content="__('Cart')" position="bottom">
        <a href="{{ route('cart.index') }}" wire:navigate class="relative inline-flex items-center">
            <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="shopping-cart" :label="__('Cart')" />
            @if($itemCount > 0)
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                    {{ $itemCount > 9 ? '9+' : $itemCount }}
                </span>
            @endif
        </a>
    </flux:tooltip>
</div>
