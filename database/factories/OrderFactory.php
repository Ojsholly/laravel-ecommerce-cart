<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'order_number' => 'ORD-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'subtotal' => fake()->randomFloat(2, 10, 500),
            'vat_rate' => '7.50',
            'vat_amount' => function (array $attributes) {
                return bcmul($attributes['subtotal'], '0.075', 2);
            },
            'total' => function (array $attributes) {
                return bcadd($attributes['subtotal'], $attributes['vat_amount'], 2);
            },
            'status' => 'completed',
            'pricing_breakdown' => function (array $attributes) {
                return [
                    'vat' => [
                        'label' => 'VAT (7.5%)',
                        'amount' => $attributes['vat_amount'],
                        'rate' => '7.5',
                    ],
                ];
            },
        ];
    }
}
