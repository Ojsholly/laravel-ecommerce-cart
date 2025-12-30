# Laravel E-Commerce Shopping Cart

A modern, feature-complete e-commerce shopping cart application built with Laravel 12, Livewire 3, and Flux UI. This project demonstrates best practices in Laravel development, including service-oriented architecture, comprehensive testing, and modern UI/UX patterns.

[![Tests](https://img.shields.io/badge/tests-117%20passing-success)](tests)
[![Laravel](https://img.shields.io/badge/Laravel-12-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-purple)](https://livewire.laravel.com)
[![Pest](https://img.shields.io/badge/Pest-4-green)](https://pestphp.com)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%205-blue)](https://phpstan.org)

## 🎉 Project Status: COMPLETED

This project is feature-complete with all core e-commerce functionality implemented, tested, and documented.

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
- [Technical Decisions](#-technical-decisions)
- [Assumptions & Tradeoffs](#-assumptions--tradeoffs)
- [API Documentation](#-api-documentation)
- [Contributing](#-contributing)

---

## ✨ Features

### Core E-Commerce Functionality
- 🛒 **Shopping Cart** - Add, update, remove products with real-time stock validation
- ❤️ **Wishlist** - Save items for later, seamlessly move between cart and wishlist
- 💳 **Checkout** - Complete order processing with VAT calculation and stock validation
- 📦 **Order Management** - View order history, track order status, and view detailed order information
- 🛍️ **Product Catalog** - Browse products with search, stock indicators, and detailed product pages

### User Experience
- 🎨 **Modern UI** - Jumia-inspired product pages with Flux UI components and Tailwind CSS 4
- ⚡ **Real-time Updates** - Livewire-powered reactive components with instant feedback
- 📱 **Responsive Design** - Mobile-first design that works on all devices
- 🌙 **Dark Mode** - Full dark mode support throughout the application
- 🔔 **Smart Notifications** - Toast notifications for user actions and errors
- 💝 **Move to Wishlist Modal** - Enticing modal when removing cart items with wishlist option

### Technical Features
- 📊 **Daily Sales Reports** - Automated email reports with sales statistics
- 🔔 **Low Stock Notifications** - Automatic alerts when products run low
- 🧪 **Comprehensive Testing** - 117 passing tests (238 assertions) with Pest framework
- 🔍 **Static Analysis** - PHPStan Level 5 with Larastan for type safety
- 📝 **Code Quality** - Laravel Pint for consistent code style
- 📋 **Log Viewer** - Web-based log viewer for debugging and monitoring

---

## 🛠 Tech Stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Livewire 3, Volt, Flux UI (Free), Tailwind CSS 4
- **Database:** PostgreSQL (SQLite for testing)
- **Testing:** Pest 4, PHPUnit 12
- **Authentication:** Laravel Fortify
- **Queue:** Database driver (configurable)
- **Mail:** Log driver (development), SMTP (production)
- **Logging:** Laravel Log Viewer (opcodesio/log-viewer)

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

### Log Viewer

Access application logs through the web interface:

```bash
# Visit the log viewer in your browser
https://laravel-ecommerce-cart.test/log-viewer

# Or using artisan serve
http://localhost:8000/log-viewer
```

**Features:**
- 📊 View all application logs in a beautiful UI
- 🔍 Search and filter logs by level, date, and content
- 📧 View sent emails (when using log mail driver)
- 🐛 Debug errors and exceptions easily
- 📈 Log statistics and analytics

**Package:** [opcodesio/log-viewer](https://github.com/opcodesio/log-viewer)

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
✓ 117 tests passing (238 assertions)
✓ Duration: ~5s
✓ PHPStan Level 5: No errors
```

**Test Coverage:**
- **Products:** 10 tests (index, detail, search, add to cart, wishlist)
- **Cart:** 15 tests (CRUD operations, move to wishlist, sidebar wishlist)
- **Wishlist:** 10 tests (CRUD operations, move to cart, icon updates)
- **Orders:** 18 tests (list, detail, creation, authorization, pagination)
- **Navigation:** 12 tests (authentication, routing, icon badges)
- **Cart Service:** 10 tests (business logic)
- **Checkout Service:** 10 tests (order processing)
- **Price Calculation:** 5 tests (VAT calculations)
- **Authentication:** 27 tests (login, registration, 2FA)

---

## 🎯 Key Features in Detail

### Product Browsing

- **Product Listing:** Grid layout with pagination, search functionality
- **Product Detail Page:** Jumia-inspired layout with image gallery, quantity selector, stock badges
- **Search:** Real-time search across product names and descriptions
- **Stock Indicators:** Visual badges for in-stock, low stock, and out-of-stock items
- **Smart Add to Cart:** Initially shows add button, then quantity controls after first add

### Shopping Cart

- **Add to Cart:** Real-time stock validation with instant feedback
- **Update Quantity:** Plus/minus buttons with stock limit enforcement
- **Remove Items:** Confirmation modal with move-to-wishlist option
- **Clear Cart:** Remove all items at once with confirmation
- **Move to Wishlist:** Transfer items between cart and wishlist seamlessly
- **Wishlist Sidebar:** View wishlist items directly on cart page
- **Real-time Updates:** Cart icon badge updates instantly across all pages

### Wishlist

- **Add to Wishlist:** Save products for later from any page
- **Move to Cart:** Quick add from wishlist with stock validation
- **Remove Items:** Clean deletion with confirmation
- **Icon Badge:** Real-time count display in navigation
- **Empty State:** Helpful message with link to browse products

### Orders

- **Order History:** Paginated list of all user orders
- **Order Details:** Complete order information with item snapshots
- **Order Status:** Visual status badges (pending, completed, cancelled)
- **Product Snapshots:** Historical record preserving product details at purchase time
- **Pricing Breakdown:** Detailed view of subtotal, VAT, and total
- **Authorization:** Users can only view their own orders

### Checkout Process

- **Stock Validation:** Checks availability before order creation
- **Partial Checkout:** Process available items if some are out of stock
- **VAT Calculation:** Configurable tax rate (default 7.5%) with precise calculations
- **Order Numbers:** Unique, date-based identifiers (ORD-YYYYMMDD-XXXX)
- **Cart Clearing:** Automatic cart cleanup after successful order

### Background Jobs

- **Daily Sales Report:** Automated email at midnight with sales statistics
- **Low Stock Alerts:** Notifications when products fall below threshold (5 items)
- **Queue Processing:** Asynchronous job handling for better performance

### Price Calculation Pipeline

Uses Laravel Pipeline pattern for flexible, extensible pricing:
1. Calculate subtotal from cart items
2. Apply VAT based on configuration
3. Calculate final total
4. Generate pricing breakdown for display

---

## 🏗 Architecture

### Design Patterns

- **Service Layer Pattern:** Business logic separated from controllers/components
- **Repository Pattern:** Data access abstraction through Eloquent models
- **Pipeline Pattern:** Flexible price calculation with extensible steps
- **Factory Pattern:** Test data generation with realistic scenarios
- **Observer Pattern:** Livewire events for real-time UI updates

### Services

- **CartService:** Manages shopping cart operations (add, update, remove, clear, move to wishlist)
- **WishlistService:** Handles wishlist functionality (add, remove, move to cart)
- **CheckoutService:** Processes orders, validates stock, manages transactions
- **PriceCalculationService:** Calculates pricing with VAT using pipeline pattern

### Models

- **Product:** Product catalog with stock management, image handling, and snapshots
- **Cart/CartItem:** User shopping carts with item relationships
- **Wishlist/WishlistItem:** User wishlists with product relationships
- **Order/OrderItem:** Completed orders with product snapshots and pricing breakdown
- **User:** Customer accounts with Fortify authentication and 2FA support

### Enums

- **OrderStatus:** Type-safe order states (pending, completed, cancelled)

### Jobs

- **SendDailySalesReport:** Daily sales summary emails with statistics
- **SendLowStockNotification:** Low inventory alerts to administrators

### Livewire Components (Volt)

- **Products Index:** Browse products with search, pagination, and cart integration
- **Products Show:** Detailed product view with quantity selector and wishlist toggle
- **Cart Index:** Cart management with wishlist sidebar and move-to-wishlist modal
- **Wishlist Index:** Wishlist management with move-to-cart functionality
- **Orders Index:** Order history with pagination and status display
- **Orders Show:** Detailed order view with item snapshots and pricing breakdown
- **Checkout Index:** Order placement with stock validation and pricing calculation
- **Cart Icon:** Real-time cart count badge in navigation
- **Wishlist Icon:** Real-time wishlist count badge in navigation

### Frontend Architecture

- **Livewire 3 + Volt:** Single-file components for rapid development
- **Flux UI (Free):** Pre-built UI components for consistent design
- **Tailwind CSS 4:** Utility-first styling with dark mode support
- **Alpine.js:** Minimal JavaScript for interactive elements (via Livewire)
- **Vite:** Fast asset bundling and hot module replacement

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

## 💡 Technical Decisions

### Why Laravel 12?
- **Latest Features:** Access to newest Laravel features and improvements
- **Long-term Support:** Extended support timeline for production applications
- **Performance:** Improved query performance and optimization
- **Modern PHP:** Leverages PHP 8.4 features (constructor property promotion, enums, etc.)

### Why Livewire 3 + Volt?
- **Rapid Development:** Build reactive interfaces without writing JavaScript
- **Single-File Components:** Volt allows blade + logic in one file for simplicity
- **Real-time Updates:** Automatic UI synchronization without page reloads
- **SEO-Friendly:** Server-side rendering maintains good SEO performance
- **Developer Experience:** Familiar Laravel/Blade syntax with reactive capabilities

### Why Flux UI (Free Edition)?
- **Pre-built Components:** Accelerates development with ready-to-use UI elements
- **Consistent Design:** Maintains design consistency across the application
- **Accessibility:** Built-in accessibility features (ARIA labels, keyboard navigation)
- **Dark Mode:** Native dark mode support out of the box
- **Livewire Integration:** Designed specifically for Livewire applications

### Why Service Layer Architecture?
- **Separation of Concerns:** Business logic separated from presentation layer
- **Testability:** Services can be unit tested independently
- **Reusability:** Services can be used across multiple components/controllers
- **Maintainability:** Changes to business logic don't affect UI components
- **Type Safety:** Strong typing with PHPDoc and PHPStan validation

### Why Pipeline Pattern for Pricing?
- **Extensibility:** Easy to add new pricing steps (discounts, shipping, etc.)
- **Flexibility:** Steps can be added/removed without changing core logic
- **Testability:** Each step can be tested independently
- **Clarity:** Clear, linear flow of pricing calculations
- **Future-Proof:** Ready for complex pricing scenarios (coupons, promotions, etc.)

### Why Product Snapshots?
- **Historical Accuracy:** Preserves product details as they were at purchase time
- **Price Protection:** Customers see the price they paid, not current price
- **Audit Trail:** Complete record for accounting and dispute resolution
- **Data Integrity:** Orders remain valid even if products are deleted/modified

### Why PostgreSQL?
- **Data Integrity:** Strong ACID compliance for financial transactions
- **JSON Support:** Native JSON columns for flexible data (images, pricing breakdown)
- **Performance:** Excellent performance for complex queries and aggregations
- **Scalability:** Handles growth from small to large-scale applications
- **Production Ready:** Industry-standard database for production applications

### Why Pest over PHPUnit?
- **Readability:** More expressive test syntax
- **Developer Experience:** Better error messages and test output
- **Modern Approach:** Designed for modern PHP and Laravel
- **Less Boilerplate:** Cleaner test code with less setup

---

## 🤔 Assumptions & Tradeoffs

### Assumptions Made

1. **Single Currency:** Application assumes a single currency (no multi-currency support)
   - **Rationale:** Simplifies pricing calculations and display
   - **Future:** Can be extended with currency conversion service

2. **Single VAT Rate:** One VAT rate applies to all products
   - **Rationale:** Simplifies tax calculations and configuration
   - **Future:** Can be extended to support per-product or per-category rates

3. **No Payment Integration:** Orders are created without actual payment processing
   - **Rationale:** Payment gateways require external accounts and API keys
   - **Future:** Can integrate Stripe, PayPal, or other payment providers

4. **Email Notifications:** Uses log driver in development
   - **Rationale:** Avoids need for SMTP configuration during development
   - **Production:** Configure SMTP or mail service (Mailgun, SendGrid, etc.)

5. **Simple Stock Management:** No warehouse locations or complex inventory
   - **Rationale:** Keeps the system simple and focused on core features
   - **Future:** Can add multi-warehouse support and advanced inventory

6. **No Product Variants:** Products don't have sizes, colors, or other variants
   - **Rationale:** Simplifies product model and cart logic
   - **Future:** Can implement variant system with separate SKUs

7. **Single Admin Email:** Low stock notifications go to one admin email
   - **Rationale:** Simple configuration for small teams
   - **Future:** Can implement role-based notification system

### Tradeoffs Made

1. **Livewire vs. API + SPA**
   - **Chosen:** Livewire for full-stack simplicity
   - **Tradeoff:** Less suitable for mobile apps (would need separate API)
   - **Benefit:** Faster development, no API versioning complexity, better SEO

2. **Service Layer vs. Repository Pattern**
   - **Chosen:** Service layer with direct Eloquent usage
   - **Tradeoff:** Tighter coupling to Eloquent ORM
   - **Benefit:** Less abstraction overhead, more Laravel-idiomatic code

3. **Database Queue vs. Redis/SQS**
   - **Chosen:** Database queue driver
   - **Tradeoff:** Lower performance for high-volume queues
   - **Benefit:** No additional infrastructure, easier local development

4. **Flux UI Free vs. Custom Components**
   - **Chosen:** Flux UI for rapid development
   - **Tradeoff:** Limited to free components, less customization
   - **Benefit:** Faster development, consistent design, maintained by experts

5. **Monolithic vs. Microservices**
   - **Chosen:** Monolithic Laravel application
   - **Tradeoff:** Harder to scale individual components independently
   - **Benefit:** Simpler deployment, easier development, lower operational complexity

6. **Real-time Stock Updates**
   - **Chosen:** Validation at checkout, not real-time reservation
   - **Tradeoff:** Possible race conditions for last items in stock
   - **Benefit:** Simpler implementation, better performance
   - **Mitigation:** Database transactions prevent overselling

7. **Image Storage**
   - **Chosen:** JSON column with URLs (using placeholder service)
   - **Tradeoff:** No actual file upload/storage implemented
   - **Benefit:** Simplified development, focus on core features
   - **Future:** Can integrate with S3, Cloudinary, or local storage

8. **Search Implementation**
   - **Chosen:** Simple LIKE queries on name/description
   - **Tradeoff:** Limited search capabilities, no relevance ranking
   - **Benefit:** No additional dependencies, works out of the box
   - **Future:** Can integrate Laravel Scout with Algolia/Meilisearch

### Performance Considerations

1. **Eager Loading:** All relationships are eager-loaded to prevent N+1 queries
2. **Pagination:** Large datasets are paginated (12 items per page for products, 10 for orders)
3. **Database Indexes:** Foreign keys and frequently queried columns are indexed
4. **Query Optimization:** PHPStan ensures type safety and catches potential issues
5. **Caching:** Can be added for product listings and frequently accessed data

### Edge Cases & Business Logic

Edge cases around partial checkout and notification thresholds were handled intentionally to reflect real-world behavior:

1. **Partial Checkout:** When some items are out of stock during checkout, available items are purchased and removed from cart, while unavailable items remain in cart for later purchase
2. **Low Stock Notifications:** Notifications are only sent when stock crosses the threshold (e.g., from 6 to 5 when threshold is 5), preventing notification spam
3. **Stock Validation:** Products are locked during checkout to prevent race conditions in concurrent purchases

### Security Considerations

1. **Authentication:** Laravel Fortify with 2FA support
2. **Authorization:** Policy-based access control for orders
3. **CSRF Protection:** Enabled for all forms and AJAX requests
4. **SQL Injection:** Protected via Eloquent ORM and parameter binding
5. **XSS Protection:** Blade templates auto-escape output
6. **Mass Assignment:** Fillable/guarded properties on all models

---

## 🚀 Future Enhancements

While the current implementation is feature-complete, here are potential enhancements:

1. **Payment Integration:** Stripe, PayPal, or other payment gateways
2. **Product Categories:** Hierarchical category system with filtering
3. **Product Reviews:** Customer reviews and ratings
4. **Coupon System:** Discount codes and promotional offers
5. **Shipping Calculation:** Multiple shipping methods and rate calculation
6. **Product Variants:** Size, color, and other product variations
7. **Advanced Search:** Full-text search with Laravel Scout
8. **Inventory Management:** Multi-warehouse support and stock transfers
9. **API Endpoints:** RESTful API for mobile apps
10. **Admin Dashboard:** Product management, order processing, analytics
11. **Email Templates:** Branded email templates for notifications
12. **Multi-currency:** Support for multiple currencies with conversion
13. **Multi-language:** Internationalization support
14. **Social Login:** OAuth integration (Google, Facebook, etc.)
15. **Analytics:** Integration with Google Analytics or custom analytics

---

## 🤝 Contributing

This project demonstrates best practices in Laravel development. Feel free to use it as a reference or starting point for your own e-commerce projects.

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
