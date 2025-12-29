# Business Logic & Services

## Service Layer Architecture

Business logic is encapsulated in service classes for testability and separation of concerns.

---

## Services Overview

### CartService
**Purpose:** Manage cart operations for authenticated users

**Key Methods:**
- `getOrCreateCart(User $user)` - Get or create user's cart
- `addProduct(Cart $cart, Product $product, int $quantity)` - Add/update cart items with stock validation
- `updateQuantity(CartItem $item, int $quantity)` - Update item quantity
- `removeItem(CartItem $item)` - Remove item from cart
- `clearCart(Cart $cart)` - Clear all cart items
- `moveToWishlist(CartItem $item)` - Move item to wishlist

**Implementation Notes:**
- Uses database transactions for atomic operations
- Validates stock availability before adding/updating
- Throws `InsufficientStockException` when stock unavailable

---

### WishlistService
**Purpose:** Manage wishlist operations

**Key Methods:**
- `getOrCreateWishlist(User $user)` - Get or create user's wishlist
- `addProduct(Wishlist $wishlist, Product $product)` - Add product to wishlist
- `removeItem(WishlistItem $item)` - Remove from wishlist
- `moveToCart(WishlistItem $item)` - Move to cart with stock validation
- `toggleProduct(Wishlist $wishlist, Product $product)` - Add/remove toggle

---

### CheckoutService
**Purpose:** Process checkout with transaction safety

**Key Responsibilities:**
- Validate cart and stock availability
- Calculate order totals using pipeline pattern
- Create orders with VAT breakdown
- Decrement stock atomically
- Support partial checkout
- Dispatch low stock notifications
- Clear cart after successful checkout

**Transaction Flow:**
1. Lock products for update
2. Validate stock availability
3. Calculate totals via pipeline
4. Create order and order items
5. Decrement stock
6. Clear cart items
7. Dispatch notifications

---

## Custom Exceptions

### InsufficientStockException
**Purpose:** Thrown when product stock is unavailable
**Usage:** Cart operations, checkout validation

### EmptyCartException
**Purpose:** Thrown when attempting checkout with empty cart
**Usage:** Checkout validation

---

## Price Calculation Pipeline

### PriceCalculationService
**Purpose:** Centralized service for all price calculations

**Methods:**
- `calculateSubtotal(array $items)` - Sum item prices
- `calculateVAT(string $amount, ?float $rate)` - Apply VAT percentage
- `calculateTotal(string $subtotal, string $vatAmount)` - Add subtotal + VAT
- `formatPrice(string $price)` - Format for display

**Pipeline Pattern:**
Use Laravel's Pipeline to process order totals through:
1. CalculateSubtotal - Sum all item prices
2. CalculateVAT - Apply VAT rate from config
3. CalculateFinalTotal - Add subtotal + VAT

**Implementation Notes:**
- All calculations use bcmath for precision
- VAT rate pulled from config (default 7.5%)
- Returns array with subtotal, vat_rate, vat_amount, total
- Used in CheckoutService for order creation

---

## Service Provider

**CartServiceProvider** - Optional service container bindings
- Bind CartService, WishlistService, CheckoutService as singletons
- Register in `bootstrap/providers.php`

---

## Next Steps

Proceed to [04-COMPONENTS-UI.md](04-COMPONENTS-UI.md)
