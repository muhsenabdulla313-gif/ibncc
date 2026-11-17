# Bug Tracker

This document records all detected bugs, discrepancies, and breaking issues identified during the project inspection.

---

### BUG-001: Missing Admin Layout Reference
- **Severity:** High
- **File:** `resources/views/admin/catalog/index.blade.php:L1`
- **Description:** Line 1 uses `@extends('admin.layouts.app')` which does not exist in the project (`resources/views/admin/layouts/` directory is absent). The correct admin master layout is `layouts.admin` with `@section('body')`.
- **Impact:** Visiting `/admin/catalog` throws an `InvalidArgumentException: View [admin.layouts.app] not found`.
- **Recommended Fix:** Change line 1 to `@extends('layouts.admin')` and replace `@section('content')` with `@section('body')`.

---

### BUG-002: Duplicate Route Definitions in `routes/web.php`
- **Severity:** Medium
- **Status:** Resolved / Fixed
- **File:** `routes/web.php:L255-L272`
- **Description:** Routes `/products` and `/trading/products` were registered twice:
  - Lines 255-256: `Route::get('/products', [ProductListingController::class, 'retail'])->name('products.index');`
  - Lines 258-259: `Route::get('/trading/products', [ProductListingController::class, 'trading'])->name('trading.products.index');`
  - Lines 261-262: Duplicate registration of `/products`.
  - Lines 267-268: Duplicate registration of `/trading/products`.
- **Resolution:** Removed redundant route blocks from `routes/web.php`. Verified via route resolution tests.

---

### BUG-003: Unmigrated Fillable Columns on `Product` Model
- **Severity:** Low
- **File:** `app/Models/Product.php:L20-L22`
- **Description:** Model `$fillable` array lists: `'qty_step', 'min_price', 'max_price', 'in_stock', 'specs_line', 'badge', 'discount_label'`, with a comment `// listing columns (added by 2026_11_03_000012 migration)`. However, no migration `2026_11_03_000012` exists, and these columns do not exist in the `products` MySQL table.
- **Impact:** `SellerProductService::syncListing` defensively checks `Schema::hasColumn('products', 'min_price')`, preventing SQL crashes, but model fillables are out of sync with the physical database schema.
- **Recommended Fix:** Either create the additive migration to add these performance denormalization columns, or clean up the `$fillable` and `$casts` definitions to match current schema.

---

### BUG-004: Hardcoded Retail Route in Dynamic Navbar
- **Severity:** Medium
- **File:** `resources/views/trading/navbar.blade.php:L10, L43, L58, L78`
- **Description:** Category links unconditionally link to `url('/products')?cat=...`. When a buyer is browsing the Wholesale Trading section, clicking any category link in the navbar bounces them out to the Retail Marketplace (`/products`) instead of retaining wholesale mode (`/trading/products`).
- **Impact:** Breaks user context when navigating categories in wholesale mode.
- **Recommended Fix:** Dynamically generate category URLs based on the current active route mode (retail vs wholesale).

---

### BUG-005: Static Action and Mock Links in `layouts/trading.blade.php`
- **Severity:** Medium
- **File:** `resources/views/layouts/trading.blade.php:L30, L38, L75, L76`
- **Description:**
  - Header brand link points to `index.html#home` instead of `url('/')`.
  - Search form action is `#` instead of submitting `q` to the active products route.
  - Wishlist icon links to `wish-list.html`.
  - Cart icon links to `cart.html`.
- **Impact:** Navbar navigation leads to static HTML files or dead ends.
- **Recommended Fix:** Replace with Laravel `route()` and `url()` helpers.

---

### BUG-006: Route URI Typo for My Account
- **Severity:** Low
- **File:** `routes/web.php:L223`
- **Description:** Route is defined as `/trading/my_accout_details` (missing the 'n' in 'account'), and named `trading.myaccount`.
- **Impact:** URL typo inconsistency.
- **Recommended Fix:** Update route URI to `/trading/my_account_details` or standard `/trading/my-account`.

---

### BUG-007: Missing Inverse Relationships on `User` Model
- **Severity:** Medium
- **File:** `app/Models/User.php`
- **Description:** While `Product`, `Order`, `OrderItem`, and `CartItem` have `belongsTo(User::class)`, `App\Models\User` does not declare `products()`, `cartItems()`, `orders()`, `orderItems()`, or `wishlists()`.
- **Impact:** Calling `$user->products` or `$user->cartItems` fails.
- **Recommended Fix:** Add the standard Eloquent relationship methods to `User.php`.

---

### BUG-008: Fatal `dd()` in `catalog/details.blade.php`
- **Severity:** High
- **Status:** Resolved / Fixed
- **File:** `resources/views/catalog/details.blade.php:L158-L170`
- **Description:** A debug `dd($colorVariants, $sizeVariants, $defaultVariant);` call was committed inside the template, halting view compilation and rendering for all visitors.
- **Resolution:** Removed the debug block. View now compiles and renders properly.

---

### BUG-009: Broken Image URLs in Product Details
- **Severity:** Medium
- **Status:** Resolved / Fixed
- **File:** `resources/views/catalog/details.blade.php`
- **Description:** Gallery rendering assumed images were located under `storage/` and called `Storage::url(...)`, generating `/storage/uploads/products/...` paths which failed because images are stored directly in `public/uploads/products/`.
- **Resolution:** Implemented direct `public_path()` existence checking with fallback to `public/assets/images/no-image.avif` and runtime HTML `onerror` attributes.

---

### BUG-010: Attribute Value Pivot Column Error
- **Severity:** Low
- **Status:** Resolved / Fixed
- **File:** `resources/views/catalog/details.blade.php:L545`
- **Description:** The specifications table attempted to read `$row->pivot->value`. The `product_attribute_values` table has typed columns (`value_text`, `value_number`, `value_bool`, `value_date`) and no raw `value` column, causing specifications to display blank.
- **Resolution:** Updated template to use `$row->display_value`, which correctly resolves typed values or linked `attribute_options`.
