# Database Structure & Schema Reference

The database `ibncc` contains **51 tables** managed across **25 migrations**. All migrations have been executed.

---

## 1. Core & User Tables
| Table | Description | Primary Key | Key Foreign Keys |
| :--- | :--- | :--- | :--- |
| `users` | User accounts (admin, members, guests). | `id` (bigint) | `role_id` -> `roles.id`<br>`parish_id` -> `parishes.id` |
| `roles` | Access roles (`admin`, `member`, `guest`). | `id` (bigint) | None |
| `addresses` | User addresses (permanent, present). | `id` (bigint) | `user_id` -> `users.id`<br>`country_id`, `state_id`, `district_id` |
| `profiles` | User profile details, occupation, etc. | `id` (bigint) | `user_id` -> `users.id`<br>`job_id` -> `jobs.id` |

---

## 2. Trading Catalog & Taxonomy Tables
| Table | Description | Columns & Constraints |
| :--- | :--- | :--- |
| `categories` | 3-level hierarchical category tree (385 rows). | `id`, `parent_id` (FK -> categories), `name`, `slug` (unique), `path` (indexed, e.g. "1/5/12/"), `depth` (0=root, 1=group, 2=item), `description`, `image`, `meta_title`, `meta_description`, `sort_order`, `is_active`. |
| `brands` | Product brands (28 rows). | `id`, `name` (unique), `slug` (unique), `logo`, `is_active`. |
| `units` | Measurement units with conversion factors (11 rows). | `id`, `name` (unique), `short_name`, `type` (`count`, `weight`, `length`, `area`, `volume`, `pack`), `base_unit_id` (FK -> units), `conversion_factor`, `allow_decimal`. |
| `attribute_groups` | Grouping for attributes (4 rows). | `id`, `name`, `slug` (unique), `sort_order`. |
| `attributes` | Specification and variant axis definitions (14 rows). | `id`, `attribute_group_id` (FK -> attribute_groups), `unit_id` (FK -> units), `name`, `slug` (unique), `input_type` (`text`, `textarea`, `number`, `select`, `multiselect`, `boolean`, `color`, `date`), `is_filterable`, `is_searchable`, `is_comparable`, `is_variant_axis`, `is_visible`, `sort_order`. |
| `attribute_options` | Selectable values for attributes (33 rows). | `id`, `attribute_id` (FK -> attributes), `label`, `value`, `color_hex` (for color swatches), `image`, `sort_order`. Unique: `[attribute_id, value]`. |
| `category_attributes` | Attribute inheritance mappings per category (3,232 rows). | `id`, `category_id` (FK -> categories), `attribute_id` (FK -> attributes), `is_required`, `is_filterable`, `is_variant_axis`, `applies_to` (`retail`, `wholesale`, `both`), `sort_order`. Unique: `[category_id, attribute_id]`. |

---

## 3. Product & Inventory Tables
| Table | Description | Columns & Relationships |
| :--- | :--- | :--- |
| `products` | Core product listing. | `id`, `uuid` (unique), `user_id` (FK -> users, the seller), `category_id` (FK -> categories), `brand_id` (FK -> brands), `unit_id` (FK -> units), `stock_unit_id` (FK -> units), `title`, `slug` (unique), `model_no`, `short_description`, `description` (longText), `sale_type` (`retail`, `wholesale`, `both`), `pack_qty`, `min_order_qty`, `max_order_qty`, `track_stock`, `hsn_code`, `tax_rate`, `price_includes_tax`, `offer_text`, `is_featured`, `is_assured`, `rating_avg`, `rating_count`, `status` (`draft`, `pending`, `active`, `rejected`, `inactive`), `rejection_reason`, `published_at`, `meta_title`, `meta_description`, `deleted_at`. |
| `product_variants` | Product SKU variations. | `id`, `product_id` (FK -> products), `sku` (unique), `barcode`, `options` (JSON display cache), `mrp`, `price` (base retail), `cost_price`, `stock`, `reserved_stock`, `low_stock_alert`, `weight`, `length`, `width`, `height`, `is_default`, `is_active`. |
| `product_price_tiers` | Quantity-based wholesale pricing. | `id`, `product_id` (FK -> products), `variant_id` (nullable FK -> product_variants), `price_type` (`retail`, `wholesale`), `min_qty`, `max_qty`, `price`, `variant_key` (Virtual Generated: `IFNULL(variant_id, 0)`). Unique: `[product_id, variant_key, price_type, min_qty]`. |
| `product_images` | Gallery images. | `id`, `product_id` (FK -> products), `variant_id` (nullable FK -> product_variants), `attribute_option_id` (nullable FK -> attribute_options for color gallery sync), `path`, `alt`, `sort_order`, `is_primary`. |
| `product_attribute_values` | Non-variant product specifications. | `id`, `product_id` (FK -> products), `attribute_id` (FK -> attributes), `attribute_option_id` (FK -> attribute_options), `value_text`, `value_number`, `value_bool`, `value_date`. |
| `variant_attribute_values` | Axis choices for each variant. | `id`, `variant_id` (FK -> product_variants), `attribute_id` (FK -> attributes), `attribute_option_id` (FK -> attribute_options). Unique: `[variant_id, attribute_id]`. |
| `product_videos` | Embedded / uploaded videos. | `id`, `product_id` (FK -> products), `url`, `provider` (`youtube`, `vimeo`, `upload`), `sort_order`. |
| `product_documents` | Spec sheets, manuals, certificates. | `id`, `product_id` (FK -> products), `title`, `path`, `sort_order`. |
| `stock_movements` | Stock transaction audit log. | `id`, `variant_id` (FK -> product_variants), `user_id` (FK -> users), `type` (`initial`, `adjustment`, `sale`, `cancel`, `return`), `qty_change`, `qty_after`, `note`, `reference_type`, `reference_id` (morphs). |
| `tags`, `product_tag` | Product tags and pivot. | `tags`: `id`, `name`, `slug`. `product_tag`: `[product_id, tag_id]`. |

---

## 4. Commerce & Order Tables
| Table | Description | Columns & Relationships |
| :--- | :--- | :--- |
| `cart_items` | Active shopping cart items. | `id`, `user_id` (FK -> users), `product_id` (FK -> products), `variant_id` (FK -> product_variants), `qty` (decimal:3). Unique: `[user_id, variant_id]`. |
| `orders` | Buyer master orders. | `id`, `order_no` (unique), `buyer_id` (FK -> users), `buyer_name`, `buyer_phone`, `buyer_email`, `shipping_address` (JSON snapshot), `buyer_note`, `tax_total`, `total`, `status` (`placed`, `processing`, `completed`, `cancelled`). |
| `order_items` | Multi-seller partitioned order line items. | `id`, `order_id` (FK -> orders), `product_id` (FK -> products), `variant_id` (FK -> product_variants), `seller_id` (FK -> users), `seller_name`, `seller_phone`, `title`, `sku`, `options` (JSON snapshot), `unit_name`, `hsn_code`, `price_type` (`retail`, `wholesale`), `qty`, `unit_price`, `tax_rate`, `tax_amount`, `line_total`, `status` (`placed`, `contacted`, `confirmed`, `shipped`, `delivered`, `cancelled`), `seller_note`, `contacted_at`. |
| `wishlists` | Buyer saved products. | `id`, `user_id` (FK -> users), `product_id` (FK -> products). Unique: `[user_id, product_id]`. |
| `product_reviews` | Product ratings and reviews. | `id`, `product_id` (FK -> products), `user_id` (FK -> users), `rating` (tinyint), `title`, `comment`, `is_verified_purchase`, `status` (`pending`, `approved`, `rejected`). Unique: `[product_id, user_id]`. |

---

## 5. Seeders & Master Data
- `RoleSeeder`: Creates standard roles (`admin`, `member`, `guest`).
- `MarketplaceCatalogSeeder`: Seeds all 385 categories, units, brands, attribute groups, and category attributes.
- `MarketplaceDemoSeeder`: Seeds demo products, variants, price tiers, and stock movements.
- `LookupSeeder`, `WorldGeoSeeder`, `KeralachurchSeeder`: Seeds geographical and ecclesiastical directory data for the Networking module.
