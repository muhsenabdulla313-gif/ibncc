# Project Overview — IBNCC

## 1. Executive Summary
**IBNCC** (International Business Network of Catholic Congress) is an enterprise web application designed to connect Christian entrepreneurs, professionals, and community members globally. The platform combines professional networking and directory services with an integrated dual-mode commercial trading system (Retail B2C Marketplace and Wholesale B2B Commodity Trading).

## 2. Technology Stack
- **Framework:** Laravel 12.69.2
- **Language / Runtime:** PHP 8.2.12
- **Database Engine:** MySQL 8.4.3 (`ibncc` database, 51 active tables)
- **Key Installed Packages:**
  - `laravel/framework`: `^12.0`
  - `livewire/livewire`: `^3.6.4`
  - `livewire/volt`: `^1.7.0`
  - `laravel/breeze`: `^2.4` (Auth scaffolding)
- **Frontend Assets:**
  - CSS: Custom CC Hub design system (`public/css/cchub/*.css`), `style.css` (Networking), Bootstrap 5 (Admin dashboard KaiAdmin theme)
  - Fonts: Cormorant Garamond, DM Sans, Manrope, Outfit, Caveat
  - Icons: Font Awesome 6.5.2 Pro / Free CDN
  - JavaScript: Vanilla JS interaction layers (`public/js/cchub/*.js`), jQuery (Admin tables)

## 3. High-Level Module Division
| Module | Responsibility | Status | Modification Policy |
| :--- | :--- | :--- | :--- |
| **Networking** | Community directory, parish/diocese hierarchy, user profiles, member search, social connections, messaging, events. | **Completed** | **STRICTLY LOCKED (Reference-Only). Never modify.** |
| **Trading** | Dual-channel e-commerce (Retail Marketplace & Wholesale Trading), catalog, product details, seller hub, cart, checkout, orders. | **In Progress** | **ACTIVE DEVELOPMENT PRIORITY** |
| **Admin Panel** | Admin authentication, banner management, header notices, catalog master data (categories, attributes, brands, units), user management. | **Partially Completed** | Minor fixes & maintenance |
| **Authentication** | Phone number / Password login, OTP verification via SMS/Guest, role-based access control. | **Completed** | Maintained as-is |

## 4. Key Trading Personas
1. **Shopper / Buyer:**
   - Browses retail marketplace or wholesale bulk commodities.
   - Filters products by category, brand, price, MOQ, and dynamic specifications.
   - Configures variants (size, color with real-time gallery switching).
   - Adds to cart, proceeds to checkout, monitors order fulfillment status.
2. **Seller (Member Role):**
   - Accesses Seller Hub (`/seller`).
   - Switches between Marketplace (retail) and Trading (wholesale) modes.
   - Manages product lifecycle (Draft, Pending Review, Active, Inactive).
   - Generates multi-axis variants, uploads photos per color/option, configures stock alerts.
   - Sets retail pricing (MRP + Sale Price + Tax) or wholesale tiered pricing (MOQ + Tiers).
   - Manages and fulfills orders assigned to their seller ID.
3. **Admin:**
   - Manages global catalog hierarchy (Categories up to depth 2, Attribute Groups, Attributes, Attribute Options, Units, Brands).
   - Moderates submitted products.
