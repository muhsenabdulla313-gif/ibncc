<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Seller Hub — CC Hub</title>
    <meta name="description" content="Manage products, pricing, stock and orders on CC Hub Seller Page." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/seller_page.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/seller_dynamic.css') }}" />
  </head>
  <body class="seller-page" data-seller-mode="{{ $mode }}">
    <header class="site-header">
      <div class="header-inner">
        <a href="{{ url('/') }}#home" class="brand" aria-label="IBNCC Home">
          <span class="brand-text">
            <img src="{{ asset('assets/images/logo_full.png') }}" alt="IBNCC logo" class="brand-logo" width="52" height="52" />
            <span class="brand-tagline"><span class="brand-tagline-line">AN INITIATIVE OF</span><span class="brand-tagline-line">CATHOLIC CONGRESS</span></span>
          </span>
        </a>

        <form class="search-bar" role="search" action="#" method="get">
          <input type="search" name="q" placeholder="Search for products, brands and more..." aria-label="Search" />
          <button type="submit" class="search-btn" aria-label="Submit search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="header-actions">
          @auth
            <a href="{{ url('/profile') }}" class="btn-login" title="My profile">{{ strtoupper(mb_substr(auth()->user()->first_name, 0, 1)) }}</a>
          @else
            <a href="#login" class="btn-login">Login</a>
          @endauth
          <a href="{{ url('/wishlist') }}" class="icon-btn" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></a>
          <a href="{{ url('/cart') }}" class="icon-btn cart-btn" aria-label="Shopping cart">
            <i class="fa-solid fa-cart-shopping"></i><span class="cart-badge">0</span>
          </a>
        </div>
      </div>
    </header>

    <main class="seller-main">
      <!-- Section 1: banner -->
      <section class="seller-banner" aria-labelledby="seller-banner-title">
        <div class="seller-banner-media" aria-hidden="true"></div>
        <a href="{{ url('/trading') }}" class="seller-banner-back" aria-label="Back to Trading page" title="Back to Trading">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        </a>
        <div class="seller-banner-inner">
          <div class="seller-banner-copy-block">
            <p class="seller-banner-eyebrow"><i class="fa-solid fa-store" aria-hidden="true"></i> Seller Hub</p>
            <h1 id="seller-banner-title">Turn your craft into <span class="seller-banner-accent">community commerce</span></h1>
            <p class="seller-banner-copy">List products for marketplace shoppers or wholesale trading buyers. Set prices, stock, and fulfill orders from one calm workspace.</p>
            <a href="#seller-workspace" class="seller-banner-cta">Start listing <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
          </div>
        </div>
      </section>

      <!-- Section 2: channel tabs -->
      <section class="seller-mode-section" aria-label="Selling mode">
        <p class="seller-mode-label">Selling channel</p>
        <div class="seller-mode-tabs" role="tablist" aria-label="Marketplace or Trading">
          <button type="button" class="seller-mode-tab {{ $mode === 'marketplace' ? 'is-active' : '' }}" role="tab" id="modeMarketplace" data-mode="marketplace" aria-selected="{{ $mode === 'marketplace' ? 'true' : 'false' }}">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Marketplace
          </button>
          <button type="button" class="seller-mode-tab {{ $mode === 'trading' ? 'is-active' : '' }}" role="tab" id="modeTrading" data-mode="trading" aria-selected="{{ $mode === 'trading' ? 'true' : 'false' }}">
            <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i> Trading
          </button>
        </div>
        <p class="seller-mode-hint" data-mode-hint="marketplace" {{ $mode === 'marketplace' ? '' : 'hidden' }}>Marketplace focuses on single-unit retail pricing, instant checkout, and consumer-ready listings.</p>
        <p class="seller-mode-hint" data-mode-hint="trading" {{ $mode === 'trading' ? '' : 'hidden' }}>Trading focuses on MOQ, bulk rules, volume price tiers, and seller-confirmed wholesale orders.</p>
      </section>

      <!-- Section 3: workspace -->
      <section class="seller-workspace" id="seller-workspace" aria-label="Seller workspace">
        <aside class="seller-nav" aria-label="Seller sections">
          <div class="seller-nav-group">
            <p class="seller-nav-title"><i class="fa-solid fa-box" aria-hidden="true"></i> Products</p>
            <div class="seller-nav-list" role="tablist" aria-orientation="vertical">
              <button type="button" class="seller-nav-item is-active" role="tab" data-panel="products" data-seller-modes="marketplace trading" aria-selected="true">
                <i class="fa-solid fa-list" aria-hidden="true"></i> My products
              </button>
              <button type="button" class="seller-nav-item" role="tab" data-panel="add-edit" data-seller-modes="marketplace trading" aria-selected="false">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Add / edit product
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="product-details" data-seller-modes="marketplace trading" aria-selected="false">
                <i class="fa-solid fa-table-list" aria-hidden="true"></i> Product details
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="variants" data-seller-modes="marketplace trading" aria-selected="false">
                <i class="fa-solid fa-swatchbook" aria-hidden="true"></i> Variants
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="pricing" data-seller-modes="marketplace" aria-selected="false">
                <i class="fa-solid fa-tags" aria-hidden="true"></i> Pricing
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="bulk-rule" data-seller-modes="marketplace" aria-selected="false">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Bulk rule
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="price-tiers" data-seller-modes="trading" aria-selected="false" hidden>
                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i> Price tiers
              </button>
              <button type="button" class="seller-nav-item needs-product" role="tab" data-panel="stock" data-seller-modes="marketplace trading" aria-selected="false">
                <i class="fa-solid fa-warehouse" aria-hidden="true"></i> Stock
              </button>
            </div>
          </div>

          <div class="seller-nav-group">
            <p class="seller-nav-title"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Orders</p>
            <div class="seller-nav-list" role="tablist" aria-orientation="vertical">
              <button type="button" class="seller-nav-item" role="tab" data-panel="retail-orders" data-seller-modes="marketplace" aria-selected="false">
                <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i> Retail orders
              </button>
              <button type="button" class="seller-nav-item" role="tab" data-panel="bulk-orders" data-seller-modes="trading" aria-selected="false" hidden>
                <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> Bulk orders
              </button>
            </div>
          </div>

          <a href="{{ url('/your-store') }}" class="seller-nav-store"><i class="fa-solid fa-store" aria-hidden="true"></i> Your Store</a>
        </aside>

        <div class="seller-panels">
          <!-- current product bar -->
          <div class="seller-current" id="currentBar" hidden>
            <div class="seller-current-info">
              <span class="seller-current-label">Editing</span>
              <strong id="currentTitle">—</strong>
              <span class="seller-pill" id="currentStatus"></span>
            </div>
            <div class="seller-current-missing" id="currentMissing" hidden></div>
          </div>

          <!-- ===== My products ===== -->
          <article class="seller-panel is-active" id="panel-products" role="tabpanel" data-panel="products">
            <div class="seller-panel-head">
              <div>
                <h2>My products</h2>
                <p>Everything you have listed for this channel. Open a product to edit its details, pricing and stock.</p>
              </div>
              <button type="button" class="seller-btn seller-btn-primary" id="newProductBtn"><i class="fa-solid fa-plus" aria-hidden="true"></i> New product</button>
            </div>
            <div class="seller-table-wrap">
              <table class="seller-table" id="myProductsTable">
                <thead><tr><th></th><th>Product</th><th>Category</th><th>Channel</th><th id="thPrice">Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="myProductsBody"><tr><td colspan="8" class="seller-empty">Loading…</td></tr></tbody>
              </table>
            </div>
          </article>

          <!-- ===== Add / edit ===== -->
          <article class="seller-panel" id="panel-add-edit" role="tabpanel" data-panel="add-edit" hidden>
            <div class="seller-panel-head">
              <div>
                <h2 id="addEditTitle">Add product</h2>
                <p>Name, category, images, and description for your listing.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>

            <form class="seller-form" id="formAddEdit" novalidate>
              <div class="seller-form-grid">
                <div class="seller-field">
                  <label for="productName">Product name</label>
                  <input id="productName" name="title" type="text" maxlength="300" required />
                </div>

                <div class="seller-field">
                  <label for="productSaleType">Sell on</label>
                  <select id="productSaleType" name="sale_type" class="seller-select">
                    <option value="retail">Marketplace (retail)</option>
                    <option value="wholesale">Trading (wholesale)</option>
                    <option value="both">Both channels</option>
                  </select>
                </div>

                @foreach ([['Category', 'productCategory'], ['Subcategory', 'productSubcategory'], ['Sub-subcategory', 'productSubSubcategory']] as $i => [$label, $id])
                  <div class="seller-field">
                    <label for="{{ $id }}">{{ $label }}</label>
                    <div class="seller-combo" data-combo data-level="{{ $i }}">
                      <input id="{{ $id }}" type="text" autocomplete="off" placeholder="{{ $i === 0 ? 'Select a category' : 'Select ' . strtolower($label) }}" role="combobox" aria-expanded="false" aria-autocomplete="list" {{ $i > 0 ? 'disabled' : '' }} />
                      <input type="hidden" data-combo-value />
                      <button type="button" class="seller-combo-toggle" aria-label="Show options" tabindex="-1"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                      <ul class="seller-combo-list" role="listbox" hidden></ul>
                    </div>
                  </div>
                @endforeach

                <div class="seller-field">
                  <label for="productBrand">Brand <span class="hint">optional</span></label>
                  <input id="productBrand" name="brand_name" type="text" list="brandList" maxlength="120" autocomplete="off" />
                  <datalist id="brandList">@foreach ($brands as $b)<option value="{{ $b }}"></option>@endforeach</datalist>
                </div>
                <div class="seller-field">
                  <label for="productSku">Product code (SKU)</label>
                  <input id="productSku" name="sku" type="text" maxlength="100" placeholder="Auto-generated if left blank" />
                </div>
                <div class="seller-field">
                  <label for="productModel">Model no. <span class="hint">optional</span></label>
                  <input id="productModel" name="model_no" type="text" maxlength="120" />
                </div>

                <div class="seller-field full">
                  <label>Images <span class="hint">up to 8 · JPG/PNG · 4 MB each · first image is the cover</span></label>
                  <input type="file" id="productImageInput" accept="image/*" multiple hidden />
                  <div class="seller-image-uploader" id="productImages"></div>
                </div>

                <div class="seller-field full">
                  <label for="productShort">Short description <span class="hint">shown on listing cards</span></label>
                  <textarea id="productShort" name="short_description" rows="2" maxlength="500"></textarea>
                </div>
                <div class="seller-field full">
                  <label for="productDescription">Description</label>
                  <textarea id="productDescription" name="description" rows="6"></textarea>
                </div>
              </div>
              <div class="seller-actions">
                <button type="submit" class="seller-btn seller-btn-primary" data-save="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save product</button>
                <button type="submit" class="seller-btn seller-btn-secondary" data-save="draft">Save as draft</button>
                <button type="button" class="seller-btn seller-btn-ghost" id="resetAddEdit">Reset</button>
              </div>
            </form>
          </article>

          <!-- ===== Product details ===== -->
          <article class="seller-panel" id="panel-product-details" role="tabpanel" data-panel="product-details" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Product details</h2>
                <p>Property and value pairs — as buyers see them on the product page. Fields come from the product’s category; add your own below.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>

            <div class="seller-details-layout">
              <div class="seller-preview-card" aria-label="Gallery preview">
                <div class="seller-preview-main"><img id="detailsMainImage" src="" alt="Selected product preview" /></div>
                <div class="seller-preview-thumbs">
                  <button type="button" class="seller-thumb-nav" id="thumbPrev" aria-label="Previous image"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                  <div class="seller-preview-thumbs-track" id="detailsThumbs"></div>
                  <button type="button" class="seller-thumb-nav" id="thumbNext" aria-label="Next image"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
              </div>

              <div>
                <div class="seller-spec-card" id="propertyRows"></div>
                <button type="button" class="seller-btn seller-btn-secondary seller-add-row" id="addPropertyRow"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add property</button>
                <div class="seller-actions"><button type="button" class="seller-btn seller-btn-primary" id="saveDetails">Save details</button></div>
              </div>
            </div>
          </article>

          <!-- ===== Variants ===== -->
          <article class="seller-panel" id="panel-variants" role="tabpanel" data-panel="variants" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Variants</h2>
                <p>Sizes, colours and other options. Each variant has its own code, price and stock.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <p class="hint" id="variantHint"></p>
            <div class="seller-variant-list" id="variantRows"></div>
            <button type="button" class="seller-btn seller-btn-secondary seller-add-row" id="addVariantRow"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add variant</button>
            <div class="seller-actions"><button type="button" class="seller-btn seller-btn-primary" id="saveVariants">Save variants</button></div>
          </article>

          <!-- ===== Pricing ===== -->
          <article class="seller-panel" id="panel-pricing" role="tabpanel" data-panel="pricing" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Pricing</h2>
                <p>Retail price for single units, the product unit and GST details.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <form class="seller-form" id="formPricing" novalidate>
              <div class="seller-price-cards">
                <div class="seller-price-card">
                  <h3>Retail price (single unit)</h3>
                  <p class="hint">Current price is optional — leave it blank if the MRP is the selling price.</p>
                  <div class="seller-variant-list" id="pricingRows"></div>
                </div>
                <div class="seller-price-card">
                  <h3>Product unit &amp; tax</h3>
                  <div class="seller-field">
                    <label for="productPricingUnit">Unit</label>
                    <div class="seller-combo" data-combo data-kind="unit">
                      <input id="productPricingUnit" type="text" autocomplete="off" placeholder="Select a unit" role="combobox" aria-expanded="false" aria-autocomplete="list" />
                      <input type="hidden" data-combo-value />
                      <button type="button" class="seller-combo-toggle" aria-label="Show unit options" tabindex="-1"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                      <ul class="seller-combo-list" role="listbox" hidden></ul>
                    </div>
                  </div>
                  <div class="seller-field" style="margin-top:12px">
                    <label for="hsnCode">HSN code <span class="hint">optional</span></label>
                    <input id="hsnCode" type="text" maxlength="20" />
                  </div>
                  <div class="seller-field" style="margin-top:12px">
                    <label for="taxRate">GST rate (%)</label>
                    <input id="taxRate" type="number" min="0" max="100" step="0.01" value="0" />
                  </div>
                  <label class="seller-check" style="margin-top:12px"><input type="checkbox" id="priceIncludesTax" checked /> Prices include tax</label>
                </div>
              </div>
              <div class="seller-actions"><button type="submit" class="seller-btn seller-btn-primary">Save pricing</button></div>
            </form>
          </article>

          <!-- ===== Bulk rule ===== -->
          <article class="seller-panel" id="panel-bulk-rule" role="tabpanel" data-panel="bulk-rule" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Bulk rule</h2>
                <p>Minimum bulk quantity that unlocks the bulk price. Saving a bulk rule also lists the product in Trading.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <form class="seller-form" id="formBulk" novalidate>
              <div class="seller-form-grid">
                <div class="seller-field">
                  <label for="moqQty">Minimum order quantity (MOQ)</label>
                  <input id="moqQty" type="number" min="1" step="any" />
                </div>
                <div class="seller-field">
                  <label for="moqUnit">Unit <span class="hint">change it in Pricing</span></label>
                  <input id="moqUnit" type="text" readonly />
                </div>
                <div class="seller-field">
                  <label for="bulkPricePerUnit">Bulk price per unit (₹)</label>
                  <input id="bulkPricePerUnit" type="number" min="0" step="0.01" placeholder="e.g. 24.52" />
                </div>
                <div class="seller-field full">
                  <label for="bulkNote">Buyer-facing note</label>
                  <textarea id="bulkNote" maxlength="255" rows="2" placeholder="Wholesale pricing • MOQ 5,000 pieces • Volume discounts apply"></textarea>
                </div>
              </div>
              <div class="seller-actions">
                <button type="submit" class="seller-btn seller-btn-warm"><i class="fa-solid fa-unlock" aria-hidden="true"></i> Save bulk rule</button>
                <button type="button" class="seller-btn seller-btn-ghost" id="disableBulk">Turn off bulk selling</button>
              </div>
            </form>
          </article>

          <!-- ===== Price tiers ===== -->
          <article class="seller-panel" id="panel-price-tiers" role="tabpanel" data-panel="price-tiers" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Price tiers</h2>
                <p>Quantity ranges with different per-unit prices. The first tier’s “From” is your minimum order quantity.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <div class="seller-field seller-tier-unit-field">
              <label for="tierProductUnit">Product unit</label>
              <div class="seller-combo" data-combo data-kind="unit">
                <input id="tierProductUnit" type="text" autocomplete="off" placeholder="Select a unit" role="combobox" aria-expanded="false" aria-autocomplete="list" />
                <input type="hidden" data-combo-value />
                <button type="button" class="seller-combo-toggle" aria-label="Show unit options" tabindex="-1"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                <ul class="seller-combo-list" role="listbox" hidden></ul>
              </div>
            </div>
            <div class="seller-tier-list" id="tierList"></div>
            <button type="button" class="seller-btn seller-btn-secondary seller-add-row" id="addTierRow"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add tier</button>
            <div class="seller-actions"><button type="button" class="seller-btn seller-btn-primary" id="saveTiers">Save tiers</button></div>
          </article>

          <!-- ===== Stock ===== -->
          <article class="seller-panel" id="panel-stock" role="tabpanel" data-panel="stock" hidden>
            <div class="seller-panel-head">
              <div>
                <h2>Stock</h2>
                <p>Available quantity and low-stock threshold alerts for each variant.</p>
              </div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <form class="seller-form" id="formStock" novalidate>
              <label class="seller-check"><input type="checkbox" id="trackStock" checked /> Track stock for this product</label>
              <div class="seller-variant-list" id="stockRows" style="margin-top:12px"></div>
              <div class="seller-stock-meter" id="stockMeter">
                <strong>Inventory health</strong>
                <p class="hint" id="stockMeterLabel">—</p>
                <div class="bar" aria-hidden="true"><span></span></div>
              </div>
              <div class="seller-actions"><button type="submit" class="seller-btn seller-btn-primary">Update stock</button></div>
            </form>
            <h3 class="seller-subhead">Recent stock activity</h3>
            <div class="seller-table-wrap">
              <table class="seller-table"><thead><tr><th>When</th><th>Variant</th><th>Type</th><th>Change</th><th>Balance</th></tr></thead><tbody id="stockMovements"></tbody></table>
            </div>
          </article>

          <!-- ===== Retail orders ===== -->
          <article class="seller-panel" id="panel-retail-orders" role="tabpanel" data-panel="retail-orders" hidden>
            <div class="seller-panel-head">
              <div><h2>Retail orders</h2><p>Single buyer, fixed price, instant checkout.</p></div>
              <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
            </div>
            <div class="seller-table-wrap">
              <div class="seller-order-retention">
                <label for="retailOrderRetention">Auto-remove old orders</label>
                <select id="retailOrderRetention" data-order-retention="sellerRetailOrdersTable" aria-label="Automatically remove old retail orders">
                  <option value="30">Remove after 30 days</option><option value="45">Remove after 45 days</option><option value="60" selected>Remove after 60 days</option>
                </select>
              </div>
              <table class="seller-table" id="sellerRetailOrdersTable" data-order-table>
                <thead><tr><th>Order</th><th>Order Date</th><th>Buyer</th><th>Item</th><th>Qty</th><th>Total</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                  @forelse ($retailItems as $i)
                    <tr data-order-date="{{ $i->created_at->format('Y-m-d') }}">
                      <td>#{{ $i->order->order_no }}</td>
                      <td>{{ $i->created_at->format('j M Y') }}</td>
                      <td>{{ $i->order->buyer_name }}</td>
                      <td>{{ $i->title }}</td>
                      <td>{{ rtrim(rtrim(number_format($i->qty, 3, '.', ''), '0'), '.') }}</td>
                      <td>₹{{ number_format($i->line_total, 2) }}</td>
                      <td><span class="seller-pill is-{{ $i->status }}" data-order-status="{{ $i->id }}">{{ ucfirst($i->status) }}</span></td>
                      <td><a class="seller-order-link" href="#" data-item="{{ $i->id }}">See Details</a></td>
                    </tr>
                  @empty
                    <tr class="seller-empty-row"><td colspan="8" class="seller-empty">No retail orders yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </article>

          <!-- ===== Bulk orders ===== -->
          <article class="seller-panel" id="panel-bulk-orders" role="tabpanel" data-panel="bulk-orders" hidden>
            <div class="seller-panel-head">
              <div><h2>Bulk orders</h2><p>Buyer requests quantity; you confirm the price before payment.</p></div>
              <span class="seller-panel-badge" data-mode-badge>Trading</span>
            </div>
            <div class="seller-table-wrap">
              <div class="seller-order-retention">
                <label for="bulkOrderRetention">Auto-remove old orders</label>
                <select id="bulkOrderRetention" data-order-retention="sellerBulkOrdersTable" aria-label="Automatically remove old bulk orders">
                  <option value="30">Remove after 30 days</option><option value="45">Remove after 45 days</option><option value="60" selected>Remove after 60 days</option>
                </select>
              </div>
              <table class="seller-table" id="sellerBulkOrdersTable" data-order-table>
                <thead><tr><th>Request</th><th>Order Date</th><th>Buyer</th><th>Product</th><th>Qty</th><th>Quoted / unit</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                  @forelse ($bulkItems as $i)
                    <tr data-order-date="{{ $i->created_at->format('Y-m-d') }}">
                      <td>#{{ $i->order->order_no }}</td>
                      <td>{{ $i->created_at->format('j M Y') }}</td>
                      <td>{{ $i->order->buyer_name }}</td>
                      <td>{{ $i->title }}</td>
                      <td>{{ rtrim(rtrim(number_format($i->qty, 3, '.', ''), '0'), '.') }} {{ $i->unit_name }}</td>
                      <td>₹{{ rtrim(rtrim(number_format($i->unit_price, 4, '.', ','), '0'), '.') }}</td>
                      <td><span class="seller-pill is-{{ $i->status }}" data-order-status="{{ $i->id }}">{{ ucfirst($i->status) }}</span></td>
                      <td><a class="seller-order-link" href="#" data-item="{{ $i->id }}">See Details</a></td>
                    </tr>
                  @empty
                    <tr class="seller-empty-row"><td colspan="8" class="seller-empty">No bulk orders yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </section>
    </main>

    <!-- order details modal -->
    <div class="seller-modal" id="orderModal" hidden>
      <div class="seller-modal-backdrop" data-close></div>
      <div class="seller-modal-card" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <button type="button" class="seller-modal-close" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        <div id="orderModalBody"></div>
      </div>
    </div>

    <div class="seller-toasts" id="sellerToasts" aria-live="polite"></div>

    <footer class="site-footer" id="footer">
      <div class="footer-card">
        <div class="container footer-grid">
          <div class="footer-brand">
            <div class="footer-brand-inner">
              <img src="{{ asset('assets/images/logo_full.png') }}" alt="IBNCC" width="56" height="56" />
              <div>
                <strong class="footer-tagline">International Business Network <br />of Catholic Congress</strong>
                <p>A global platform connecting Catholic entrepreneurs, professionals and organizations to collaborate, trade and grow together.</p>
              </div>
            </div>
          </div>
          <div class="footer-nav">
            <h3>Navigate</h3>
            <a href="{{ url('/') }}#home"><span>Home</span></a>
            <a href="{{ url('/') }}#about"><span>About</span></a>
            <a href="{{ url('/') }}#events"><span>Events</span></a>
            <a href="{{ url('/') }}#networking"><span>Networking</span></a>
            <a href="{{ url('/trading') }}"><span>Trading</span></a>
          </div>
          <div class="footer-connect">
            <h3>Connect</h3>
            <a href="mailto:ibncc26@gmail.com" class="footer-contact-link"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>ibncc26@gmail.com</span></a>
            <a href="tel:+919847030064" class="footer-contact-link"><i class="fa-solid fa-phone" aria-hidden="true"></i><span>+91 9847030064</span></a>
            <h3 class="footer-follow">Follow Us</h3>
            <div class="socials" aria-label="Social links">
              <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
              <a href="#" aria-label="X / Twitter"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
              <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
              <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
            </div>
          </div>
        </div>
        <div class="container footer-bottom">
          <p class="footer-copy">&copy; {{ date('Y') }} IBNCC. All rights reserved.</p>
          <div class="footer-design"><a href="#">Designed By : Astraz Software solutions</a></div>
        </div>
      </div>
    </footer>

    <script>
      window.SELLER = {
        mode: @json($mode),
        roots: @json($rootCats->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'has_children' => $c->children_count > 0])->values()),
        units: @json($units),
        routes: {
          products: @json(route('seller.products.index')),
          product: @json(url('seller/products')),        // + /{uuid}[/section]
          children: @json(route('seller.categories.children')),
          categoryAttrs: @json(url('seller/categories')), // + /{id}/attributes
          orders: @json(url('seller/orders'))             // + /{id}
        }
      };
    </script>
    <script src="{{ asset('js/cchub/ch-login.js') }}"></script>
    <script src="{{ asset('js/cchub/seller-order-retention.js') }}"></script>
    <script src="{{ asset('js/cchub/seller_page.js') }}"></script>
  </body>
</html>
