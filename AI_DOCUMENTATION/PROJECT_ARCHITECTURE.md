# Project Architecture

## 1. Directory Structure Highlights
```text
ibncc_project/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                  # Admin controllers (Catalog, Banners, Users, Events, etc.)
│   │   │   ├── Seller/                 # Seller controllers (SellerProduct, SellerCatalog, SellerOrder, etc.)
│   │   │   ├── ProductListingController.php # Catalog listing and product details (Retail & Trading)
│   │   │   ├── TradingController.php   # Trading homepage controller
│   │   │   ├── NetworkingController.php # LOCKED (Reference-only)
│   │   │   └── RegisterController.php  # Public & auth controller
│   ├── Models/                         # Eloquent Models (Product, Category, Order, CartItem, etc.)
│   └── Services/                       # Domain logic services
│       ├── ImageResizer.php            # GD-based image resizing (1400px limit, cPanel compatible)
│       ├── ProductImageService.php     # Public uploads management for product photos
│       └── SellerProductService.php    # Attribute inheritance, payload builder, stock audit, validation
├── database/
│   ├── migrations/                     # 25 executed migrations
│   └── seeders/                        # Seeders for roles, catalog, and demo data
├── public/
│   ├── css/cchub/                      # Trading & catalog styles
│   ├── js/cchub/                       # Frontend interactivity scripts
│   ├── uploads/                        # Public asset uploads (products, banners, categories)
│   └── *.html                          # Original HTML UI reference mockups
├── resources/
│   └── views/
│       ├── admin/                      # Admin views
│       ├── catalog/                    # listing.blade.php & details.blade.php
│       ├── components/                 # Blade components (auth modals, UI helpers)
│       ├── layouts/                    # master, network, trading, admin layouts
│       ├── seller/                     # index.blade.php (Seller Hub) & hub.blade.php
│       └── trading/                    # navbar.blade.php, my_account.blade.php
└── routes/
    ├── auth.php                        # Breeze/Volt auth routes
    └── web.php                         # All web application routes
```

## 2. Authentication & Authorization Flow
- **Guards:**
  - `web`: Uses `App\Models\User` with session driver.
  - `admin`: Uses dedicated admin authentication guard configured in `config/auth.php`.
- **Roles:**
  - `roles` table: ID 1 = `admin`, ID 2 = `member`, ID 3 = `guest`.
  - Member check helper: `$user->isMember()`, `$user->isAdmin()`.
  - Middleware: `role:admin`, `role:member`.
  - Seller Hub is gated under `Route::middleware('role:member')`.

## 3. Trading Backend Architecture

### 3.1 Dual-Channel Logic
Trading supports two operating modes across the application:
1. **Retail Mode (`retail` / `marketplace`):**
   - Products with `sale_type` in `['retail', 'both']`.
   - Pricing driven by `product_variants.price` and `product_variants.mrp`.
   - Minimum order quantity defaults to 1.
   - Route: `/products` and `/products/{slug}`.
2. **Wholesale Mode (`wholesale` / `trading`):**
   - Products with `sale_type` in `['wholesale', 'both']`.
   - Pricing driven by `product_price_tiers` (volume-based pricing table).
   - Minimum order quantity enforced by `products.min_order_qty`.
   - Route: `/trading/products` and `/trading/products/{slug}`.

### 3.2 Dynamic Attribute & Variant System
- **Inheritance Chain:** Category attributes inherit from parent categories via materialized path or recursive parent lookup (`SellerProductService::attributesFor`).
- **Axis vs Specification:**
  - `is_variant_axis = true`: Generates selectable variant permutations (e.g. Size, Color) saved in `variant_attribute_values`.
  - `is_variant_axis = false`: Saved in `product_attribute_values` as typed specifications (text, number, boolean, date, select, multiselect).
- **Variant Options:** Cached as JSON in `product_variants.options` (e.g. `{"color":"Blue","size":"M"}`) for rapid UI display without extra joins.
- **Color Swatches & Image Sync:** Product images can link to specific `attribute_option_id` (via `product_images.attribute_option_id`), enabling automatic gallery switching when buyers select a color swatch.

### 3.3 Inventory & Stock Audit
- Inventory is tracked at variant level (`product_variants.stock`, `reserved_stock`).
- Available stock is calculated as `max(0, stock - reserved_stock)`.
- Stock modifications are logged in `stock_movements` with `type` (`initial`, `adjustment`, `sale`, `cancel`, `return`), `qty_change`, and `qty_after`.

### 3.4 Multi-Seller Order Architecture
- An order placed by a buyer (`orders.buyer_id`) creates an overall `Order` record with `shipping_address` (JSON) and totals.
- Individual order lines are stored in `order_items`, with each line linked to its specific seller (`order_items.seller_id`).
- This allows multi-seller orders where each seller can view and fulfill their own order lines independently via `SellerOrderController`.
- Status flow per item: `placed` -> `contacted` -> `confirmed` -> `shipped` -> `delivered` (or `cancelled`).
- Order item status transitions automatically update the parent `Order.status` via `SellerOrderController::syncOrderStatus`.

## 4. Trading Frontend Architecture
- **Layout:** `resources/views/layouts/trading.blade.php` wraps the trading experience.
- **Navigation:** `resources/views/trading/navbar.blade.php` renders the dynamic 3-level mega-menu (Category -> Subcategory -> Item).
- **Product Listing:** `resources/views/catalog/listing.blade.php` dynamically adapts between retail and wholesale presentation.
- **Product Details:** `resources/views/catalog/details.blade.php` handles interactive variant selection, color gallery synchronization, quantity incrementors, and wholesale tier tables.
- **Seller Hub:** `resources/views/seller/index.blade.php` is a single-page interactive workspace powered by `public/js/cchub/seller_page.js` communicating with `SellerProductController` via AJAX JSON APIs.
