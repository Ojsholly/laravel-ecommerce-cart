<?php

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Livewire\Volt\Volt;

uses()->group('cart');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cartService = app(CartService::class);
});

test('cart page can be rendered', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cart.index'));

    $response->assertOk();
});

test('cart page displays cart items', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Test Product']);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 2);

    Volt::test('cart.index')
        ->assertSee('Test Product')
        ->assertSee('2');
});

test('can update cart item quantity', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 2);

    Volt::test('cart.index')
        ->call('updateQuantity', $cartItem->id, 5)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 5,
    ]);
});

test('can show remove item confirmation modal', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 1);

    Volt::test('cart.index')
        ->call('confirmRemoveItem', $cartItem->id)
        ->assertSet('showRemoveModal', true)
        ->assertSet('itemToRemove', $cartItem->id);
});

test('can cancel remove item', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 1);

    Volt::test('cart.index')
        ->call('confirmRemoveItem', $cartItem->id)
        ->call('cancelRemove')
        ->assertSet('showRemoveModal', false)
        ->assertSet('itemToRemove', null);

    $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
});

test('can remove item from cart completely', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 1);

    Volt::test('cart.index')
        ->call('confirmRemoveItem', $cartItem->id)
        ->call('removeItem')
        ->assertDispatched('cart-updated');

    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
});

test('can move cart item to wishlist', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 1);

    Volt::test('cart.index')
        ->call('confirmRemoveItem', $cartItem->id)
        ->call('moveToWishlist')
        ->assertDispatched('cart-updated')
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    $this->assertDatabaseHas('wishlist_items', ['product_id' => $product->id]);
});

test('can clear entire cart', function () {
    $this->actingAs($this->user);

    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product1, 1);
    $this->cartService->addProduct($cart, $product2, 1);

    Volt::test('cart.index')
        ->call('clearCart')
        ->assertDispatched('cart-updated');

    expect($cart->fresh()->items()->count())->toBe(0);
});

test('cart displays wishlist items in sidebar', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Wishlist Product']);
    $wishlistService = app(\App\Services\WishlistService::class);
    $wishlist = $wishlistService->getOrCreateWishlist($this->user);
    $wishlistService->addProduct($wishlist, $product);

    Volt::test('cart.index')
        ->assertSee('Wishlist');
});

test('can move wishlist item to cart from cart page', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $wishlistService = app(\App\Services\WishlistService::class);
    $wishlist = $wishlistService->getOrCreateWishlist($this->user);
    $wishlistItem = $wishlistService->addProduct($wishlist, $product);

    Volt::test('cart.index')
        ->call('moveWishlistItemToCart', $wishlistItem->id)
        ->assertDispatched('cart-updated')
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseHas('cart_items', ['product_id' => $product->id]);
    $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
});

test('empty cart shows empty state', function () {
    $this->actingAs($this->user);

    Volt::test('cart.index')
        ->assertSee('Your cart is empty')
        ->assertSee('Browse Products');
});

test('cart calculates totals correctly', function () {
    $this->actingAs($this->user);

    $product1 = Product::factory()->create(['price' => '10.00', 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => '20.00', 'stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product1, 2);
    $this->cartService->addProduct($cart, $product2, 1);

    Volt::test('cart.index')
        ->assertSee('40.00');
});
