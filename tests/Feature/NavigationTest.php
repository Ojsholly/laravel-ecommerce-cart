<?php

use App\Models\User;

uses()->group('navigation');

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can access products page', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('products.index'));

    $response->assertOk();
});

test('authenticated user can access cart page', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('cart.index'));

    $response->assertOk();
});

test('authenticated user can access wishlist page', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('wishlist.index'));

    $response->assertOk();
});

test('authenticated user can access orders page', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
});

test('authenticated user can access checkout page with items in cart', function () {
    $this->actingAs($this->user);

    $product = \App\Models\Product::factory()->create(['stock_quantity' => 10]);
    $cartService = app(\App\Services\CartService::class);
    $cart = $cartService->getOrCreateCart($this->user);
    $cartService->addProduct($cart, $product, 1);

    $response = $this->get(route('checkout.index'));

    $response->assertOk();
});

test('checkout page redirects to cart when empty', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('checkout.index'));

    $response->assertRedirect(route('cart.index'));
});

test('guest cannot access products page', function () {
    $response = $this->get(route('products.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot access cart page', function () {
    $response = $this->get(route('cart.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot access wishlist page', function () {
    $response = $this->get(route('wishlist.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot access orders page', function () {
    $response = $this->get(route('orders.index'));

    $response->assertRedirect(route('login'));
});

test('sidebar contains shop navigation links', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('dashboard'));

    $response->assertSee('Products')
        ->assertSee('Cart')
        ->assertSee('Wishlist')
        ->assertSee('Orders');
});

test('cart icon displays item count', function () {
    $this->actingAs($this->user);

    $product = \App\Models\Product::factory()->create(['stock_quantity' => 10]);
    $cartService = app(\App\Services\CartService::class);
    $cart = $cartService->getOrCreateCart($this->user);
    $cartService->addProduct($cart, $product, 2);

    $response = $this->get(route('products.index'));

    $response->assertSee('2');
});

test('wishlist icon displays item count', function () {
    $this->actingAs($this->user);

    $product = \App\Models\Product::factory()->create();
    $wishlistService = app(\App\Services\WishlistService::class);
    $wishlist = $wishlistService->getOrCreateWishlist($this->user);
    $wishlistService->addProduct($wishlist, $product);

    $response = $this->get(route('products.index'));

    $response->assertSee('1');
});
