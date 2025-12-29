<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productNames = [
            'Wireless Bluetooth Headphones',
            'Smart Watch Pro',
            'USB-C Fast Charger',
            'Portable Power Bank',
            'Laptop Stand Aluminum',
            'Mechanical Keyboard RGB',
            'Wireless Mouse Ergonomic',
            'Phone Case Premium',
            'Screen Protector Tempered Glass',
            'Cable Organizer Set',
        ];

        $name = fake()->randomElement($productNames);

        return [
            'name' => $name,
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 9.99, 299.99),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'images' => [
                [
                    'url' => 'https://via.placeholder.com/400x400?text='.urlencode($name),
                    'alt' => $name.' - Front view',
                    'is_primary' => true,
                    'order' => 1,
                ],
                [
                    'url' => 'https://via.placeholder.com/400x400?text='.urlencode($name.' 2'),
                    'alt' => $name.' - Side view',
                    'is_primary' => false,
                    'order' => 2,
                ],
            ],
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(1, 5),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
