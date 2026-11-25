<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $profile?->organization ?: $user->name }} — Your Store — CC Hub</title>
    <meta name="description" content="Manage your Marketplace and Trading store listings and orders on CC Hub." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/trading-product-list.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/seller_page.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/cchub/your-store.css') }}" />
  </head>
  <body class="your-store-page product-list-page" data-store-mode="marketplace" data-store-view="gallery">
    <header class="site-header">
      <div class="header-inner">
        <a href="{{ url('/') }}#home" class="brand" aria-label="IBNCC Home">
          <span class="brand-text">
            <img src="{{ asset('assets/images/logo_full.png') }}" alt="IBNCC logo" class="brand-logo" width="52" height="52" />
            <span class="brand-tagline"><span class="brand-tagline-line">AN INITIATIVE OF</span><span class="brand-tagline-line">CATHOLIC CONGRESS</span></span>
          </span>
        </a>

        <form class="search-bar" role="search" action="{{ url('/seller') }}" method="get">
          <input type="search" name="q" placeholder="Search for products, brands and more..." aria-label="Search" />
          <button type="submit" class="search-btn" aria-label="Submit search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="header-actions">
          @auth
            <a href="{{ url('/seller') }}" class="btn-login" style="background:#0f766e; color:#fff; font-weight:600;"><i class="fa-solid fa-gauge-high me-1"></i> Seller Workspace</a>
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

    <main class="your-store-main">
      <header>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
          <div>
            <h1 class="your-store-title">{{ $profile?->organization ?: $user->name . "'s Store" }}</h1>
            <p class="your-store-title-sub">
              {{ $profile?->about ?: 'Review marketplace and trading listings, edit products, and track retail or bulk orders.' }}
            </p>
          </div>
          <div style="display:flex; gap:10px;">
            <a href="{{ url('/seller') }}" class="seller-btn seller-btn-primary" style="text-decoration:none;"><i class="fa-solid fa-sliders me-1"></i> Manage Products in Hub</a>
          </div>
        </div>
      </header>

      <div class="your-store-mode-tabs" role="tablist" aria-label="Store channel">
        <button type="button" class="your-store-mode-tab is-active" role="tab" data-store-mode-tab="marketplace" aria-selected="true">
          <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Marketplace ({{ $marketplaceProducts->count() }})
        </button>
        <button type="button" class="your-store-mode-tab" role="tab" data-store-mode-tab="trading" aria-selected="false">
          <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i> Trading ({{ $tradingProducts->count() }})
        </button>
      </div>

      <div class="your-store-sub-tabs" role="tablist" aria-label="Store content">
        <button type="button" class="your-store-sub-tab is-active" role="tab" data-store-view-tab="gallery" aria-selected="true">
          <i class="fa-solid fa-images" aria-hidden="true"></i> Product Gallery
        </button>
        <button type="button" class="your-store-sub-tab" role="tab" data-store-view-tab="orders" aria-selected="false">
          <i class="fa-solid fa-receipt" aria-hidden="true"></i> Order Details ({{ $orders->count() }})
        </button>
      </div>

      <!-- Marketplace → Product Gallery -->
      <section class="your-store-pane" id="storeMarketplaceGallery" aria-label="Marketplace product gallery">
        <div class="your-store-gallery-head">
          <h2>Marketplace products</h2>
          <p>Retail listings in your store</p>
        </div>
        @if($marketplaceProducts->isEmpty())
          <p class="your-store-empty" style="text-align:center; padding:50px 20px; color:#64748b;">No retail products listed yet. <a href="{{ url('/seller') }}">Add products from your Seller Hub</a>.</p>
        @else
          <div class="pl-grid" aria-live="polite">
            @foreach($marketplaceProducts as $p)
              @php
                $v = $p->defaultVariant ?: $p->variants->first();
                $price = $v?->price ?? $p->min_price ?? 0;
                $mrp = $v?->mrp ?? $p->max_price ?? 0;
                $discount = ($mrp > $price && $mrp > 0) ? round((($mrp - $price) / $mrp) * 100) : 0;
                $stock = $v?->stock ?? 0;
                $firstImg = $p->images->first()?->path;
                $img = $firstImg ? (str_starts_with($firstImg, 'http') ? $firstImg : asset($firstImg)) : asset('assets/images/no-image.avif');
              @endphp
              <article class="pl-card" data-id="{{ $p->id }}" data-price="{{ $price }}">
                <div class="pl-card-img-wrap">
                  <img src="{{ $img }}" alt="{{ $p->title }}" loading="lazy" />
                </div>
                <h3 class="pl-card-title">
                  <a href="{{ route('products.show', $p->slug) }}" target="_blank">{{ $p->title }}</a>
                </h3>
                <p class="pl-card-specs">
                  {{ $p->brand?->name ?? 'IBNCC' }} &bull; {{ $p->category?->name ?? 'General' }}
                </p>
                <div class="pl-rating-row">
                  <span class="pl-rating-badge">{{ number_format($p->rating_avg ?? 4.5, 1) }} <i class="fa-solid fa-star"></i></span>
                  <span class="pl-reviews">({{ $p->rating_count ?? 1 }})</span>
                </div>
                <div class="pl-price-row">
                  <span class="pl-price">₹{{ number_format($price) }}</span>
                  @if($mrp > $price)
                    <span class="pl-mrp">₹{{ number_format($mrp) }}</span>
                    <span class="pl-discount">{{ $discount }}% off</span>
                  @endif
                </div>
                <p class="pl-stock {{ $stock > 0 ? 'is-in' : 'is-out' }}">{{ $stock > 0 ? 'In Stock (' . $stock . ')' : 'Out of Stock' }}</p>
                <div class="pl-card-actions">
                  <a href="{{ url('/seller') }}" class="pl-btn-edit">Edit in Hub</a>
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </section>

      <!-- Trading → Product Gallery -->
      <section class="your-store-pane trading-product-list-page" id="storeTradingGallery" aria-label="Trading product gallery" hidden>
        <div class="your-store-gallery-head">
          <h2>Trading products</h2>
          <p>Wholesale listings with MOQ &amp; tiered pricing</p>
        </div>
        @if($tradingProducts->isEmpty())
          <p class="your-store-empty" style="text-align:center; padding:50px 20px; color:#64748b;">No wholesale products listed yet. <a href="{{ url('/seller') }}">Set up bulk rules or price tiers in Seller Hub</a>.</p>
        @else
          <div class="pl-grid" aria-live="polite">
            @foreach($tradingProducts as $p)
              @php
                $v = $p->defaultVariant ?: $p->variants->first();
                $firstTier = $p->priceTiers->sortBy('min_qty')->first();
                $price = $firstTier ? $firstTier->price : ($v?->price ?? $p->min_price ?? 0);
                $firstImg = $p->images->first()?->path;
                $img = $firstImg ? (str_starts_with($firstImg, 'http') ? $firstImg : asset($firstImg)) : asset('assets/images/no-image.avif');
                $moq = $p->min_order_qty ?: ($firstTier?->min_qty ?: 1);
              @endphp
              <article class="pl-card" data-id="{{ $p->id }}" data-price="{{ $price }}">
                <div class="pl-card-img-wrap">
                  <img src="{{ $img }}" alt="{{ $p->title }}" loading="lazy" />
                </div>
                <h3 class="pl-card-title">
                  <a href="{{ route('products.show', $p->slug) }}" target="_blank">
                    @if($p->is_assured)<span class="pl-super-badge" aria-label="Assured"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Assured</span>@endif
                    {{ $p->title }}
                  </a>
                </h3>
                <p class="pl-card-specs">
                  {{ $p->brand?->name ?? 'Wholesale' }} &bull; {{ $p->category?->name ?? 'Bulk Trading' }}
                </p>
                <div class="pl-rating-row">
                  <span class="pl-rating-badge">{{ number_format($p->rating_avg ?? 4.5, 1) }} <i class="fa-solid fa-star"></i></span>
                  <span class="pl-reviews">({{ $p->rating_count ?? 1 }})</span>
                </div>
                <div class="pl-price-row">
                  <span class="pl-price">₹{{ number_format($price, 2) }}</span>
                  <span class="pl-moq">MOQ: {{ $moq }} {{ $p->unit?->name ?? 'pcs' }}</span>
                </div>
                @if($p->priceTiers->count() > 1)
                  <div style="font-size:12px; color:#059669; font-weight:600; margin-top:4px;">
                    <i class="fa-solid fa-layer-group"></i> {{ $p->priceTiers->count() }} volume price tiers
                  </div>
                @endif
                <div class="pl-card-actions">
                  <a href="{{ url('/seller') }}" class="pl-btn-edit">Edit in Hub</a>
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </section>

      @php
        $retailOrders = $orders->filter(fn($i) => ($i->price_type ?? 'retail') !== 'wholesale');
        $bulkOrders = $orders->filter(fn($i) => ($i->price_type ?? 'retail') === 'wholesale');
      @endphp

      <!-- Marketplace → Order Details -->
      <article class="seller-panel your-store-pane" id="panel-retail-orders" role="tabpanel" data-panel="retail-orders" hidden>
        <div class="seller-panel-head">
          <div>
            <h2>Retail orders</h2>
            <p>Customer purchases fulfilled through your retail storefront.</p>
          </div>
          <span class="seller-panel-badge" data-mode-badge>Marketplace</span>
        </div>
        <div class="seller-table-wrap">
          <table class="seller-table" id="sellerRetailOrdersTable">
            <thead>
              <tr>
                <th>Order</th><th>Date</th><th>Buyer</th><th>Item</th><th>Qty</th><th>Total</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($retailOrders as $i)
                <tr>
                  <td>#{{ $i->order?->order_no ?? $i->id }}</td>
                  <td>{{ $i->created_at?->format('d M Y') }}</td>
                  <td>{{ $i->order?->buyer_name ?? 'Customer' }}</td>
                  <td>{{ $i->title }}</td>
                  <td>{{ rtrim(rtrim(number_format($i->qty, 3, '.', ''), '0'), '.') }}</td>
                  <td>₹{{ number_format($i->line_total ?? $i->total_amount ?? 0, 2) }}</td>
                  <td><span class="seller-pill is-{{ $i->status }}">{{ ucfirst($i->status) }}</span></td>
                </tr>
              @empty
                <tr class="seller-empty-row"><td colspan="7" class="seller-empty">No retail orders yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>

      <!-- Trading → Order Details -->
      <article class="seller-panel your-store-pane" id="panel-bulk-orders" role="tabpanel" data-panel="bulk-orders" hidden>
        <div class="seller-panel-head">
          <div>
            <h2>Trading bulk requests</h2>
            <p>Wholesale quotations and bulk purchase inquiries from trading buyers.</p>
          </div>
          <span class="seller-panel-badge" data-mode-badge>Trading</span>
        </div>
        <div class="seller-table-wrap">
          <table class="seller-table" id="sellerBulkOrdersTable">
            <thead>
              <tr>
                <th>Request</th><th>Date</th><th>Buyer</th><th>Item</th><th>Qty</th><th>Quoted Unit</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($bulkOrders as $i)
                <tr>
                  <td>#{{ $i->order?->order_no ?? $i->id }}</td>
                  <td>{{ $i->created_at?->format('d M Y') }}</td>
                  <td>{{ $i->order?->buyer_name ?? 'Buyer' }}</td>
                  <td>{{ $i->title }}</td>
                  <td>{{ rtrim(rtrim(number_format($i->qty, 3, '.', ''), '0'), '.') }} {{ $i->unit_name }}</td>
                  <td>₹{{ number_format($i->unit_price, 2) }}</td>
                  <td><span class="seller-pill is-{{ $i->status }}">{{ ucfirst($i->status) }}</span></td>
                </tr>
              @empty
                <tr class="seller-empty-row"><td colspan="7" class="seller-empty">No bulk requests yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
    </main>

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

    <script src="{{ asset('js/cchub/ch-login.js') }}"></script>
    <script src="{{ asset('js/cchub/seller-order-retention.js') }}"></script>
    <script src="{{ asset('js/cchub/your-store.js') }}"></script>
  </body>
</html>