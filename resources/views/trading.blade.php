@extends('layouts.trading', ['navCategories' => $categories])
@section('body')
   @include('trading.navbar')


  


@if($banners->count())
<section class="hero" aria-label="Promotional banners">
    <div class="hero-slider" id="heroSlider">
        <div class="hero-track" id="heroTrack">
          @foreach($banners as $b)
    <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-index="{{ $loop->index }}">
        @if($b->link)
            <a href="{{ url($b->link) }}" style="display:block">
                <img src="{{ asset($b->image) }}" alt="Banner {{ $loop->iteration }}"
                    class="hero-slide-img" @if(!$loop->first) loading="lazy" @endif>
            </a>
        @else
            <img src="{{ asset($b->image) }}" alt="Banner {{ $loop->iteration }}"
                class="hero-slide-img" @if(!$loop->first) loading="lazy" @endif>
        @endif
    </article>
@endforeach
</div>

        <button class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Previous slide">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Next slide">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div class="hero-dots" id="heroDots" role="tablist" aria-label="Slide pagination"></div>
    </div>
</section>
@endif

    <main class="trading-main">
      <!-- TRADING — scrolls right → left -->
      <section
        class="trade-section"
        id="trading"
        aria-labelledby="trading-heading"
      >
        <header class="trade-section-intro">
          <h1 id="trading-heading" class="trade-section-title">Trading</h1>
          <p class="trade-section-subtitle">
            Bulk Trading. Better Value. Stronger Together.
          </p>
        </header>

        <div
          class="marquee marquee--rtl"
          data-marquee
          data-direction="rtl"
          aria-label="Trading categories carousel"
        >
          <button
            type="button"
            class="marquee-arrow marquee-arrow-prev"
            aria-label="Previous"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button
            type="button"
            class="marquee-arrow marquee-arrow-next"
            aria-label="Next"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          <div class="marquee-viewport">
            <div class="marquee-track" data-marquee-track>
              @foreach($tradingCategories as $cat)
@php $tcMods = ['trade-card--grains','trade-card--agro','trade-card--electronics','trade-card--home','trade-card--spices','trade-card--packaging']; @endphp
              <article class="trade-card {{ $tcMods[$loop->index % 6] }}">
                <span class="trade-card-icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
                <div class="trade-card-copy">
                  <h2>{{ $cat->name }}</h2>
                  <p>{{ $cat->description ?? '' }}</p>
                  <a href="{{ route('trading.products.index') }}?category={{ $cat->slug }}" class="trade-card-cta">Trade Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <img class="trade-card-img" src="{{ $cat->image ? asset($cat->image) : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $cat->name }}" />
              </article>
@endforeach
                </div>
               
              </article>

              
            </div>
          </div>

          <div class="marquee-dots" aria-hidden="true">
            <span class="marquee-dot is-active"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
          </div>
        </div>
      </section>

      <!-- MARKET PLACE — scrolls left → right -->
      <section
        class="trade-section"
        id="marketplace"
        aria-labelledby="marketplace-heading"
      >
        <header class="trade-section-intro">
          <h2 id="marketplace-heading" class="trade-section-title">
            Market Place
          </h2>
          <p class="trade-section-subtitle">
            Discover deals across top categories
          </p>
        </header>

        <div
          class="marquee marquee--ltr"
          data-marquee
          data-direction="ltr"
          aria-label="Marketplace deals carousel"
        >
          <button
            type="button"
            class="marquee-arrow marquee-arrow-prev"
            aria-label="Previous"
          >
            <i class="fa-solid fa-chevron-left"></i>
          </button>
          <button
            type="button"
            class="marquee-arrow marquee-arrow-next"
            aria-label="Next"
          >
            <i class="fa-solid fa-chevron-right"></i>
          </button>

          <div class="marquee-viewport">
            <div class="marquee-track" data-marquee-track>
              @foreach($marketplaceProducts as $p)
@php $mcMods = ['market-card--luggage','market-card--watches','market-card--jewellery','market-card--kindle','market-card--fashion','market-card--beauty']; @endphp
              <article class="market-card {{ $mcMods[$loop->index % 6] }}">
                <div class="market-card-copy">
                  <h3>{{ $p->title }}</h3>
                  <p>{{ $p->short_description ?? '' }}</p>
                  @if($p->min_price)
                    <p class="market-card-price"><strong>Rs {{ $p->min_price }}</strong>@if($p->max_price && $p->max_price != $p->min_price) <span>Rs {{ $p->max_price }}</span>@endif</p>
                  @endif
                  <a href="{{ route('products.show', $p->slug) }}" class="market-card-cta">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <img class="market-card-img" src="{{ $p->images->first()?->url ?? 'https://images.unsplash.com/photo-1565022449183-c4a2d0b2a0a0?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $p->title }}" />
              </article>
@endforeach
                </div>
               
              </article>

              
            </div>
          </div>

          <div class="marquee-dots" aria-hidden="true">
            <span class="marquee-dot is-active"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
            <span class="marquee-dot"></span>
          </div>
        </div>
      </section>

      <!-- Trading / Marketplace Tabs -->
      <div
        class="trading-mode-tabs"
        role="tablist"
        aria-label="Trading and Marketplace"
      >
        <button
          type="button"
          class="trading-mode-tab is-active"
          id="tab-marketplace"
          role="tab"
          aria-selected="true"
          data-mode="marketplace"
        >
          <span class="trading-mode-tab-icon" aria-hidden="true">
            <i class="fa-solid fa-store"></i>
          </span>

          <span class="trading-mode-tab-label">Marketplace</span>
        </button>
        <button
          type="button"
          class="trading-mode-tab"
          id="tab-trading"
          role="tab"
          aria-selected="false"
          data-mode="trading"
        >
          <span class="trading-mode-tab-icon" aria-hidden="true">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
          </span>
          <span class="trading-mode-tab-label">Trading</span>
        </button>
      </div>

      <!-- Galleries -->
      <section
        class="gallery-block"
        id="browse-category"
        aria-labelledby="browse-heading"
      >
        <header class="gallery-block-header">
          <div>
            <h2 id="browse-heading" class="gallery-block-title">
              Browse by Category
            </h2>
            <p class="gallery-block-subtitle">
              Find what you love from top categories
            </p>
          </div>
        </header>

        <div class="category-gallery">
@foreach($categories as $cat)
          <div class="category-gallery-card" style="--cat-bg: #e8eef5">
            <div class="category-gallery-media">
@foreach($cat->children->take(4) as $child)
              <a href="{{ route('trading.products.index') }}?category={{ $child->slug }}"><img src="{{ $child->image ? asset($child->image) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=200&q=80' }}" alt="{{ $child->name }}" /></a>
@endforeach
            </div>
            <a href="{{ route('trading.products.index') }}?category={{ $cat->slug }}" class="category-gallery-footer" style="text-decoration:none;color:inherit;">
              <strong>{{ $cat->name }}</strong>
              <span>Explore <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </a>
          </div>
@endforeach
        </div>
      </section>

        <section
        class="gallery-block"
        id="bestsellers"
        aria-labelledby="bestsellers-heading"
      >
        <header class="gallery-block-header gallery-block-header--row">
          <div>
            <h2 id="bestsellers-heading" class="gallery-block-title">
              Bestsellers
            </h2>
            <p class="gallery-block-subtitle">
              Top picks loved by our customers
            </p>
          </div>
          <a href="#all-bestsellers" class="gallery-view-all"
            >View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
          ></a>
        </header>

        <div class="bestseller-grid">
          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=400&q=80"
                alt="boAt Airdopes earbuds"
              />
            </div>
            <h3>boAt Airdopes 141</h3>
            <p>Wireless Earbuds</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.5
              <span>(12.4k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹1,299</strong><s>₹2,999</s><em>57% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80"
                alt="Noise ColorFit smartwatch"
              />
            </div>
            <h3>Noise ColorFit Pro 4</h3>
            <p>Smartwatch</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.4
              <span>(8.1k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹2,499</strong><s>₹5,999</s><em>58% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80"
                alt="Sony headphones"
              />
            </div>
            <h3>Sony WH-CH520</h3>
            <p>Bluetooth Headphones</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.6
              <span>(5.2k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹4,490</strong><s>₹5,990</s><em>25% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=400&q=80"
                alt="Skincare set"
              />
            </div>
            <h3>Minimalist SPF 50</h3>
            <p>Sunscreen</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.7
              <span>(21k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹399</strong><s>₹499</s><em>20% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80"
                alt="Running shoes"
              />
            </div>
            <h3>Nike Revolution 6</h3>
            <p>Men&apos;s Running Shoes</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.3
              <span>(3.8k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹3,695</strong><s>₹4,495</s><em>18% OFF</em>
            </div>
          </article>

          <article class="bestseller-card">
            <div class="bestseller-media">
              <img
                src="https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=400&q=80"
                alt="Kitchen mixer"
              />
            </div>
            <h3>Prestige Mixer</h3>
            <p>Kitchen Appliance</p>
            <div class="bestseller-rating">
              <i class="fa-solid fa-star" aria-hidden="true"></i> 4.2
              <span>(9.6k)</span>
            </div>
            <div class="bestseller-price">
              <strong>₹2,999</strong><s>₹5,495</s><em>45% OFF</em>
            </div>
          </article>
        </div>
      </section>

      <section
        class="gallery-block"
        id="similar-searches"
        aria-labelledby="similar-heading"
      >
        <header class="gallery-block-header gallery-block-header--row">
          <div>
            <h2 id="similar-heading" class="gallery-block-title">
              Similar to your searches
            </h2>
            <p class="gallery-block-subtitle">Handpicked just for you</p>
          </div>
          <a href="#all-similar" class="gallery-view-all"
            >View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
          ></a>
        </header>

        <div class="similar-rows">
          <div class="similar-row">
            <a
              href="#luggage"
              class="similar-lead"
              style="--lead-bg: #e8f0e4; --lead-color: #2f5d3a"
            >
              <h3>Luggage &amp; Travel</h3>
              <p>Built for every journey</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#luggage-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=500&q=80"
                alt="Hard-shell suitcase"
              />
            </a>
            <a href="#luggage-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1565026057447-bc90a3dceb87?auto=format&fit=crop&w=500&q=80"
                alt="Travel duffel bag"
              />
            </a>
            <a href="#luggage-3" class="similar-photo">
              <img src="assets/images/lag2.jpg" alt="Cabin luggage set" />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#smartwatches"
              class="similar-lead"
              style="--lead-bg: #eaf4fb; --lead-color: #0b2a4a"
            >
              <h3>Smartwatches</h3>
              <p>Track fitness in style</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#watch-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=500&q=80"
                alt="Black smartwatch"
              />
            </a>
            <a href="#watch-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?auto=format&fit=crop&w=500&q=80"
                alt="Sport smartwatch"
              />
            </a>
            <a href="#watch-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=500&q=80"
                alt="Classic smartwatch"
              />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#home-essentials"
              class="similar-lead"
              style="--lead-bg: #fbf6e4; --lead-color: #c9a017"
            >
              <h3>Home Essentials</h3>
              <p>Comfort for every room</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#home-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=500&q=80"
                alt="Bedroom furniture"
              />
            </a>
            <a href="#home-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=500&q=80"
                alt="Dining set"
              />
            </a>
            <a href="#home-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80"
                alt="Living room sofa"
              />
            </a>
          </div>

          <div class="similar-row">
            <a
              href="#kitchen"
              class="similar-lead"
              style="--lead-bg: #e8f1fb; --lead-color: #1d4e89"
            >
              <h3>Kitchen Appliances</h3>
              <p>Cook smarter every day</p>
              <span
                >Explore now
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i
              ></span>
            </a>
            <a href="#kitchen-1" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=500&q=80"
                alt="Microwave oven"
              />
            </a>
            <a href="#kitchen-2" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1585515320310-259814833e62?auto=format&fit=crop&w=500&q=80"
                alt="Air fryer"
              />
            </a>
            <a href="#kitchen-3" class="similar-photo">
              <img
                src="https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=500&q=80"
                alt="Blender"
              />
            </a>
          </div>
        </div>
      </section>
    </main>

    <!-- ========== FOOTER ========== -->
   @endsection