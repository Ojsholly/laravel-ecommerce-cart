<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Services\PriceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('pricing');

beforeEach(function () {
    $this->service = new PriceCalculationService();
});

test('formats price correctly', function () {
    expect($this->service->formatPrice('10.00'))->toBe('$10.00')
        ->and($this->service->formatPrice('99.99'))->toBe('$99.99')
        ->and($this->service->formatPrice('1234.56'))->toBe('$1,234.56');
});

test('calculates order pricing with vat', function () {
    config(['cart.vat_rate' => 7.5]);
    
    $product = Product::factory()->create(['price' => '100.00']);
    $cartItem = new CartItem(['quantity' => 2]);
    $cartItem->product = $product;

    $pricing = $this->service->calculateOrderPricing([$cartItem]);

    expect($pricing->subtotal)->toBe('200.00')
        ->and($pricing->total)->toBe('215.00')
        ->and($pricing->breakdown)->toHaveKey('vat')
        ->and($pricing->breakdown['vat']['amount'])->toBe('15.00');
});

test('calculates pricing with custom vat rate', function () {
    $product = Product::factory()->create(['price' => '100.00']);
    $cartItem = new CartItem(['quantity' => 1]);
    $cartItem->product = $product;

    $pricing = $this->service->calculateOrderPricing([$cartItem], 10.0);

    expect($pricing->subtotal)->toBe('100.00')
        ->and($pricing->total)->toBe('110.00')
        ->and($pricing->breakdown['vat']['amount'])->toBe('10.00')
        ->and($pricing->breakdown['vat']['rate'])->toBe(10.0);
});

test('pricing breakdown includes vat details', function () {
    config(['cart.vat_rate' => 7.5]);
    
    $product = Product::factory()->create(['price' => '50.00']);
    $cartItem = new CartItem(['quantity' => 1]);
    $cartItem->product = $product;

    $pricing = $this->service->calculateOrderPricing([$cartItem]);

    expect($pricing->breakdown)->toHaveKey('vat')
        ->and($pricing->breakdown['vat'])->toHaveKeys(['amount', 'label', 'rate'])
        ->and($pricing->breakdown['vat']['label'])->toBe('VAT (7.5%)');
});

test('pricing uses bcmath for precision', function () {
    $product = Product::factory()->create(['price' => '10.99']);
    $cartItem = new CartItem(['quantity' => 3]);
    $cartItem->product = $product;

    $pricing = $this->service->calculateOrderPricing([$cartItem], 7.5);

    expect($pricing->subtotal)->toBe('32.97')
        ->and($pricing->breakdown['vat']['amount'])->toBe('2.47')
        ->and($pricing->total)->toBe('35.44');
});
