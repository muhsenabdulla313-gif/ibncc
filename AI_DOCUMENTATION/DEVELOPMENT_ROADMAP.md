# Development Roadmap — Trading Module

This roadmap outlines the recommended phased sequence for completing the Trading module safely and systematically, without altering the Networking module.

---

```
Phase 1: Foundation Fixes & Route Cleanup
    │
    ▼
Phase 2: Buyer Shopping Cart System
    │
    ▼
Phase 3: Buyer Checkout & Multi-Seller Order Placement
    │
    ▼
Phase 4: Buyer Order History & Tracking
    │
    ▼
Phase 5: Trading Homepage Dynamization
    │
    ▼
Phase 6: Wishlist & Product Reviews
    │
    ▼
Phase 7: Admin Product Approval & Moderation
```

---

## Phase 1: Foundation Fixes & Route Cleanup
- **Goal:** Fix non-working views, remove route redundancies, and prepare models for commerce operations.
- **Tasks:**
  1. Fix `resources/views/admin/catalog/index.blade.php`: Update layout extension from `@extends('admin.layouts.app')` to `@extends('layouts.admin')`.
  2. Clean up duplicated route registrations in `routes/web.php` for `/products` and `/trading/products`.
  3. Add inverse Eloquent relationships to `App\Models\User`:
     - `products()` -> `hasMany(Product::class, 'user_id')`
     - `cartItems()` -> `hasMany(CartItem::class, 'user_id')`
     - `orders()` -> `hasMany(Order::class, 'buyer_id')`
     - `orderItems()` -> `hasMany(OrderItem::class, 'seller_id')`
     - `wishlists()` -> `hasMany(Wishlist::class, 'user_id')`
  4. Fix layout links in `resources/views/layouts/trading.blade.php` to use Laravel routes instead of static `.html` files.

---

## Phase 2: Buyer Shopping Cart System
- **Goal:** Allow authenticated (and guest) users to add retail and wholesale products to a persistent shopping cart with proper validation.
- **Tasks:**
  1. Implement `CartController` with actions:
     - `index`: Calculate line prices (variant retail price or wholesale tier price based on quantity), subtotal, GST/tax, and grand total.
     - `store`: Validate product availability, variant status, stock level, and MOQ constraints.
     - `update`: Handle increment/decrement with stock bounds checking.
     - `destroy`: Remove line item.
     - `count`: Badge update endpoint.
  2. Convert `public/cart.html` and `public/trading_cart.html` into unified Blade template `resources/views/trading/cart.blade.php`.
  3. Wire the "Add to Cart" and "Buy Now" buttons in `resources/views/catalog/details.blade.php` to the cart API.
  4. Update cart badges in all layouts.

---

## Phase 3: Buyer Checkout & Multi-Seller Order Placement
- **Goal:** Convert cart items into an official `Order` and partitioned `OrderItem` records.
- **Tasks:**
  1. Implement `CheckoutController`:
     - Display selected delivery address (from `addresses` table) or allow new address input.
     - Collect buyer contact details (`buyer_name`, `buyer_phone`, `buyer_email`, `buyer_note`).
     - Display order breakdown grouped by seller.
  2. Order Placement Transaction:
     - Atomically create record in `orders`.
     - Create records in `order_items` partitioned by `seller_id`.
     - Increment `product_variants.reserved_stock`.
     - Log stock reservation in `stock_movements`.
     - Clear purchased items from `cart_items`.
  3. Order confirmation view (`resources/views/trading/order_success.blade.php`).

---

## Phase 4: Buyer Order History & Tracking
- **Goal:** Provide buyers with an order management interface.
- **Tasks:**
  1. Buyer orders list (`/trading/orders` -> `resources/views/trading/orders.blade.php`).
  2. Buyer order details (`/trading/orders/{order}` -> `resources/views/trading/order_details.blade.php` based on `public/order-details.html`).
  3. Display seller contact information for each line item to facilitate direct buyer-to-seller communication.

---

## Phase 5: Trading Homepage Dynamization
- **Goal:** Transform `/trading` from static HTML placeholders to a dynamic community commerce portal.
- **Tasks:**
  1. Update `TradingController@index` to query active trade categories with item counts, featured wholesale commodities, and trending retail products.
  2. Replace static placeholder cards in `resources/views/trading.blade.php` with dynamic Blade loops linked to real catalog listings.

---

## Phase 6: Buyer Wishlist & Reviews
- **Goal:** Enhance buyer retention and trust.
- **Tasks:**
  1. Implement `WishlistController` (toggle endpoint + `resources/views/trading/wishlist.blade.php`).
  2. Implement `ReviewController` for verified purchases with moderation status.

---

## Phase 7: Admin Product Approval & Moderation
- **Goal:** Give administrators oversight of products submitted by community sellers.
- **Tasks:**
  1. Admin pending product review queue (`/admin/products`).
  2. Actions to approve (publish to store) or reject with feedback reason (`rejection_reason`).
