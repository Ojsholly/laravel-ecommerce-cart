# Implementation Overview

## Executive Summary

This document outlines a pragmatic implementation plan for a simple e-commerce shopping cart system inspired by Jumia's approach. The solution prioritizes clarity, correctness, and Laravel best practices.

---

## Core Deliverables

1. ✅ Product browsing with database-backed inventory
2. ✅ Optimistic cart management (add, update, remove)
3. ✅ Wishlist functionality
4. ✅ Persistent cart/wishlist storage (database)
5. ✅ Checkout flow with real-time stock validation
6. ✅ Partial checkout support
7. ✅ Background job for low stock notifications
8. ✅ Scheduled daily sales report
9. ✅ Comprehensive feature tests

---

## Design Decisions (Confirmed with Client)

### Stock Management Strategy

**✅ Optimistic Cart Approach (Jumia-Inspired)**

**Why this approach?**
- Analyzed Jumia.com.ng (major African e-commerce platform)
- No time-based reservations (users can shop at their own pace)
- Cart items persist indefinitely
- Stock validation happens at checkout, not add-to-cart
- Out-of-stock items remain in cart but clearly marked
- Partial checkout support (proceed with available items)

**Key Benefits:**
- Better UX for thoughtful shoppers
- No artificial time pressure
- Simpler implementation (no reservation cleanup jobs)
- Industry-proven pattern
- Honest about availability

**How it works:**
```
1. Add to Cart: No stock decrement, just add to cart_items table
2. View Cart: Show real-time stock status for each item
3. Checkout: 
   - Lock products for update (transaction)
   - Validate stock for each item
   - If some unavailable: offer partial checkout
   - If all available: create order, decrement stock
```

---

## Wishlist Feature

**Inspired by Jumia's wishlist:**
- Separate `wishlist_items` table
- Users can save products for later
- Move items between cart and wishlist
- No stock validation for wishlist items
- Heart icon to add/remove from wishlist

**Use Cases:**
- Save items for future purchase
- Compare products over time
- Share wishlist with others (future enhancement)

---

## Technical Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.44.0 |
| PHP | PHP | 8.4.16 |
| Frontend | Livewire + Volt | 3.7.3 + 1.10.1 |
| UI Library | Flux UI (Free) | 2.10.2 |
| Styling | Tailwind CSS | 4.1.11 |
| Database | PostgreSQL | Latest |
| Testing | Pest | 4.2.0 |
| Queue | Database driver | Built-in |
| Email | Log driver | Built-in |

---

## Database Schema Philosophy

- **Normalized design** with proper foreign keys
- **No reservation columns** (optimistic approach)
- **Price snapshots** in order_items (protect against price changes)
- **Soft deletes** on products (preserve order history)
- **Timestamps** everywhere (audit trail)

---

## Concurrency Strategy

**Problem:** Two users checkout simultaneously for last item

**Solution:**
```php
DB::transaction(function () {
    // Lock products for update (row-level locking)
    $products = Product::whereIn('id', $productIds)
        ->lockForUpdate()
        ->get();
    
    // Validate stock
    // Create order
    // Decrement stock
});
```

**Result:** First transaction wins, second gets clear error message.

---

## Configuration

### Environment Variables

```env
MAIL_ADMIN_EMAIL=admin@example.com
LOW_STOCK_THRESHOLD=10
VAT_RATE=7.5
MAIL_MAILER=log
```

### Config Files

**`config/cart.php`:**
```php
return [
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
    'vat_rate' => env('VAT_RATE', 7.5),
];
```

---

## Time Estimates by Phase

| Phase | Task | Time |
|-------|------|------|
| 1 | Database migrations & models | 40 min |
| 2 | Business logic (services) | 50 min |
| 3 | Livewire components | 90 min |
| 4 | Jobs & scheduling | 60 min |
| 5 | Routes & navigation | 15 min |
| 6 | Database seeding | 20 min |
| 7 | Testing | 60 min |
| 8 | UI implementation | 30 min |
| 9 | Documentation | 15 min |
| **Total** | | **6h 30min** |

**Buffer:** 1.5 hours for debugging and refinement

---

## Key Features

### Cart Management
- Add products to cart
- Update quantities
- Remove items
- Clear cart
- Real-time stock indicators
- Partial checkout

### Wishlist
- Add/remove products
- Move to cart
- Persistent storage
- No stock validation

### Checkout
- Transaction-safe processing
- Stock validation
- Partial checkout support
- Order creation
- Email notifications

### Background Jobs
- Low stock notifications
- Daily sales reports
- Queue-based processing

---

## Success Criteria

✅ Users can browse products  
✅ Users can manage cart and wishlist  
✅ Checkout validates stock in real-time  
✅ Out-of-stock items handled gracefully  
✅ Orders created with price snapshots  
✅ Stock decrements on successful checkout  
✅ Low stock notifications sent  
✅ Daily sales reports generated  
✅ Comprehensive test coverage  
✅ Clean, maintainable code  

---

## Next Steps

1. Review this overview
2. Proceed to [02-DATABASE-SCHEMA.md](02-DATABASE-SCHEMA.md)
3. Follow [07-CHECKLIST.md](07-CHECKLIST.md) for implementation
