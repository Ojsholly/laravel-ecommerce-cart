# Laravel E-Commerce Shopping Cart

A modern, production-ready e-commerce shopping cart built with Laravel 12, Livewire 3, and Flux UI.

[![Tests](https://img.shields.io/badge/tests-58%20passing-success)](tests)
[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-purple)](https://livewire.laravel.com)
[![Pest](https://img.shields.io/badge/Pest-4-green)](https://pestphp.com)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [Running the Application](#-running-the-application)
- [Testing](#-testing)
- [Key Features](#-key-features-in-detail)
- [Architecture](#-architecture)
- [API Documentation](#-api-documentation)
- [Contributing](#-contributing)

---

## ✨ Features

- 🛒 **Shopping Cart** - Add, update, remove products with real-time stock validation
- ❤️ **Wishlist** - Save items for later, move between cart and wishlist
- 💳 **Checkout** - Complete order processing with VAT calculation
- 📦 **Order Management** - Track orders with unique order numbers and snapshots
- 📊 **Daily Sales Reports** - Automated email reports with sales statistics
- 🔔 **Low Stock Notifications** - Automatic alerts when products run low
- 🧪 **Comprehensive Testing** - 58 passing tests with Pest framework
- 🎨 **Modern UI** - Built with Flux UI and Tailwind CSS
- ⚡ **Real-time Updates** - Livewire-powered reactive components

---

## 🛠 Tech Stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Livewire 3, Volt, Flux UI (Free), Tailwind CSS 4
- **Database:** PostgreSQL (SQLite for testing)
- **Testing:** Pest 4, PHPUnit 12
- **Authentication:** Laravel Fortify
- **Queue:** Database driver (configurable)
- **Mail:** Log driver (development), SMTP (production)

---

## 📦 Requirements

- PHP 8.4+
- Composer
- Node.js 18+ & NPM
- PostgreSQL 14+
- Laravel Herd (recommended) or Valet/Homestead

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Ojsholly/laravel-ecommerce-cart.git
cd laravel-ecommerce-cart
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your settings:

```env
APP_NAME="E-Commerce Cart"
APP_URL=http://laravel-ecommerce-cart.test

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_ecommerce_cart
DB_USERNAME=your_username
DB_PASSWORD=your_password

# VAT Configuration (default 7.5%)
VAT_RATE=7.5

# Admin email for notifications
MAIL_ADMIN_EMAIL=admin@example.com

# Queue configuration
QUEUE_CONNECTION=database
```

---

## 💾 Database Setup

### 1. Create Database

```bash
# PostgreSQL
createdb laravel_ecommerce_cart
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Database (Recommended)

```bash
php artisan db:seed
```

This will create:
- **3 test users** with credentials (see below)
- **25 products** (20 in stock, 3 low stock, 2 out of stock)
- **Sample cart** with items for testing
- **Sample orders** (completed and pending)

#### 🔑 Test Credentials

After seeding, you can log in with these accounts:

| Email | Password | Role | Description |
|-------|----------|------|-------------|
| `admin@ecommerce.test` | `password` | Admin | Admin user for testing |
| `customer@ecommerce.test` | `password` | Customer | Has sample cart & orders |
| `jane@ecommerce.test` | `password` | Customer | Has sample orders |

**Note:** All passwords are `password` for easy testing.

---

## 🏃 Running the Application

### Development Server

```bash
# Start Laravel development server
php artisan serve

# Or use Laravel Herd (recommended)
# Site will be available at: https://laravel-ecommerce-cart.test
```

### Build Assets

```bash
# Development
npm run dev

# Production
npm run build

# Watch for changes
npm run dev
```

### Queue Worker

```bash
# Start queue worker for background jobs
php artisan queue:work

# Or use Horizon (if installed)
php artisan horizon
```

### Scheduler

```bash
# Add to crontab for production
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# For development, run manually
php artisan schedule:work
```

---

## 🧪 Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suites

```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/CartServiceTest.php

# With coverage
php artisan test --coverage
```

### Test Results

```
✓ 58 tests passing (129 assertions)
✓ Duration: ~3.5s
```

**Test Coverage:**
- Cart operations (10 tests)
- Checkout process (10 tests)
- Price calculations (5 tests)
- Authentication (33 tests)

---

## 🎯 Key Features in Detail

### Shopping Cart

- **Add to Cart:** Real-time stock validation
- **Update Quantity:** Prevents exceeding available stock
- **Remove Items:** Clean deletion with database cleanup
- **Clear Cart:** Remove all items at once
- **Move to Wishlist:** Transfer items between cart and wishlist

### Checkout Process

- **Stock Validation:** Checks availability before order creation
- **Partial Checkout:** Process available items if some are out of stock
- **VAT Calculation:** Configurable tax rate with precise calculations
- **Product Snapshots:** Historical record of product details at purchase time
- **Order Numbers:** Unique, date-based order identifiers

### Background Jobs

- **Daily Sales Report:** Automated email at midnight with sales statistics
- **Low Stock Alerts:** Notifications when products fall below threshold
- **Queue Processing:** Asynchronous job handling for better performance

### Price Calculation Pipeline

Uses Laravel Pipeline pattern for flexible pricing:
1. Calculate subtotal from cart items
2. Apply VAT based on configuration
3. Calculate final total

---

## 🏗 Architecture

### Services

- **CartService:** Manages shopping cart operations
- **WishlistService:** Handles wishlist functionality
- **CheckoutService:** Processes orders and manages stock
- **PriceCalculationService:** Calculates pricing with VAT using pipeline pattern

### Models

- **Product:** Product catalog with stock management
- **Cart/CartItem:** User shopping carts
- **Wishlist/WishlistItem:** User wishlists
- **Order/OrderItem:** Completed orders with snapshots
- **User:** Customer accounts with Fortify authentication

### Jobs

- **SendDailySalesReport:** Daily sales summary emails
- **SendLowStockNotification:** Low inventory alerts

### Livewire Components

- **Product Listing:** Browse products with stock indicators
- **Cart Management:** Real-time cart updates
- **Checkout:** Order placement with validation
- **Wishlist:** Manage saved items

---

## 📚 API Documentation

### CartService

```php
// Get or create cart for user
$cart = $cartService->getOrCreateCart($user);

// Add product to cart
$cartItem = $cartService->addProduct($cart, $product, $quantity);

// Update quantity (throws exception if quantity <= 0)
$cartItem = $cartService->updateQuantity($cartItem, $newQuantity);

// Remove item
$cartService->removeItem($cartItem);

// Clear cart
$cartService->clearCart($cart);

// Move to wishlist
$cartService->moveToWishlist($cartItem, $user);
```

### CheckoutService

```php
// Process checkout
$order = $checkoutService->processCheckout($cart);
```

### PriceCalculationService

```php
// Calculate pricing with VAT
$pricing = $priceService->calculateOrderPricing($items, $vatRate);

// Access pricing details
$pricing->subtotal;
$pricing->total;
$pricing->breakdown['vat']['rate'];
$pricing->breakdown['vat']['amount'];
```

---

## 🤝 Contributing

This is an assessment project. For production use, consider:

1. Adding API endpoints for mobile apps
2. Implementing payment gateway integration
3. Adding product categories and filters
4. Implementing product reviews and ratings
5. Adding coupon/discount system
6. Implementing inventory management
7. Adding shipping calculation

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Ojsholly**  
GitHub: [@Ojsholly](https://github.com/Ojsholly)

---

## 🙏 Acknowledgments

- Laravel Framework
- Livewire & Volt
- Flux UI
- Pest PHP
- Tailwind CSS
