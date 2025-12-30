<?php

use App\Models\Product;
use App\Models\User;
use Livewire\Volt\Volt;

uses()->group('products');

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('products index page can be rendered', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('products.index'));

    $response->assertOk();
});

test('products index displays products', function () {
    $this->actingAs($this->user);

    $products = Product::factory()->count(5)->create();

    Volt::test('products.index')
        ->assertSee($products->first()->name)
        ->assertSee($products->last()->name);
});

test('products index can search products', function () {
    $this->actingAs($this->user);

    Product::factory()->create(['name' => 'Laptop Computer']);
    Product::factory()->create(['name' => 'Wireless Mouse']);

    $response = $this->get(route('products.index', ['search' => 'Laptop']));

    $response->assertSee('Laptop Computer');
});

test('product detail page can be rendered', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();

    $response = $this->get(route('products.show', $product));

    $response->assertOk();
});

test('product detail page displays product information', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create([
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => '99.99',
    ]);

    Volt::test('products.show', ['product' => $product])
        ->assertSee('Test Product')
        ->assertSee('Test Description')
        ->assertSee('99.99');
});

test('can add product to cart from product detail page', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);

    Volt::test('products.show', ['product' => $product])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertDispatched('cart-updated');

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

test('can toggle wishlist from product detail page', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();

    Volt::test('products.show', ['product' => $product])
        ->call('toggleWishlist')
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseHas('wishlist_items', [
        'product_id' => $product->id,
    ]);
});

test('can add product to cart from products index', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);

    Volt::test('products.index')
        ->call('addToCart', $product->id, 1)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('adding same product multiple times increases quantity', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);

    Volt::test('products.index')
        ->call('addToCart', $product->id, 1)
        ->call('addToCart', $product->id, 1)
        ->call('addToCart', $product->id, 1)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
});

test('cannot add out of stock product to cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 0]);

    Volt::test('products.show', ['product' => $product])
        ->set('quantity', 1)
        ->call('addToCart')
        ->assertDispatched('notify');

    $this->assertDatabaseMissing('cart_items', [
        'product_id' => $product->id,
    ]);
});
