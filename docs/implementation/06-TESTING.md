# Testing Strategy

## Overview

Use Pest for testing. Focus on feature tests for business logic, unit tests for models.

---

## Test Structure

### Feature Tests
**Location:** `tests/Feature/`

**Coverage:**
- Cart operations (add, update, remove)
- Wishlist operations (add, remove, toggle)
- Checkout process (validation, order creation, stock decrement)
- Stock concurrency
- Partial checkout
- Job dispatching

### Unit Tests
**Location:** `tests/Unit/`

**Coverage:**
- Model methods (stock validation, price calculations)
- Scopes
- Relationships
- Accessors/mutators

---

## Cart Tests

### CartServiceTest
**Purpose:** Test cart operations

**Test Cases:**
- Add product to cart
- Add product with insufficient stock (exception)
- Update quantity
- Update with insufficient stock (exception)
- Remove item
- Clear cart
- Move to wishlist

---

## Checkout Tests

### CheckoutServiceTest
**Purpose:** Test checkout process

**Test Cases:**
- Successful checkout
- Empty cart (exception)
- All items out of stock (exception)
- Partial checkout (some items unavailable)
- Stock concurrency (pessimistic locking)
- Order creation with VAT breakdown
- Stock decrement
- Cart cleared after checkout
- Low stock notification dispatched

---

## Wishlist Tests

### WishlistServiceTest
**Purpose:** Test wishlist operations

**Test Cases:**
- Add product to wishlist
- Remove from wishlist
- Toggle product
- Move to cart
- Move to cart with insufficient stock (exception)

---

## Job Tests

### SendLowStockNotificationTest
**Purpose:** Test low stock notification job

**Test Cases:**
- Job dispatched when stock below threshold
- Email sent with correct data
- Job not dispatched when stock above threshold

### SendDailySalesReportTest
**Purpose:** Test daily sales report job

**Test Cases:**
- Report includes today's orders only
- Correct totals calculated
- Email sent with correct data
- Empty report when no orders

---

## Model Tests

### ProductTest
**Purpose:** Test product model methods

**Test Cases:**
- `hasStock()` returns correct boolean
- `isLowStock()` detects low stock
- `isOutOfStock()` detects out of stock
- Price mutator/accessor with bcmath
- Images cast to array
- UUID auto-generated
- Soft delete works

---

## Running Tests

**All tests:**
```
php artisan test
```

**Specific file:**
```
php artisan test tests/Feature/CartServiceTest.php
```

**With coverage:**
```
php artisan test --coverage
```

---

## Test Database

**Configuration:** Use in-memory SQLite for speed

**Environment:** `.env.testing`
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

---

## Factories

**Purpose:** Generate test data

**Models:**
- ProductFactory - Creates products with random data
- UserFactory - Creates users
- OrderFactory - Creates orders with items

**Usage:** Use factories in tests for consistent data generation

---

## Next Steps

Proceed to [07-CHECKLIST.md](07-CHECKLIST.md)
