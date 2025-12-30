<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding database...');

        // Create test users
        $this->command->info('👤 Creating test users...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@ecommerce.test'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@ecommerce.test'],
            [
                'name' => 'John Doe',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'jane@ecommerce.test'],
            [
                'name' => 'Jane Smith',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Created 3 test users');

        // Create products with varied stock levels
        $this->command->info('📦 Creating products...');

        $products = Product::factory(20)->create();
        $lowStockProducts = Product::factory(3)->lowStock()->create();
        $outOfStockProducts = Product::factory(2)->outOfStock()->create();

        $this->command->info('✅ Created 25 products (20 in stock, 3 low stock, 2 out of stock)');

        // Create sample cart for customer
        $this->command->info('🛒 Creating sample cart...');

        $cart = Cart::firstOrCreate(['user_id' => $customer->id]);
        $cart->items()->createMany([
            [
                'product_id' => $products->first()->id,
                'quantity' => 2,
            ],
            [
                'product_id' => $products->skip(1)->first()->id,
                'quantity' => 1,
            ],
        ]);

        $this->command->info('✅ Created sample cart with 2 items');

        // Create sample orders
        $this->command->info('📋 Creating sample orders...');

        $this->createSampleOrders($customer, $products);
        $this->createSampleOrders($customer2, $products);

        $this->command->info('✅ Created sample orders');

        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@ecommerce.test', 'password', 'Admin'],
                ['customer@ecommerce.test', 'password', 'Customer'],
                ['jane@ecommerce.test', 'password', 'Customer'],
            ]
        );
    }

    /**
     * Create sample orders for a user.
     */
    private function createSampleOrders(User $user, $products): void
    {
        // Create a completed order from yesterday
        $order1 = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'subtotal' => '150.00',
            'vat_rate' => 7.5,
            'vat_amount' => '11.25',
            'total' => '161.25',
            'status' => OrderStatus::COMPLETED,
            'created_at' => now()->subDay(),
        ]);

        $product1 = $products->random();
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price_snapshot' => $product1->price,
            'product_snapshot' => [
                'name' => $product1->name,
                'description' => $product1->description,
            ],
        ]);

        // Create a pending order from today
        $order2 = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'subtotal' => '89.99',
            'vat_rate' => 7.5,
            'vat_amount' => '6.75',
            'total' => '96.74',
            'status' => OrderStatus::PENDING,
            'created_at' => now(),
        ]);

        $product2 = $products->random();
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_snapshot' => $product2->price,
            'product_snapshot' => [
                'name' => $product2->name,
                'description' => $product2->description,
            ],
        ]);
    }
}
