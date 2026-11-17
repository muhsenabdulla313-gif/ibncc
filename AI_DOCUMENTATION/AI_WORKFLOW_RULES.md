# AI Workflow & Development Rules

## 1. Project Context & Non-Negotiable Boundaries
- **Project Name:** IBNCC (International Business Network of Catholic Congress).
- **Core Modules:**
  1. **Networking Module:** Completed and production-tested. **STRICT RULE: Reference-only. Never modify its code, database migrations, routes, views, assets, or functionality.**
  2. **Trading Module:** Current active development priority. Focus on completing and refining B2C Retail Marketplace and B2B Wholesale Trading.
  3. **Admin Module:** Catalog master data and general administration.

## 2. Safety & Change Management Rules
1. **Never Discard Uncommitted Work:**
   - Always run `git status` before touching files.
   - Protect any existing unstaged changes created by other developers.
2. **No Unrequested Redesigns:**
   - Preserve existing Trading design, CSS classes, and HTML template structures provided in `public/*.html`.
   - Match existing styling (`public/css/cchub/*.css`).
3. **Targeted Edits Only:**
   - Modify only files required for the approved task.
   - Do not perform global formatting or refactoring outside scope.
4. **Shared Hosting / cPanel Compatibility:**
   - File uploads must go directly to `public/uploads/` via `ProductImageService` or standard public directory management.
   - Use GD for image resizing (via `ImageResizer`), avoiding dependencies on non-standard binary extensions or `php artisan storage:link`.
5. **Database Integrity:**
   - Never run destructive migrations (`migrate:fresh`, `migrate:reset`, `db:wipe`) on working databases.
   - Additive migrations only. Respect existing column constraints and virtual columns (e.g. `variant_key`).

## 3. Architecture & Coding Conventions
- **Framework:** Laravel 12.x / PHP 8.2+.
- **Database:** MySQL 8.4+.
- **Authentication:** Dual-guard setup (`auth` for web users/members, `auth:admin` for admin panel). Role middleware (`role:admin`, `role:member`).
- **Trading Separation:**
  - `sale_type` enum values: `'retail'`, `'wholesale'`, `'both'`.
  - Mode parameter `'retail'` vs `'wholesale'` (or `'marketplace'` vs `'trading'`).
  - Retail pricing is variant-based (`product_variants.price`).
  - Wholesale pricing is tier-based (`product_price_tiers` linked to MOQ).
- **Orders & Multi-Seller Architecture:**
  - An `Order` belongs to a buyer (`buyer_id`).
  - Each `OrderItem` is attributed to its respective seller (`seller_id`), allowing multi-seller carts and partitioned fulfillment.

## 4. Documentation Protocol
- Maintain and update the `AI_DOCUMENTATION/` directory whenever completing a task or discovering a bug.
- Document known issues in `BUG_TRACKER.md` immediately upon discovery.
- Keep `CURRENT_STATUS.md` and `DEVELOPMENT_LOG.md` synchronized after every work turn.
