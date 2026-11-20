@extends('layouts.trading', ['navCategories' => $categories])

@section('body')
@include('trading.navbar')

@php
    $resolveImg = function ($path, $default = 'assets/images/no-image.avif') {
        if (!$path) return asset($default);
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) return $path;
        $clean = ltrim($path, '/\\');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);
        return asset($clean);
    };
@endphp

{{-- ==================== 1. HERO SLIDER ==================== --}}
<section class="hero" aria-label="Promotional banners">
    <div class="hero-slider" id="heroSlider">
        <div class="hero-track" id="heroTrack">
            @forelse($banners as $b)
                <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-index="{{ $loop->index }}">
                    <img src="{{ $resolveImg($b->image) }}" alt="{{ $b->title ?? 'Banner ' . $loop->iteration }}"
                        class="hero-slide-img" @if(!$loop->first) loading="lazy" @endif>
                    @if($b->title || $b->subtitle || $b->eyebrow)
                        <div class="hero-slide-overlay {{ ($b->text_position ?? 'right') === 'left' ? 'darker' : '' }}"></div>
                        <div class="hero-slide-content hero-content-{{ $b->text_position ?? 'right' }}">
                            @if($b->eyebrow)<p class="hero-eyebrow">{{ $b->eyebrow }}</p>@endif
                            @if($b->title)<h2 class="hero-title">{{ $b->title }}</h2>@endif
                            @if($b->subtitle)<p class="hero-subtitle">{{ $b->subtitle }}</p>@endif
                            @if($b->cta_text)
                                <a href="{{ $b->link ? url($b->link) : '#' }}" class="hero-cta">{{ $b->cta_text }}</a>
                            @endif
                        </div>
                    @elseif($b->link)
                        <a href="{{ url($b->link) }}" class="hero-slide-link" aria-label="View banner link" style="position:absolute;inset:0;z-index:2;"></a>
                    @endif
                </article>
            @empty
                <article class="hero-slide is-active" data-index="0">
                    <img src="{{ asset('assets/images/gift2.png') }}" alt="Floral Bloom" class="hero-slide-img" />
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-content hero-content-right">
                        <p class="hero-eyebrow">IBNCC Presents</p>
                        <h2 class="hero-title">Floral Bloom</h2>
                        <p class="hero-subtitle">Timeless pieces for moments that matter</p>
                        <a href="{{ route('products.index') }}" class="hero-cta">Shop Now</a>
                    </div>
                </article>
                <article class="hero-slide" data-index="1">
                    <img src="{{ asset('assets/images/harvest.png') }}" alt="Harvest Fresh" class="hero-slide-img" loading="lazy" />
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-content hero-content-left">
                        <p class="hero-eyebrow">Farm &amp; Garden</p>
                        <h2 class="hero-title">Harvest Fresh</h2>
                        <p class="hero-subtitle">Quality produce from trusted local growers</p>
                        <a href="{{ route('products.index', ['cat' => 'farm-garden']) }}" class="hero-cta">Explore Deals</a>
                    </div>
                </article>
                <article class="hero-slide" data-index="2">
                    <img src="{{ asset('assets/images/network1.png') }}" alt="Connect &amp; Grow" class="hero-slide-img" loading="lazy" />
                    <div class="hero-slide-overlay darker"></div>
                    <div class="hero-slide-content hero-content-left">
                        <p class="hero-eyebrow">Networking</p>
                        <h2 class="hero-title">Connect &amp; Grow</h2>
                        <p class="hero-subtitle">Build trusted partnerships across the community</p>
                        <a href="{{ url('/members') }}" class="hero-cta">Join Network</a>
                    </div>
                </article>
                <article class="hero-slide" data-index="3">
                    <img src="{{ asset('assets/images/sld3.png') }}" alt="Market Essentials" class="hero-slide-img" loading="lazy" />
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-content hero-content-right">
                        <p class="hero-eyebrow">Food Items</p>
                        <h2 class="hero-title">Market Essentials</h2>
                        <p class="hero-subtitle">Everyday staples delivered with care</p>
                        <a href="{{ route('products.index', ['cat' => 'food-items']) }}" class="hero-cta">Shop Food</a>
                    </div>
                </article>
                <article class="hero-slide" data-index="4">
                    <img src="{{ asset('assets/images/wholesale.png') }}" alt="Bulk. Better. Together." class="hero-slide-img" loading="lazy" />
                    <div class="hero-slide-overlay darker"></div>
                    <div class="hero-slide-content hero-content-left">
                        <p class="hero-eyebrow">Trading Hub</p>
                        <h2 class="hero-title">Bulk. Better. Together.</h2>
                        <p class="hero-subtitle">Commodities and wholesale deals you can trust</p>
                        <a href="{{ route('trading.products.index') }}" class="hero-cta">Start Trading</a>
                    </div>
                </article>
            @endforelse
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

<main class="trading-main">
    {{-- ==================== 2. TRADING (RTL MARQUEE) ==================== --}}
    <section class="trade-section" id="trading" aria-labelledby="trading-heading">
        <header class="trade-section-intro">
            <h1 id="trading-heading" class="trade-section-title">Trading</h1>
            <p class="trade-section-subtitle">
                Bulk Trading. Better Value. Stronger Together.
            </p>
        </header>

        <div class="marquee marquee--rtl" data-marquee data-direction="rtl" aria-label="Trading categories carousel">
            <button type="button" class="marquee-arrow marquee-arrow-prev" aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="marquee-arrow marquee-arrow-next" aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="marquee-viewport">
                <div class="marquee-track" data-marquee-track>
                    @php
                        $tcStyleMap = [
                            0 => ['class' => 'trade-card--grains', 'icon' => 'fa-wheat-awn', 'def_img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80'],
                            1 => ['class' => 'trade-card--agro', 'icon' => 'fa-seedling', 'def_img' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80'],
                            2 => ['class' => 'trade-card--electronics', 'icon' => 'fa-desktop', 'def_img' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80'],
                            3 => ['class' => 'trade-card--home', 'icon' => 'fa-couch', 'def_img' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80'],
                            4 => ['class' => 'trade-card--spices', 'icon' => 'fa-mortar-pestle', 'def_img' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80'],
                            5 => ['class' => 'trade-card--packaging', 'icon' => 'fa-box', 'def_img' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=600&q=80'],
                        ];
                    @endphp

                    @foreach($tradingCategories as $index => $cat)
                        @php
                            $style = $tcStyleMap[$index % 6];
                            $icon = $cat->icon ?: $style['icon'];
                            $catImg = $cat->image ? $resolveImg($cat->image) : $style['def_img'];
                        @endphp
                        <article class="trade-card {{ $style['class'] }}">
                            <span class="trade-card-icon" aria-hidden="true">
                                <i class="fa-solid {{ $icon }}"></i>
                            </span>
                            <div class="trade-card-copy">
                                <h2>{{ $cat->name }}</h2>
                                <p>{{ $cat->description ?: 'Quality products in bulk, delivered with trust' }}</p>
                                <a href="{{ route('trading.products.index', ['cat' => $cat->slug]) }}" class="trade-card-cta">
                                    Trade Now <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                            <img class="trade-card-img" src="{{ $catImg }}" alt="{{ $cat->name }}" />
                        </article>
                    @endforeach
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

    {{-- ==================== 3. MARKET PLACE (LTR MARQUEE) ==================== --}}
    <section class="trade-section" id="marketplace" aria-labelledby="marketplace-heading">
        <header class="trade-section-intro">
            <h2 id="marketplace-heading" class="trade-section-title">
                Market Place
            </h2>
            <p class="trade-section-subtitle">
                Discover deals across top categories
            </p>
        </header>

        <div class="marquee marquee--ltr" data-marquee data-direction="ltr" aria-label="Marketplace deals carousel">
            <button type="button" class="marquee-arrow marquee-arrow-prev" aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" class="marquee-arrow marquee-arrow-next" aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="marquee-viewport">
                <div class="marquee-track" data-marquee-track>
                    @php
                        $mcClasses = [
                            0 => 'market-card--luggage',
                            1 => 'market-card--watches',
                            2 => 'market-card--jewellery',
                            3 => 'market-card--kindle',
                            4 => 'market-card--fashion',
                            5 => 'market-card--beauty',
                        ];
                    @endphp

                    @foreach($marketplaceProducts as $index => $p)
                        @php
                            $cardClass = $mcClasses[$index % 6];
                            $price = $p->defaultVariant?->price ?: $p->min_price;
                            $mrp = $p->defaultVariant?->mrp ?: $p->max_price;
                            $firstImg = $p->images->first()?->path;
                            $productImg = $firstImg ? $resolveImg($firstImg) : asset('assets/images/no-image.avif');
                        @endphp
                        <article class="market-card {{ $cardClass }}">
                            <div class="market-card-copy">
                                <h3>{{ $p->title }}</h3>
                                <p>{{ $p->short_description ?: 'Discover premium collection and top brands' }}</p>

                                @if($p->offer_text)
                                    <strong class="market-card-offer">{{ $p->offer_text }}</strong>
                                @elseif($price)
                                    <p class="market-card-price">
                                        <strong>₹{{ number_format($price) }}</strong>
                                        @if($mrp && $mrp > $price)
                                            <span>₹{{ number_format($mrp) }}</span>
                                        @endif
                                    </p>
                                @endif

                                <a href="{{ route('products.show', $p->slug) }}" class="market-card-cta">
                                    Shop Now <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                            <img class="market-card-img" src="{{ $productImg }}" alt="{{ $p->title }}" />
                        </article>
                    @endforeach
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

    {{-- ==================== 4. TRADING / MARKETPLACE TABS ==================== --}}
    <div class="trading-mode-tabs" role="tablist" aria-label="Trading and Marketplace">
        <button type="button" class="trading-mode-tab is-active" id="tab-marketplace" role="tab" aria-selected="true" data-mode="marketplace">
            <span class="trading-mode-tab-icon" aria-hidden="true">
                <i class="fa-solid fa-store"></i>
            </span>
            <span class="trading-mode-tab-label">Marketplace</span>
        </button>
        <button type="button" class="trading-mode-tab" id="tab-trading" role="tab" aria-selected="false" data-mode="trading">
            <span class="trading-mode-tab-icon" aria-hidden="true">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </span>
            <span class="trading-mode-tab-label">Trading</span>
        </button>
    </div>

    {{-- ==================== 5. BROWSE BY CATEGORY ==================== --}}
    <section class="gallery-block" id="browse-category" aria-labelledby="browse-heading">
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
            @php
                $catBgs = ['#e8eef5', '#eef2f6', '#e8eef5', '#eaf4fb', '#eef6fb', '#ebe6df', '#e8f0ea', '#eef0f4', '#eaf3e6', '#fbf6e4'];
                $defaultCatImages = [
                    'electronics' => [
                        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=200&q=80',
                    ],
                    'tvs-appliances' => [
                        'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=200&q=80',
                    ],
                    'men' => [
                        'https://images.unsplash.com/photo-1617137968427-85924c800a22?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=200&q=80',
                        asset('assets/images/glass2.jpg'),
                        asset('assets/images/men-outfit.jpg'),
                    ],
                    'women' => [
                        'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=200&q=80',
                        asset('assets/images/women-outfit.jpg'),
                    ],
                    'baby-kids' => [
                        'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1519689373023-dd07c7988603?auto=format&fit=crop&w=200&q=80',
                    ],
                    'home-furniture' => [
                        'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=200&q=80',
                    ],
                    'sports-books-more' => [
                        asset('assets/images/sp.jpg'),
                        'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=200&q=80',
                        asset('assets/images/bk.jpg'),
                    ],
                    'farm-garden' => [
                        'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1592419044706-39796d40f98c?auto=format&fit=crop&w=200&q=80',
                        asset('assets/images/plant.jpg'),
                    ],
                    'food-items' => [
                        'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=200&q=80',
                    ],
                ];
            @endphp

            @foreach($categories as $index => $cat)
                @php
                    $bg = $catBgs[$index % count($catBgs)];
                    $catImages = [];
                    if ($cat->children && $cat->children->isNotEmpty()) {
                        foreach ($cat->children->take(4) as $child) {
                            if ($child->image) {
                                $catImages[] = $resolveImg($child->image);
                            }
                        }
                    }
                    $fallbackList = $defaultCatImages[$cat->slug] ?? [
                        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=200&q=80',
                        'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80',
                    ];
                    while (count($catImages) < 4) {
                        $idx = count($catImages);
                        $catImages[] = $fallbackList[$idx % count($fallbackList)];
                    }
                @endphp
                <a href="{{ route('products.index', ['cat' => $cat->slug]) }}" class="category-gallery-card" style="--cat-bg: {{ $bg }}">
                    <div class="category-gallery-media">
                        @foreach(array_slice($catImages, 0, 4) as $imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $cat->name }}" loading="lazy" />
                        @endforeach
                    </div>
                    <div class="category-gallery-footer">
                        <strong>{{ $cat->name }}</strong>
                        <span>Explore <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ==================== 6. BESTSELLERS ==================== --}}
    <section class="gallery-block" id="bestsellers" aria-labelledby="bestsellers-heading">
        <header class="gallery-block-header gallery-block-header--row">
            <div>
                <h2 id="bestsellers-heading" class="gallery-block-title">
                    Bestsellers
                </h2>
                <p class="gallery-block-subtitle">
                    Top picks loved by our customers
                </p>
            </div>
            <a href="{{ route('products.index', ['sort' => 'rating']) }}" class="gallery-view-all">
                View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </header>

        <div class="bestseller-grid">
            @foreach($bestsellers as $b)
                @php
                    $price = $b->defaultVariant?->price ?: $b->min_price;
                    $mrp = $b->defaultVariant?->mrp ?: $b->max_price;
                    $firstImg = $b->images->first()?->path;
                    $img = $firstImg ? $resolveImg($firstImg) : asset('assets/images/no-image.avif');
                    $discount = ($mrp && $price && $mrp > $price) ? round((($mrp - $price) / $mrp) * 100) : 0;
                    $rating = $b->rating_avg ?: 4.5;
                    $count = $b->rating_count ?: 1200;
                    $countFmt = $count >= 1000 ? number_format($count / 1000, 1) . 'k' : number_format($count);
                @endphp
                <article class="bestseller-card">
                    <div class="bestseller-media">
                        <a href="{{ route('products.show', $b->slug) }}">
                            <img src="{{ $img }}" alt="{{ $b->title }}" loading="lazy" />
                        </a>
                    </div>
                    <h3>
                        <a href="{{ route('products.show', $b->slug) }}" style="text-decoration:none;color:inherit;">
                            {{ $b->title }}
                        </a>
                    </h3>
                    <p>{{ $b->short_description ?: ($b->category?->name ?: 'Top pick') }}</p>
                    <div class="bestseller-rating">
                        <i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format($rating, 1) }}
                        <span>({{ $countFmt }})</span>
                    </div>
                    <div class="bestseller-price">
                        <strong>₹{{ number_format($price) }}</strong>
                        @if($mrp && $mrp > $price)
                            <s>₹{{ number_format($mrp) }}</s>
                            @if($discount > 0)<em>{{ $discount }}% OFF</em>@endif
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ==================== 7. SIMILAR TO YOUR SEARCHES ==================== --}}
    <section class="gallery-block" id="similar-searches" aria-labelledby="similar-heading">
        <header class="gallery-block-header gallery-block-header--row">
            <div>
                <h2 id="similar-heading" class="gallery-block-title">
                    Similar to your searches
                </h2>
                <p class="gallery-block-subtitle">Handpicked just for you</p>
            </div>
            <a href="{{ route('products.index') }}" class="gallery-view-all">
                View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </header>

        <div class="similar-rows">
            @foreach($similarSearches as $row)
                <div class="similar-row">
                    <a href="{{ $row['url'] }}" class="similar-lead" style="--lead-bg: {{ $row['lead_bg'] }}; --lead-color: {{ $row['lead_color'] }};">
                        <h3>{{ $row['title'] }}</h3>
                        <p>{{ $row['subtitle'] }}</p>
                        <span>Explore now <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </a>

                    @php
                        $products = $row['products'] ?? collect();
                        $fallbacks = $row['fallbacks'] ?? [];
                    @endphp

                    @for($slot = 0; $slot < 3; $slot++)
                        @if(isset($products[$slot]))
                            @php
                                $prod = $products[$slot];
                                $pImg = $prod->images->first()?->path ? $resolveImg($prod->images->first()->path) : ($fallbacks[$slot]['url'] ?? asset('assets/images/no-image.avif'));
                            @endphp
                            <a href="{{ route('products.show', $prod->slug) }}" class="similar-photo">
                                <img src="{{ $pImg }}" alt="{{ $prod->title }}" loading="lazy" />
                            </a>
                        @elseif(isset($fallbacks[$slot]))
                            <a href="{{ $row['url'] }}" class="similar-photo">
                                <img src="{{ $fallbacks[$slot]['url'] }}" alt="{{ $fallbacks[$slot]['alt'] }}" loading="lazy" />
                            </a>
                        @endif
                    @endfor
                </div>
            @endforeach
        </div>
    </section>
</main>
@endsection
