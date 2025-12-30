<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Livewire\Volt\Volt;

uses()->group('orders');

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('orders index page can be rendered', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
});

test('orders index displays user orders', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-TEST-123',
    ]);

    Volt::test('orders.index')
        ->assertSee('ORD-TEST-123');
});

test('orders index only shows current user orders', function () {
    $this->actingAs($this->user);

    $userOrder = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-USER-123',
    ]);

    $otherUser = User::factory()->create();
    $otherOrder = Order::factory()->create([
        'user_id' => $otherUser->id,
        'order_number' => 'ORD-OTHER-456',
    ]);

    Volt::test('orders.index')
        ->assertSee('ORD-USER-123')
        ->assertDontSee('ORD-OTHER-456');
});

test('empty orders page shows empty state', function () {
    $this->actingAs($this->user);

    Volt::test('orders.index')
        ->assertSee('No orders yet')
        ->assertSee('Browse Products');
});

test('orders index displays order status', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);

    Volt::test('orders.index')
        ->assertSee('Pending');
});

test('orders index displays order total', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'total' => '99.99',
    ]);

    Volt::test('orders.index')
        ->assertSee('99.99');
});

test('order detail page can be rendered', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('orders.show', $order));

    $response->assertOk();
});

test('order detail page displays order information', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-DETAIL-789',
        'total' => '149.99',
    ]);

    Volt::test('orders.show', ['order' => $order])
        ->assertSee('ORD-DETAIL-789')
        ->assertSee('149.99');
});

test('cannot view other user order', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->get(route('orders.show', $order));

    $response->assertForbidden();
});

test('order detail displays order items', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Test Product', 'price' => '29.99']);
    $order = Order::factory()->hasItems(1, [
        'price_snapshot' => '29.99',
        'product_snapshot' => [
            'name' => 'Test Product',
            'price' => '29.99',
        ],
        'quantity' => 2,
    ])->create(['user_id' => $this->user->id]);

    Volt::test('orders.show', ['order' => $order])
        ->assertSee('Test Product')
        ->assertSee('2');
});

test('can create order from cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['price' => '50.00', 'stock_quantity' => 10]);
    $cartService = app(CartService::class);
    $cart = $cartService->getOrCreateCart($this->user);
    $cartService->addProduct($cart, $product, 2);

    $checkoutService = app(CheckoutService::class);
    $order = $checkoutService->processCheckout($cart);

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($this->user->id)
        ->and($order->items->count())->toBe(1);

    $this->assertDatabaseHas('orders', ['id' => $order->id]);
});

test('order creation clears cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $cartService = app(CartService::class);
    $cart = $cartService->getOrCreateCart($this->user);
    $cartService->addProduct($cart, $product, 1);

    $checkoutService = app(CheckoutService::class);
    $checkoutService->processCheckout($cart);

    expect($cart->fresh()->items()->count())->toBe(0);
});

test('order detail shows pricing breakdown', function () {
    $this->actingAs($this->user);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'subtotal' => '100.00',
        'vat_amount' => '7.50',
        'total' => '107.50',
        'pricing_breakdown' => [
            'vat' => [
                'label' => 'VAT (7.5%)',
                'amount' => '7.50',
                'rate' => '7.5',
            ],
        ],
    ]);

    Volt::test('orders.show', ['order' => $order])
        ->assertSee('100.00')
        ->assertSee('VAT (7.5%)')
        ->assertSee('7.50')
        ->assertSee('107.50');
});

test('orders are paginated', function () {
    $this->actingAs($this->user);

    Order::factory()->count(15)->create(['user_id' => $this->user->id]);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
    // Should see pagination since we have more than 10 orders
    $this->assertDatabaseCount('orders', 15);
});
