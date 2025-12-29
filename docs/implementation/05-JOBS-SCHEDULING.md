# Background Jobs & Scheduling

## Overview

Background jobs handle asynchronous tasks like email notifications. Scheduler runs daily reports.

---

## Jobs

### SendLowStockNotification
**Purpose:** Email admin when product stock falls below threshold

**Trigger:** Dispatched after stock decrement in CheckoutService

**Process:**
- Receives Product model
- Checks if stock <= threshold
- Sends email to admin
- Includes product details and current stock level

**Queue:** Default queue

---

### SendDailySalesReport
**Purpose:** Email daily sales summary to admin

**Trigger:** Scheduled daily at 6 PM

**Process:**
- Query orders for current day
- Calculate totals (items sold, revenue, VAT collected)
- Group by product
- Format report
- Send email to admin

**Queue:** Default queue

---

## Mail Classes

### LowStockNotification
**Purpose:** Email template for low stock alerts

**Data:**
- Product name
- Current stock quantity
- Threshold value
- Product UUID (link to product)

---

### DailySalesReport
**Purpose:** Email template for daily sales summary

**Data:**
- Date
- Total orders
- Total revenue
- Total VAT collected
- Product breakdown (name, quantity sold, revenue)

---

## Scheduler Configuration

**File:** `bootstrap/app.php` or `routes/console.php`

**Schedule:**
- `SendDailySalesReport` - Daily at 18:00 (6 PM)

**Command:** `php artisan schedule:work` (development) or cron (production)

---

## Queue Configuration

**Driver:** Database (default)

**Setup:**
- Migration: `php artisan queue:table`
- Worker: `php artisan queue:work`
- Supervisor for production

**Environment:**
```
QUEUE_CONNECTION=database
```

---

## Configuration

**File:** `config/cart.php`
```
'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
'vat_rate' => env('VAT_RATE', 7.5),
```

**Environment Variables:**
```
MAIL_ADMIN_EMAIL=admin@example.com
LOW_STOCK_THRESHOLD=10
VAT_RATE=7.5
MAIL_MAILER=log
```

---

## Testing

### Job Testing
- Test job dispatched on stock decrement
- Test email sent with correct data
- Test daily report query and calculations

### Scheduler Testing
- Use `php artisan schedule:test` to verify schedule
- Test scheduled job execution

---

## Next Steps

Proceed to [06-TESTING.md](06-TESTING.md)
