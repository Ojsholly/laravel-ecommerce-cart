<?php

use App\Models\Product;
use App\Models\User;
use App\Services\WishlistService;
use Livewire\Volt\Volt;

uses()->group('wishlist');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wishlistService = app(WishlistService::class);
});

test('wishlist page can be rendered', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('wishlist.index'));

    $response->assertOk();
});

test('wishlist page displays wishlist items', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['name' => 'Wishlist Product']);
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $this->wishlistService->addProduct($wishlist, $product);

    Volt::test('wishlist.index')
        ->assertSee('Wishlist Product');
});

test('can add product to wishlist', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);

    $wishlistItem = $this->wishlistService->addProduct($wishlist, $product);

    expect($wishlistItem->product_id)->toBe($product->id);
    $this->assertDatabaseHas('wishlist_items', [
        'wishlist_id' => $wishlist->id,
        'product_id' => $product->id,
    ]);
});

test('can remove item from wishlist', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $wishlistItem = $this->wishlistService->addProduct($wishlist, $product);

    Volt::test('wishlist.index')
        ->call('removeItem', $wishlistItem->id)
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
});

test('can move wishlist item to cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 10]);
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $wishlistItem = $this->wishlistService->addProduct($wishlist, $product);

    Volt::test('wishlist.index')
        ->call('moveToCart', $wishlistItem->id)
        ->assertDispatched('cart-updated')
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItem->id]);
    $this->assertDatabaseHas('cart_items', ['product_id' => $product->id]);
});

test('cannot move out of stock item to cart', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create(['stock_quantity' => 0]);
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $wishlistItem = $this->wishlistService->addProduct($wishlist, $product);

    Volt::test('wishlist.index')
        ->call('moveToCart', $wishlistItem->id)
        ->assertDispatched('notify');

    $this->assertDatabaseHas('wishlist_items', ['id' => $wishlistItem->id]);
    $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
});

test('wishlist displays multiple items', function () {
    $this->actingAs($this->user);

    $product1 = Product::factory()->create(['name' => 'Product One']);
    $product2 = Product::factory()->create(['name' => 'Product Two']);
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $this->wishlistService->addProduct($wishlist, $product1);
    $this->wishlistService->addProduct($wishlist, $product2);

    Volt::test('wishlist.index')
        ->assertSee('Product One')
        ->assertSee('Product Two');
});

test('empty wishlist shows empty state', function () {
    $this->actingAs($this->user);

    Volt::test('wishlist.index')
        ->assertSee('Your wishlist is empty')
        ->assertSee('Browse Products');
});

test('wishlist icon updates with item count', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);
    $this->wishlistService->addProduct($wishlist, $product);

    Volt::test('wishlist.icon')
        ->assertSee('1');
});

test('adding duplicate product to wishlist does not create duplicate', function () {
    $this->actingAs($this->user);

    $product = Product::factory()->create();
    $wishlist = $this->wishlistService->getOrCreateWishlist($this->user);

    $this->wishlistService->addProduct($wishlist, $product);
    $this->wishlistService->addProduct($wishlist, $product);

    expect($wishlist->fresh()->items()->count())->toBe(1);
});
