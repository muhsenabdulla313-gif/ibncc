
@extends('layouts.trading')
@php
    use Illuminate\Support\Str;

    $isRetail  = $mode === 'retail';
    $pageTitle = $selectedCategory?->name ?? ($isRetail ? 'Products' : 'Trading Products');
    $noImage   = asset('assets/images/no-image.avif');
    $indexUrl  = $isRetail ? route('products.index') : route('trading.products.index');

    // Indian grouping, trailing zeros trimmed (128680 → 1,28,680 | 10.50 → 10.5)
    $inr = function ($n, int $dec = 2): string {
        $n = round((float) $n, $dec);
        [$int, $frac] = array_pad(explode('.', number_format(abs($n), $dec, '.', '')), 2, '');
        $frac = rtrim($frac, '0');
        $last = substr($int, -3);
        $rest = substr($int, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',';
        }
        return $rest . $last . ($frac !== '' ? '.' . $frac : '');
    };
    $qty = fn ($q) => rtrim(rtrim(number_format((float) $q, 3, '.', ''), '0'), '.');

    // attribute id → key used in data-filter="…" and data-… on the cards (lowercase, like the HTML)
    $attrKey = [];
    foreach ($filters as $f) {
        $attrKey[$f->id] = str_replace('-', '', Str::lower($f->slug));
    }

    $items = $products instanceof \Illuminate\Pagination\AbstractPaginator
        ? $products->getCollection() : collect($products);
    $total = method_exists($products, 'total') ? $products->total() : $items->count();

    // ---- precompute every card once --------------------------------------
    $cards = $items->map(function ($product) use ($isRetail, $attrKey, $qty, $inr) {
        $variants = $product->variants->where('is_active', true);
        $variant  = $variants->firstWhere('is_default', true) ?? $variants->first();
        $image    = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

        $price = $variant?->price;
        $mrp   = $variant?->mrp;
        $tiers = $product->priceTiers->where('price_type', 'wholesale');
        $low   = $tiers->min('price') ?? $price;
        $high  = $tiers->max('price') ?? $price;
        $disc  = ($isRetail && $mrp && $price && $mrp > $price) ? (int) round((($mrp - $price) / $mrp) * 100) : 0;

        $avail = $variants->sum(fn ($v) => max(0, (float) $v->stock - (float) $v->reserved_stock));
        $inStock = $product->track_stock ? $avail > 0 : true;

        // cat / group / item like the HTML (root slug, 2nd level name, 3rd level name)
        $chain = [];
        for ($c = $product->category; $c; $c = $c->parent) { array_unshift($chain, $c); }
        $catSlug = $chain[0]->slug ?? '';
        $group   = $chain[1]->name ?? ($chain[0]->name ?? '');
        $item    = isset($chain[2]) ? $chain[2]->name : '';

        $attrs = [];
        if ($product->relationLoaded('attributeValues')) {
            foreach ($product->attributeValues as $v) {
                if (isset($attrKey[$v->attribute_id]) && $v->option) {
                    $attrs[$attrKey[$v->attribute_id]][] = $v->option->label;
                }
            }
        }

        return [
            'p' => $product, 'variant' => $variant, 'image' => $image,
            'price' => $price, 'mrp' => $mrp, 'low' => $low, 'high' => $high, 'disc' => $disc,
            'inStock' => $inStock, 'cat' => $catSlug, 'group' => $group, 'item' => $item, 'attrs' => $attrs,
            'specs' => $product->short_description
                ? Str::limit(strip_tags($product->short_description), 90)
                : collect([$product->brand?->name, $product->model_no])->filter()->implode(', '),
            'rating' => rtrim(rtrim(number_format((float) $product->rating_avg, 1), '0'), '.'),
            'url' => $isRetail ? route('products.show', $product->slug) : route('trading.products.show', $product->slug),
        ];
    });

    // price slider bounds from the listed products
    $shown = $cards->map(fn ($c) => $isRetail ? $c['price'] : $c['high'])->filter();
    $bMin  = $shown->isNotEmpty() ? (int) floor($cards->map(fn ($c) => $isRetail ? $c['price'] : $c['low'])->filter()->min()) : 0;
    $bMax  = $shown->isNotEmpty() ? (int) ceil($shown->max()) : 1000;
    $step  = max(1, (int) round(($bMax - $bMin) / 100));

    $subcatBgs = ['#e8eef5', '#eef2f6', '#eaf4fb', '#eef6fb', '#e8f0ea', '#eef0f4', '#eaf3e6', '#fbf6e4'];
@endphp

@section('title')
    {{ $pageTitle }} — CC Hub
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}">
    @unless($isRetail)
        <link rel="stylesheet" href="{{ asset('css/cchub/trading-product-list.css') }}">
    @endunless
@endpush

@section('page-class', 'product-list-page' . ($isRetail ? '' : ' trading-product-list-page'))

@section('body')
@include('trading.navbar')

<main class="pl-main">
    <div class="pl-layout">

        <aside class="pl-sidebar" id="plSidebar" aria-label="Product filters">
            <header class="pl-sidebar-head">
                <h2>Filters</h2>
                <button type="button" class="pl-clear-btn" id="plClearAll">Clear all</button>
                <button type="button" class="pl-sidebar-close" id="plSidebarClose" aria-label="Close filters">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="pl-filters" id="plFilters">
                <div class="pl-filter-set" data-preset="default" data-is-fallback="true">

                    @if($brands->isNotEmpty())
                        <details class="pl-filter-group" open>
                            <summary class="pl-filter-summary">
                                Brand <i class="fa-solid fa-chevron-down"></i>
                            </summary>
                            <div class="pl-filter-body">
                                @foreach($brands as $brand)
                                    <label class="pl-filter-check"><input type="checkbox" data-filter="brand" value="{{ $brand->name }}" /><span>{{ $brand->name }}</span></label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @foreach($filters as $filter)
                        @if($filter->options->isNotEmpty())
                            <details class="pl-filter-group" open>
                                <summary class="pl-filter-summary">
                                    {{ $filter->name }} <i class="fa-solid fa-chevron-down"></i>
                                </summary>
                                <div class="pl-filter-body">
                                    @foreach($filter->options as $option)
                                        <label class="pl-filter-check"><input type="checkbox" data-filter="{{ $attrKey[$filter->id] }}" value="{{ $option->label }}" /><span>{{ $option->label }}</span></label>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endforeach

                    <details class="pl-filter-group" open>
                        <summary class="pl-filter-summary">
                            Price Range <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="pl-filter-body pl-price-range">
                            <input type="range" min="{{ $bMin }}" max="{{ $bMax }}" step="{{ $step }}" value="{{ $bMax }}" data-filter="price" data-range="max" />
                            <div class="pl-price-labels">
                                <span>₹{{ $inr($bMin, 0) }}</span><span>₹{{ $inr($bMax, 0) }}</span>
                            </div>
                        </div>
                    </details>

                    <details class="pl-filter-group" open>
                        <summary class="pl-filter-summary">
                            Discount <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="pl-filter-body">
                            @foreach([10, 20, 30, 40] as $d)
                                <label class="pl-filter-check"><input type="checkbox" data-filter="discount" value="{{ $d }}% and above" /><span>{{ $d }}% and above</span></label>
                            @endforeach
                        </div>
                    </details>

                    <details class="pl-filter-group" open>
                        <summary class="pl-filter-summary">
                            Customer Ratings <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="pl-filter-body">
                            <label class="pl-filter-check"><input type="checkbox" data-filter="rating" value="4★ &amp; above" /><span>4★ &amp; above</span></label>
                            <label class="pl-filter-check"><input type="checkbox" data-filter="rating" value="3★ &amp; above" /><span>3★ &amp; above</span></label>
                        </div>
                    </details>

                    <details class="pl-filter-group" open>
                        <summary class="pl-filter-summary">
                            Availability <i class="fa-solid fa-chevron-down"></i>
                        </summary>
                        <div class="pl-filter-body">
                            <label class="pl-filter-check"><input type="checkbox" data-filter="availability" value="In Stock" /><span>In Stock</span></label>
                            <label class="pl-filter-check"><input type="checkbox" data-filter="availability" value="Out of Stock" /><span>Out of Stock</span></label>
                        </div>
                    </details>
                </div>
            </div>
        </aside>

        <div class="pl-content">
            <nav class="pl-breadcrumb" id="plBreadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a><span aria-hidden="true">›</span>
                <span aria-current="page">{{ $pageTitle }}</span>
            </nav>

            @if($isRetail)
                <section class="pl-subcat-gallery" id="plSubcatGallery" aria-label="Shop by subcategory" hidden>
                    @foreach($categories as $category)
                        @if($category->children->isNotEmpty())
                            <div class="pl-subcat-set" data-cat="{{ $category->slug }}" hidden>
                                @foreach($category->children as $child)
                                    @php
                                        $imgs = collect($child->preview_images ?? [])->filter()->take(4)->values();
                                        if ($imgs->isEmpty()) {
                                            $imgs = collect([$child->image ? asset('storage/' . $child->image) : $noImage]);
                                        }
                                    @endphp
                                    <a href="{{ $indexUrl }}?{{ http_build_query(['cat' => $category->slug, 'group' => $child->name]) }}" class="pl-subcat-card" style="--card-bg:{{ $subcatBgs[$loop->index % count($subcatBgs)] }}">
                                        <div class="pl-subcat-media">
                                            @foreach($imgs as $src)
                                                <img src="{{ $src }}" alt="" loading="lazy" width="100" height="100" />
                                            @endforeach
                                        </div>
                                        <div class="pl-subcat-footer">
                                            <strong>{{ $child->name }}</strong>
                                            <span>Explore <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </section>
            @endif

            <header class="pl-head">
                <h1 id="plTitle">{{ $pageTitle }}</h1>
                <p class="pl-count" id="plCount">(Showing {{ $total }} products)</p>
            </header>

            <div class="pl-toolbar">
                <div class="pl-applied-wrap">
                    <span class="pl-applied-label">Sorted By / Applied Filters</span>
                    <div class="pl-applied" id="plApplied">
                        <span class="pl-applied-empty">No filters applied</span>
                    </div>
                </div>
                <div class="pl-sort-wrap">
                    <span class="pl-sort-label">Sort By</span>
                    <div class="pl-sort-tabs" id="plSortTabs" role="tablist">
                        <button type="button" class="pl-sort-tab" data-sort="relevance" role="tab">Relevance</button>
                        <button type="button" class="pl-sort-tab is-active" data-sort="popularity" role="tab">Popularity</button>
                        <button type="button" class="pl-sort-tab" data-sort="price-asc" role="tab">Price — Low to High</button>
                        <button type="button" class="pl-sort-tab" data-sort="price-desc" role="tab">Price — High to Low</button>
                        <button type="button" class="pl-sort-tab" data-sort="rating" role="tab">Customer Rating</button>
                        <button type="button" class="pl-sort-tab" data-sort="newest" role="tab">Newest</button>
                    </div>
                </div>
            </div>

            <div class="pl-grid" id="plGrid" aria-live="polite">
                @foreach($cards as $c)
                    @php $product = $c['p']; @endphp
                    <article
                        class="pl-card"
                        data-id="{{ $product->id }}"
                        data-cat="{{ $c['cat'] }}"
                        data-group="{{ $c['group'] }}"
                        data-item="{{ $c['item'] }}"
                        data-brand="{{ $product->brand?->name }}"
                        data-price="{{ $isRetail ? $c['price'] : $c['low'] }}"
                        @unless($isRetail) data-price-max="{{ $c['high'] }}" data-moq="{{ $qty($product->min_order_qty) }}" @endunless
                        data-mrp="{{ $isRetail ? $c['mrp'] : $c['high'] }}"
                        data-discount="{{ $c['disc'] }}"
                        data-rating="{{ $product->rating_avg + 0 }}"
                        data-reviews="{{ $product->rating_count }}"
                        data-availability="{{ $c['inStock'] ? 'In Stock' : 'Out of Stock' }}"
                        @foreach($c['attrs'] as $k => $vals) data-{{ $k }}="{{ implode(', ', array_unique($vals)) }}" @endforeach
                        @unless($isRetail) data-categories="Wholesale" @endunless
                    >
                        <button type="button" class="pl-wishlist" data-wish="{{ $product->id }}" aria-label="Add to wishlist">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                        <div class="pl-card-img-wrap">
                            <img src="{{ $c['image'] ? asset('storage/' . $c['image']->path) : $noImage }}" alt="{{ $c['image']?->alt ?? $product->title }}" loading="lazy" />
                        </div>
                        @if($isRetail && $product->is_featured)
                            <span class="pl-sponsored">Sponsored</span>
                        @endif

                        <h3 class="pl-card-title">
                            <a href="{{ $c['url'] }}">@if(! $isRetail && ($product->is_assured || $product->offer_text))<span class="pl-super-badge" aria-label="Super"><i class="fa-solid fa-bolt" aria-hidden="true"></i> {{ $product->offer_text ?: 'Super' }}</span> @endif{{ $product->title }}</a>
                        </h3>
                        @if($c['specs'])
                            <p class="pl-card-specs">{{ $c['specs'] }}</p>
                        @endif
                        <div class="pl-rating-row">
                            <span class="pl-rating-badge">{{ $c['rating'] }} <i class="fa-solid fa-star"></i></span>
                            <span class="pl-reviews">({{ $inr($product->rating_count, 0) }})</span>
                        </div>
                        <div class="pl-price-row">
                            @if($isRetail)
                                <span class="pl-price">₹{{ $inr($c['price']) }}</span>
                                @if($c['disc'] > 0)
                                    <span class="pl-mrp">₹{{ $inr($c['mrp']) }}</span>
                                    <span class="pl-discount">{{ $c['disc'] }}% off</span>
                                @endif
                            @else
                                <span class="pl-price">₹{{ $inr($c['low']) }}@if($c['high'] && $c['high'] != $c['low'])-{{ $inr($c['high']) }}@endif</span>
                                <span class="pl-moq">MOQ: {{ $qty($product->min_order_qty) }}</span>
                            @endif
                        </div>
                        @if($isRetail)
                            <p class="pl-stock {{ $c['inStock'] ? 'is-in' : 'is-out' }}">{{ $c['inStock'] ? 'In Stock' : 'Out of Stock' }}</p>
                        @endif

                        <div class="pl-card-actions">
                            <button type="button" class="pl-btn-cart" data-cart="{{ $product->id }}">Add to Cart</button>
                            <a class="pl-btn-view" href="{{ $c['url'] }}">View Details</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="pl-empty" id="plEmpty" @if($cards->isNotEmpty()) hidden @endif>
                No products match your filters. Try adjusting or clearing filters.
            </p>

            @if($products instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="pl-pagination">{{ $products->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>

    <button type="button" class="pl-filters-fab" id="plFiltersFab">
        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
        Filters
    </button>
    <div class="pl-sidebar-backdrop" id="plSidebarBackdrop" hidden></div>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/cchub/ch-mega-menu.js') }}"></script>
    <script src="{{ asset('js/cchub/ch-login.js') }}"></script>
    <script src="{{ asset('js/cchub/ch-product-list.js') }}"></script>
@endpush