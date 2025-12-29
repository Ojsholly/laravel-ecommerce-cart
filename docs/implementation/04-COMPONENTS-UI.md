# Livewire Components & UI

## Component Structure

All components use Livewire Volt for clean, functional syntax.

---

## Product List Component
**Location:** `resources/views/livewire/products/index.blade.php`

**Purpose:** Display paginated product catalog

**Features:**
- Grid layout (responsive)
- Product cards with image, name, description, price
- Stock status badges (in stock, low stock, out of stock)
- Add to cart button
- Wishlist toggle button
- Pagination

**State:** `perPage` (default 12)
**Computed:** `products` (paginated with wishlist items)

---

## Add to Cart Button Component
**Location:** `resources/views/livewire/cart/add-to-cart-button.blade.php`

**Purpose:** Add products to cart with validation

**Features:**
- Stock validation
- Quantity input
- Loading states
- Success/error notifications
- Cart update events

**State:** `productId`, `quantity`
**Actions:** `addToCart()` - Validates stock, calls CartService, dispatches events

---

## Wishlist Toggle Button Component
**Location:** `resources/views/livewire/wishlist/toggle-button.blade.php`

**Purpose:** Add/remove products from wishlist

**Features:**
- Toggle functionality
- Icon state (filled/outline)
- Loading states
- Notifications

**State:** `productId`, `isInWishlist`
**Actions:** `toggle()` - Calls WishlistService, updates state

---

## Cart Summary Component
**Location:** `resources/views/livewire/cart/summary.blade.php`

**Purpose:** Display cart totals in header/sidebar

**Features:**
- Item count badge
- Subtotal display
- VAT display
- Total display
- Dropdown preview
- Link to cart page

**Computed:** `cart`, `itemCount`, `subtotal`, `vatAmount`, `total`
**Listeners:** `cart-updated` - Refreshes cart data

---

## Cart Page Component
**Location:** `resources/views/livewire/cart/show.blade.php`

**Purpose:** Full cart management page

**Features:**
- List all cart items
- Update quantities
- Remove items
- Move to wishlist
- Stock availability warnings
- Proceed to checkout button
- Empty cart state

**Computed:** `cart`, `availableItems`, `unavailableItems`
**Actions:**
- `updateQuantity($itemId, $quantity)` - Updates item quantity
- `removeItem($itemId)` - Removes from cart
- `moveToWishlist($itemId)` - Moves item to wishlist
- `proceedToCheckout()` - Redirects to checkout

---

## Wishlist Page Component
**Location:** `resources/views/livewire/wishlist/show.blade.php`

**Purpose:** Display and manage wishlist

**Features:**
- Grid layout of wishlist items
- Product details
- Stock status
- Add to cart button
- Remove from wishlist
- Empty wishlist state

**Computed:** `wishlist`, `items`
**Actions:**
- `removeItem($itemId)` - Removes from wishlist
- `moveToCart($itemId)` - Moves to cart with stock validation

---

## Checkout Component
**Location:** `resources/views/livewire/checkout/process.blade.php`

**Purpose:** Process order checkout

**Features:**
- Order summary
- Item list with images
- Price breakdown (subtotal, VAT, total)
- Stock validation
- Partial checkout support
- Confirm order button
- Loading states
- Success/error handling

**Computed:** `cart`, `availableItems`, `unavailableItems`, `subtotal`, `vatAmount`, `total`
**Actions:**
- `confirmOrder()` - Calls CheckoutService, creates order, redirects to success page
- Handles InsufficientStockException
- Handles EmptyCartException

---

## UI Components (Flux UI)

### Available Flux Components
- `flux:button` - Buttons with variants
- `flux:badge` - Status badges
- `flux:card` - Product cards
- `flux:input` - Form inputs
- `flux:modal` - Modals for confirmations
- `flux:dropdown` - Dropdowns for cart preview

### Styling
- TailwindCSS for all custom styling
- Responsive design (mobile-first)
- Consistent spacing and typography
- Loading states with spinners
- Toast notifications for feedback

---

## Event System

**Dispatched Events:**
- `cart-updated` - When cart changes
- `wishlist-updated` - When wishlist changes
- `order-created` - After successful checkout

**Listened Events:**
- Components listen to relevant events to refresh data
- Real-time UI updates without page refresh

---

## Next Steps

Proceed to [05-JOBS-SCHEDULING.md](05-JOBS-SCHEDULING.md)
