<?php

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;

uses()->group('cart');

beforeEach(function () {
    $this->cartService = app(CartService::class);
    $this->user = User::factory()->create();
});

test('can create cart for user', function () {
    $cart = $this->cartService->getOrCreateCart($this->user);

    expect($cart)->not->toBeNull()
        ->and($cart->user_id)->toBe($this->user->id);
    
    $this->assertDatabaseHas('carts', ['user_id' => $this->user->id]);
});

test('returns existing cart for user', function () {
    $cart1 = $this->cartService->getOrCreateCart($this->user);
    $cart2 = $this->cartService->getOrCreateCart($this->user);

    expect($cart1->id)->toBe($cart2->id);
});

test('can add product to cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);

    $cartItem = $this->cartService->addProduct($cart, $product, 2);

    expect($cartItem->quantity)->toBe(2)
        ->and($cartItem->product_id)->toBe($product->id);
    
    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

test('adding same product increases quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);

    $this->cartService->addProduct($cart, $product, 2);
    $cartItem = $this->cartService->addProduct($cart, $product, 3);

    expect($cartItem->quantity)->toBe(5);
});

test('cannot add product exceeding stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);
    $cart = $this->cartService->getOrCreateCart($this->user);

    $this->cartService->addProduct($cart, $product, 10);
})->throws(InsufficientStockException::class);

test('can update cart item quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 2);

    $updatedItem = $this->cartService->updateQuantity($cartItem, 5);

    expect($updatedItem->quantity)->toBe(5);
});

test('updating to zero removes item', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 2);

    $this->cartService->updateQuantity($cartItem, 0);

    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
});

test('can remove item from cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $cartItem = $this->cartService->addProduct($cart, $product, 2);

    $this->cartService->removeItem($cartItem);

    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
});

test('can clear cart', function () {
    $product1 = Product::factory()->create(['stock_quantity' => 10]);
    $product2 = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    
    $this->cartService->addProduct($cart, $product1, 2);
    $this->cartService->addProduct($cart, $product2, 3);

    $this->cartService->clearCart($cart);

    expect($cart->fresh()->items()->count())->toBe(0);
});

test('cart calculates total correctly', function () {
    $product1 = Product::factory()->create(['price' => '10.00', 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => '20.00', 'stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    
    $this->cartService->addProduct($cart, $product1, 2);
    $this->cartService->addProduct($cart, $product2, 1);

    $cart = $cart->fresh();
    $cart->load('items.product');

    expect($cart->getTotal())->toBe('40.00');
});
