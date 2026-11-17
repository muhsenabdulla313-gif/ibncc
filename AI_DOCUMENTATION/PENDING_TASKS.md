# Pending Tasks — Trading Module

The following tasks are required to bring the Trading module to 100% completion:

---

## High Priority (Core E-Commerce Gaps)

### 1. Buyer Shopping Cart Implementation
- [ ] Create `CartController` with actions:
  - `index`: View current cart items for authenticated user (and guest session support).
  - `store`: Add product/variant to cart with quantity validation (respecting retail MOQ = 1, wholesale MOQ = `min_order_qty`).
  - `update`: Update quantity of existing cart item.
  - `destroy`: Remove item from cart.
  - `count`: Return active cart item count for header badge.
- [ ] Convert `public/cart.html` and `public/trading_cart.html` to Blade:
  - `resources/views/cart/index.blade.php` (or `resources/views/trading/cart.blade.php`).
- [x] Wire up "Add to Cart" and "Buy Now" client hooks in `resources/views/catalog/details.blade.php` (`window.pdSelection()` ready for CartController).
- [ ] Wire up cart button on product cards in `resources/views/catalog/listing.blade.php`.
- [ ] Update header cart badge across layouts to reflect live database count.

### 2. Buyer Checkout & Order Placement
- [ ] Create `CheckoutController` / `OrderController`:
  - Checkout form: shipping address selection / creation, contact phone/email confirmation, buyer note.
  - Order creation transaction:
    - Validate variant stock against cart quantity.
    - Reserve stock (`reserved_stock` increment).
    - Create parent `orders` record.
    - Snapshot and partition items into `order_items` with appropriate `seller_id`.
    - Clear ordered items from `cart_items`.
    - Log order placement in `stock_movements`.
- [ ] Create Checkout view (`resources/views/trading/checkout.blade.php` using design from `public/payment.html`).
- [ ] Order confirmation / success view (`resources/views/trading/order_success.blade.php`).

### 3. Buyer Order History & Tracking
- [ ] Add buyer orders view (`resources/views/trading/orders.blade.php` and `resources/views/trading/order_details.blade.php` using design from `public/order-details.html`).
- [ ] Allow buyers to view item fulfillment status (`placed`, `contacted`, `confirmed`, `shipped`, `delivered`, `cancelled`).
- [ ] Display seller contact details so buyer can connect with seller directly.

---

## Medium Priority (Experience & Discovery)

### 4. Dynamic Trading Homepage (`/trading`)
- [ ] Replace static HTML placeholder cards in `resources/views/trading.blade.php`:
  - Dynamically load top Trading categories with product counts.
  - Dynamically display featured wholesale deals / commodity offers (`is_featured = 1`).
  - Dynamically display trending retail marketplace items.
  - Ensure all category and card links point to active route URLs rather than hash anchors (`#grains`, etc.).

### 5. Buyer Wishlist Functionality
- [ ] Create `WishlistController` with toggle action (`/wishlist/toggle/{product}`).
- [ ] Convert `public/wish-list.html` and `public/trading_wishlist.html` to `resources/views/trading/wishlist.blade.php`.
- [ ] Connect heart icons across product listing and product details to wishlist endpoint.

### 6. Product Ratings & Reviews
- [ ] Add review submission endpoint (`POST /products/{product}/review`) verifying buyer purchase.
- [ ] Display approved reviews and average star rating on `catalog/details.blade.php`.

---

## Low Priority / Administrative

### 7. Admin Product Moderation UI
- [ ] Create admin interface for reviewing pending products (`admin.products.index`).
- [ ] Implement Approve / Reject actions with rejection reason notes.

### 8. Bug Fixes & Code Cleanup
- [ ] Fix `@extends('admin.layouts.app')` in `resources/views/admin/catalog/index.blade.php` to `@extends('layouts.admin')`.
- [x] Clean up duplicate route registrations in `routes/web.php`.
- [ ] Add missing Trading inverse relationships on `App\Models\User` (`products()`, `cartItems()`, `orders()`, `orderItems()`, `wishlists()`).
- [ ] Fix typo in route URI `/trading/my_accout_details`.
