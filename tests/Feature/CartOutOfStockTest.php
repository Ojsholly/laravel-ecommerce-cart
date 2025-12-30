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

test('items that become out of stock are greyed out in cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Test Product', 'stock_quantity' => 5]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 1);

    // Product goes out of stock after being added
    $product->update(['stock_quantity' => 0]);

    Volt::test('cart.index')
        ->assertSee('Test Product')
        ->assertSee('Out of Stock')
        ->assertSee('Currently unavailable');
});

test('out of stock items are excluded from cart total', function () {
    $this->actingAs($this->user);

    $availableProduct = Product::factory()->create(['price' => '50.00', 'stock_quantity' => 10]);
    $outOfStockProduct = Product::factory()->create(['price' => '100.00', 'stock_quantity' => 5]);

    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $availableProduct, 1);
    $this->cartService->addProduct($cart, $outOfStockProduct, 1);

    // Product goes out of stock after being added
    $outOfStockProduct->update(['stock_quantity' => 0]);

    Volt::test('cart.index')
        ->assertSee('50.00') // Only available product price
        ->assertSee('1 item(s) out of stock and excluded from total');
});

test('checkout button is disabled when all items are out of stock', function () {
    $this->actingAs($this->user);

    $product1 = Product::factory()->create(['stock_quantity' => 5]);
    $product2 = Product::factory()->create(['stock_quantity' => 5]);

    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product1, 1);
    $this->cartService->addProduct($cart, $product2, 1);

    // Products go out of stock after being added
    $product1->update(['stock_quantity' => 0]);
    $product2->update(['stock_quantity' => 0]);

    Volt::test('cart.index')
        ->assertSee('All items in your cart are currently out of stock');
});

test('checkout button is enabled when some items are available', function () {
    $this->actingAs($this->user);

    $availableProduct = Product::factory()->create(['stock_quantity' => 10]);
    $outOfStockProduct = Product::factory()->create(['stock_quantity' => 5]);

    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $availableProduct, 1);
    $this->cartService->addProduct($cart, $outOfStockProduct, 1);

    // One product goes out of stock after being added
    $outOfStockProduct->update(['stock_quantity' => 0]);

    Volt::test('cart.index')
        ->assertSee('Proceed to Checkout')
        ->assertSee('1 item(s) out of stock and excluded from total');
});

test('cart with insufficient stock for requested quantity shows item as unavailable', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 5);

    // Stock drops below cart quantity
    $product->update(['stock_quantity' => 2]);

    Volt::test('cart.index')
        ->assertSee('Out of Stock')
        ->assertSee('Currently unavailable');
});
