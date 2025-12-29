<?php

use App\Enums\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Jobs\SendLowStockNotification;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Queue;

uses()->group('checkout');

beforeEach(function () {
    $this->checkoutService = app(CheckoutService::class);
    $this->cartService = app(CartService::class);
    $this->user = User::factory()->create();
});

test('cannot checkout empty cart', function () {
    $cart = $this->cartService->getOrCreateCart($this->user);

    $this->checkoutService->processCheckout($cart);
})->throws(EmptyCartException::class);

test('can process checkout successfully', function () {
    $product = Product::factory()->create(['price' => '100.00', 'stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 2);

    $order = $this->checkoutService->processCheckout($cart);

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($this->user->id)
        ->and($order->status)->toBe(OrderStatus::PENDING)
        ->and($order->order_number)->not->toBeNull()
        ->and($order->items()->count())->toBe(1);
});

test('checkout decrements stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 3);

    $this->checkoutService->processCheckout($cart);

    expect($product->fresh()->stock_quantity)->toBe(7);
});

test('checkout creates product snapshot', function () {
    $product = Product::factory()->create(['name' => 'Test Product', 'price' => '50.00']);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 1);

    $order = $this->checkoutService->processCheckout($cart);
    $orderItem = $order->items->first();

    expect($orderItem->product_snapshot)->not->toBeNull()
        ->and($orderItem->product_snapshot['name'])->toBe('Test Product')
        ->and($orderItem->price_snapshot)->toBe('50.00');
});

test('checkout clears cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 2);

    $this->checkoutService->processCheckout($cart);

    expect($cart->fresh()->items()->count())->toBe(0);
});

test('checkout calculates vat correctly', function () {
    config(['cart.vat_rate' => 7.5]);
    $product = Product::factory()->create(['price' => '100.00', 'stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 1);

    $order = $this->checkoutService->processCheckout($cart);

    expect($order->subtotal)->toBe('100.00')
        ->and($order->vat_amount)->toBe('7.50')
        ->and($order->total)->toBe('107.50');
});

test('checkout stores pricing breakdown', function () {
    $product = Product::factory()->create(['price' => '100.00', 'stock_quantity' => 10]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 1);

    $order = $this->checkoutService->processCheckout($cart);

    expect($order->pricing_breakdown)->not->toBeNull()
        ->and($order->pricing_breakdown)->toHaveKey('vat');
});

test('low stock notification dispatched', function () {
    Queue::fake();
    config(['cart.low_stock_threshold' => 5]);
    $product = Product::factory()->create(['stock_quantity' => 6]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 2);

    $this->checkoutService->processCheckout($cart);

    Queue::assertPushed(SendLowStockNotification::class);
});

test('checkout with out of stock product throws exception', function () {
    $product = Product::factory()->create(['stock_quantity' => 1]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    $this->cartService->addProduct($cart, $product, 1);
    
    $product->update(['stock_quantity' => 0]);

    $this->checkoutService->processCheckout($cart);
})->throws(InsufficientStockException::class);

test('partial checkout with mixed stock', function () {
    $availableProduct = Product::factory()->create(['stock_quantity' => 10]);
    $unavailableProduct = Product::factory()->create(['stock_quantity' => 1]);
    $cart = $this->cartService->getOrCreateCart($this->user);
    
    $this->cartService->addProduct($cart, $availableProduct, 2);
    $this->cartService->addProduct($cart, $unavailableProduct, 1);
    
    $unavailableProduct->update(['stock_quantity' => 0]);

    $order = $this->checkoutService->processCheckout($cart);

    expect($order->items()->count())->toBe(1)
        ->and($order->items->first()->product_id)->toBe($availableProduct->id);
});
