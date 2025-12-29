# Database Schema & Models

## Entity Relationship Diagram

```
┌─────────────────┐
│     users       │
│─────────────────│
│ id              │
│ name            │
│ email           │
│ password        │
│ timestamps      │
└────────┬────────┘
         │
         ├──────────────────────────────────┐
         │                                  │
         │ 1:1                              │ 1:1
         ▼                                  ▼
┌─────────────────┐              ┌─────────────────┐
│     carts       │              │   wishlists     │
│─────────────────│              │─────────────────│
│ id              │              │ id              │
│ user_id (FK)    │              │ user_id (FK)    │
│ timestamps      │              │ timestamps      │
└────────┬────────┘              └────────┬────────┘
         │                                │
         │ 1:N                            │ 1:N
         ▼                                ▼
┌─────────────────┐              ┌─────────────────┐
│   cart_items    │              │ wishlist_items  │
│─────────────────│              │─────────────────│
│ id              │              │ id              │
│ cart_id (FK)    │              │ wishlist_id(FK) │
│ product_id (FK) │              │ product_id (FK) │
│ quantity        │              │ timestamps      │
│ timestamps      │              └────────┬────────┘
└────────┬────────┘                       │
         │                                │
         │ N:1                            │ N:1
         └────────────┬───────────────────┘
                      ▼
              ┌─────────────────┐
              │    products     │
              │─────────────────│
              │ id              │
              │ uuid (unique)   │
              │ name            │
              │ description     │
              │ price (decimal) │
              │ stock_quantity  │
              │ images (JSON)   │
              │ timestamps      │
              │ deleted_at      │
              └────────┬────────┘
                       │
                       │ 1:N
                       ▼
              ┌─────────────────┐
              │  order_items    │
              │─────────────────│
              │ id              │
              │ order_id (FK)   │
              │ product_id (FK) │
              │ quantity        │
              │ price_snapshot  │
              │ product_snapshot│
              │ timestamps      │
              └────────┬────────┘
                       │
                       │ N:1
                       ▼
              ┌─────────────────┐
              │     orders      │
              │─────────────────│
              │ id              │
              │ uuid (unique)   │
              │ order_number    │
              │ user_id (FK)    │
              │ subtotal        │
              │ vat_rate        │
              │ vat_amount      │
              │ total           │
              │ status          │
              │ timestamps      │
              └─────────────────┘
                       ▲
                       │ 1:N
                       │
               ┌───────┴────────┐
               │     users      │
               └────────────────┘
```

---

## Table Descriptions

### Products
**Purpose:** Store product catalog with inventory

**Key Fields:**
- `uuid` - Public-facing identifier (indexed)
- `name` - Product name
- `description` - Product details
- `price` - Decimal(10,2) for precision
- `stock_quantity` - Available inventory (indexed)
- `images` - JSON array with multiple images (url, alt, is_primary, order)
- `deleted_at` - Soft delete for order history

**Indexes:**
- uuid, stock_quantity, created_at, deleted_at

**Notes:**
- Uses UUID for non-enumerable URLs
- Images stored as JSON for flexibility
- Soft deletes preserve order history

---

### Carts
**Purpose:** One cart per authenticated user

**Key Fields:**
- `user_id` - Foreign key (unique, indexed)

**Constraints:**
- One-to-one with users
- Cascade delete with user

---

### Cart Items
**Purpose:** Products in user's cart

**Key Fields:**
- `cart_id` - Foreign key (indexed)
- `product_id` - Foreign key (indexed)
- `quantity` - Must be > 0

**Constraints:**
- Unique per cart/product combination
- Quantity check constraint
- Cascade delete with cart/product

**Indexes:**
- cart_id, product_id, updated_at

---

### Wishlists
**Purpose:** One wishlist per authenticated user

**Key Fields:**
- `user_id` - Foreign key (unique, indexed)

**Constraints:**
- One-to-one with users
- Cascade delete with user

---

### Wishlist Items
**Purpose:** Products saved for later

**Key Fields:**
- `wishlist_id` - Foreign key (indexed)
- `product_id` - Foreign key (indexed)

**Constraints:**
- Unique per wishlist/product combination
- No quantity field (save only)
- Cascade delete with wishlist/product

**Indexes:**
- wishlist_id, product_id, created_at

---

### Orders
**Purpose:** Customer orders with VAT breakdown

**Key Fields:**
- `uuid` - Public-facing identifier (indexed)
- `order_number` - Human-readable (e.g., ORD-20231229-0001)
- `user_id` - Foreign key (indexed)
- `subtotal` - Before VAT (decimal 10,2)
- `vat_rate` - Percentage at order time (decimal 5,2, default 7.5%)
- `vat_amount` - Calculated VAT (decimal 10,2)
- `total` - Final amount (decimal 10,2)
- `status` - pending, completed, cancelled

**Indexes:**
- uuid, order_number, user_id, status, [created_at + status]

**Notes:**
- UUID for secure URLs
- Stores VAT rate for historical accuracy
- Auto-generates order number on creation
- VAT configurable via environment (default 7.5%)

---

### Order Items
**Purpose:** Line items in orders with historical data

**Key Fields:**
- `order_id` - Foreign key (indexed, cascade delete)
- `product_id` - Foreign key (indexed, no cascade)
- `quantity` - Items ordered
- `price_snapshot` - Price at purchase (decimal 10,2)
- `product_snapshot` - Complete product JSON at purchase time

**Indexes:**
- order_id, product_id

**Notes:**
- Product snapshot preserves complete history
- No cascade on product deletion (preserve order data)
- Snapshot includes: id, uuid, name, description, price, images
- Essential for refunds, disputes, and historical accuracy

---

## Model Overview

### Product
- Auto-generates UUID on creation
- Route binding via UUID
- Price mutator/accessor using bcmath
- Images cast to array
- Soft deletes
- Stock validation methods
- Scopes: `lowStock()`, `inStock()`
- Relationships: hasMany CartItem, WishlistItem, OrderItem

### Cart
- One per user
- Calculates total using bcmath
- Relationships: belongsTo User, hasMany CartItem

### CartItem
- Unique per cart/product
- Quantity validation
- Calculates subtotal
- Relationships: belongsTo Cart, Product

### Wishlist
- One per user
- Relationships: belongsTo User, hasMany WishlistItem

### WishlistItem
- Unique per wishlist/product
- No quantity field
- Relationships: belongsTo Wishlist, Product

### Order
- Auto-generates UUID and order number
- Route binding via UUID
- Stores VAT breakdown
- Status tracking
- Scopes: `completed()`, `forDate()`
- Relationships: belongsTo User, hasMany OrderItem

### OrderItem
- Stores price and product snapshots
- Calculates subtotal
- Relationships: belongsTo Order, Product

### User
- Relationships: hasOne Cart, Wishlist; hasMany Order

---

## Next Steps

Proceed to [03-BUSINESS-LOGIC.md](03-BUSINESS-LOGIC.md)
