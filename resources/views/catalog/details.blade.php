@extends('layouts.trading')

@php
    $noImg = asset('assets/images/no-image.avif');

    // Safe image resolver: handles absolute URLs, public/uploads directly, or storage fallback
    $resolveImg = function ($path) use ($noImg) {
        if (!$path) {
            return $noImg;
        }
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }
        $clean = ltrim($path, '/\\');
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }
        // Fallback to asset directly or noImg if missing
        return file_exists(public_path($clean)) ? asset($clean) : $noImg;
    };

    $title = $product->title ?? $product->name ?? 'Product';
    $isRetail = ($mode === 'retail');

    /* ---------- Images ---------- */
    $allImages = $product->images->sortBy('sort_order')->values();

    $imageData = $allImages->map(fn ($img) => [
        'id'         => $img->id,
        'url'        => $resolveImg($img->path),
        'option_id'  => $img->attribute_option_id ? (int) $img->attribute_option_id : null,
        'variant_id' => $img->variant_id ? (int) $img->variant_id : null,
        'is_primary' => (bool) $img->is_primary,
    ])->values()->all();

    // General images (not tied to any specific variant option)
    $generalImages = collect($imageData)->filter(fn ($i) => is_null($i['option_id']) && is_null($i['variant_id']))->values();
    $fallbackImages = $generalImages->isNotEmpty()
        ? $generalImages->pluck('url')->unique()->values()->all()
        : (collect($imageData)->pluck('url')->unique()->values()->all() ?: [$noImg]);

    /* ---------- Variants & Axes ---------- */
    $variants = $product->variants->where('is_active', true)->values();

    $axes = $variants
        ->flatMap(fn ($variant) => $variant->attributeValues)
        ->filter(fn ($value) =>
            $value->attribute &&
            $value->attribute->is_variant_axis &&
            $value->option
        )
        ->groupBy('attribute_id')
        ->map(fn ($rows) => [
            'attribute' => $rows->first()->attribute,
            'options'   => $rows->pluck('option')->unique('id')->sortBy('sort_order')->values(),
        ]);

    // Identify color axis (or default image axis)
    $colorAxis = $axes->first(function ($axis) {
        return strtolower($axis['attribute']->name) === 'color' || $axis['attribute']->input_type === 'color';
    }) ?? $axes->first();
    $colorAxisId = $colorAxis ? (int) $colorAxis['attribute']->id : null;

    /* ---------- Selected Default Variant ---------- */
    $selectedVariant = $variants->firstWhere('is_default', true) ?? $variants->first();

    $selectedOptions = $selectedVariant
        ? $selectedVariant->attributeValues->pluck('attribute_option_id', 'attribute_id')
            ->mapWithKeys(fn ($optId, $attrId) => [(string) $attrId => (int) $optId])
            ->all()
        : [];

    /* ---------- Quantity & Stock Limits ---------- */
    $minQty = $isRetail ? 1 : max(1, (int) ($product->min_order_qty ?? 1));
    $maxOrderQty = $product->max_order_qty ? (int) $product->max_order_qty : null;

    $availStock = $selectedVariant
        ? max(0, (int) $selectedVariant->stock - (int) ($selectedVariant->reserved_stock ?? 0))
        : 0;
    $isInStock = $product->track_stock ? ($availStock > 0 && $availStock >= $minQty) : true;

    /* ---------- Wholesale Price Tiers ---------- */
    $wholesaleTiers = $product->priceTiers
        ->where('price_type', 'wholesale')
        ->sortBy('min_qty')
        ->values();

    /* ---------- Initial Pricing ---------- */
    if ($isRetail) {
        $startPrice = (float) ($selectedVariant?->price ?? 0);
        $startMrp   = (float) ($selectedVariant?->mrp ?? 0);
        $startDiscount = ($startMrp > $startPrice && $startMrp > 0)
            ? (int) round((($startMrp - $startPrice) / $startMrp) * 100)
            : 0;
    } else {
        $matchingTier = $wholesaleTiers->first(function ($t) use ($minQty) {
            return $t->min_qty <= $minQty && (is_null($t->max_qty) || $t->max_qty >= $minQty);
        }) ?? $wholesaleTiers->first();
        $startPrice = $matchingTier ? (float) $matchingTier->price : (float) ($product->priceTiers->min('price') ?? 0);
        $startMrp   = 0;
        $startDiscount = 0;
    }

    /* ---------- Initial Gallery Images ---------- */
    $selectedColorOptionId = $colorAxisId ? ($selectedOptions[(string) $colorAxisId] ?? null) : null;
    $initialImages = [];
    if ($selectedColorOptionId) {
        $initialImages = collect($imageData)->filter(fn ($i) => $i['option_id'] === (int) $selectedColorOptionId)->pluck('url')->all();
    }
    if (empty($initialImages) && $selectedVariant) {
        $initialImages = collect($imageData)->filter(fn ($i) => $i['variant_id'] === (int) $selectedVariant->id)->pluck('url')->all();
    }
    if (empty($initialImages)) {
        $initialImages = $fallbackImages;
    }
    if (empty($initialImages)) {
        $initialImages = [$noImg];
    }

    /* ---------- JavaScript Data Payloads ---------- */
    $variantData = $variants->map(function ($v) use ($product, $imageData, $fallbackImages, $colorAxisId) {
        $optIds = $v->attributeValues->pluck('attribute_option_id', 'attribute_id')
            ->mapWithKeys(fn ($optId, $attrId) => [(string) $attrId => (int) $optId])
            ->all();

        $colorOptId = $colorAxisId ? ($optIds[(string) $colorAxisId] ?? null) : null;

        $variantImages = [];
        if ($colorOptId) {
            $variantImages = collect($imageData)->filter(fn ($i) => $i['option_id'] === (int) $colorOptId)->pluck('url')->all();
        }
        if (empty($variantImages)) {
            $variantImages = collect($imageData)->filter(fn ($i) => $i['variant_id'] === (int) $v->id)->pluck('url')->all();
        }
        if (empty($variantImages)) {
            $variantImages = $fallbackImages;
        }

        $stock = max(0, (int) $v->stock - (int) ($v->reserved_stock ?? 0));
        $mrp = (float) ($v->mrp ?? 0);
        $price = (float) ($v->price ?? 0);
        $disc = ($mrp > $price && $mrp > 0) ? (int) round((($mrp - $price) / $mrp) * 100) : 0;

        return [
            'id'               => $v->id,
            'sku'              => $v->sku,
            'price'            => $price,
            'mrp'              => $mrp,
            'discount_percent' => $disc,
            'stock'            => $stock,
            'track_stock'      => (bool) $product->track_stock,
            'options'          => $optIds,
            'images'           => array_values(array_unique($variantImages)),
        ];
    })->values()->all();

    $tiersData = $wholesaleTiers->map(fn ($t) => [
        'id'         => $t->id,
        'variant_id' => $t->variant_id ? (int) $t->variant_id : null,
        'min_qty'    => (float) $t->min_qty,
        'max_qty'    => $t->max_qty !== null ? (float) $t->max_qty : null,
        'price'      => (float) $t->price,
    ])->values()->all();

    /* ---------- Specifications Sheet ---------- */
    $basicSpecs = array_filter([
        'Brand'     => $product->brand?->name,
        'Model'     => $product->model_no ?? null,
        'Category'  => $product->category?->name,
        'Unit'      => $product->unit?->name,
        'HSN Code'  => $product->hsn_code ?? null,
    ]);

    $specs = $product->attributeValues
        ->filter(fn ($pav) => $pav->attribute && !$pav->attribute->is_variant_axis)
        ->groupBy('attribute_id')
        ->map(function ($rows) {
            $attribute = $rows->first()->attribute;
            $values = $rows->map->display_value->filter()->unique()->implode(', ');
            return [
                'name'  => $attribute->name,
                'value' => $values,
            ];
        })
        ->filter(fn ($spec) => $spec['value'] !== '')
        ->values();

    $hasRating = (int) ($product->rating_count ?? 0) > 0;
@endphp

@section('title')
    {{ $title }} — CC Hub
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@300;400;500;600;700&family=Manrope:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-details.css') }}">

<style>
    .pd-gallery-set[hidden] { display: none !important; }
    .pd-prop-group {
        margin: 16px 0 0;
        padding: 16px 16px 18px;
        background: #f4f7fb;
        border: 1px solid #e3e9f2;
        border-radius: 14px;
    }
    .pd-prop-title {
        display: block;
        margin-bottom: 12px;
        font-size: 15px;
        font-weight: 600;
        color: #0f2a4a;
    }
    .pd-prop-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pd-prop-option {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 9px 18px 9px 14px;
        background: #fff;
        border: 1px solid #dde4ee;
        border-radius: 999px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #0f2a4a;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .pd-prop-option input {
        margin: 0;
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
        flex-shrink: 0;
    }
    .pd-prop-option span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }
    .pd-prop-option:hover { border-color: #93b4f5; }
    .pd-prop-option:has(input:checked) {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
    }
    .pd-prop-option:has(input:disabled) {
        opacity: .45;
        cursor: not-allowed;
        background: #f1f5f9;
        border-style: dashed;
        box-shadow: none;
    }
    .pd-prop-option input:disabled { cursor: not-allowed; }
    .pd-swatch {
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, .25);
        flex-shrink: 0;
    }
    .pd-thumb { cursor: pointer; }
    .pd-thumb.is-active { outline: 2px solid #2563eb; }
    .pd-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pd-channel-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        color: #475569;
        margin-top: 10px;
    }
    .pd-channel-badge a {
        color: #2563eb;
        font-weight: 600;
        text-decoration: underline;
    }
    .pd-tier-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 10px;
        border-radius: 6px;
        margin-top: 4px;
        transition: background .15s ease;
    }
    .pd-tier-item.is-active-tier {
        background: #dbeafe;
        color: #1e40af;
        font-weight: 700;
    }
    .pd-toast-msg {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0f172a;
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: none;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        font-size: 14px;
        animation: fadeInToast 0.25s ease-out;
    }
    @keyframes fadeInToast {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('body')
<main class="pd-main">
    <article class="pd-product" data-id="{{ $product->id }}">

        {{-- BREADCRUMB NAVIGATION --}}
        <nav class="pl-breadcrumb pd-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('trading') }}">Home</a>
            <span aria-hidden="true">›</span>
            <a href="{{ $isRetail ? route('products.index') : route('trading.products.index') }}">
                {{ $isRetail ? 'Products' : 'Trading Products' }}
            </a>
            @if ($product->category)
                @php
                    $catChain = [];
                    for ($c = $product->category; $c; $c = $c->parent) {
                        array_unshift($catChain, $c);
                    }
                @endphp
                @foreach ($catChain as $cNode)
                    <span aria-hidden="true">›</span>
                    <a href="{{ $isRetail ? route('products.index', ['cat' => $cNode->slug]) : route('trading.products.index', ['cat' => $cNode->slug]) }}">
                        {{ $cNode->name }}
                    </a>
                @endforeach
            @endif
            <span aria-hidden="true">›</span>
            <span aria-current="page">{{ $title }}</span>
        </nav>

        {{-- HERO SECTION: GALLERY + INFO --}}
        <section class="pd-hero">

            {{-- =====================================================
                 PRODUCT IMAGE GALLERY
            ====================================================== --}}
            <div class="pd-gallery">
                <div class="pd-gallery-set is-active" id="pdGallery">
                    <div class="pd-main-media">
                        <img class="pd-main-image" id="pdMainImage"
                             src="{{ $initialImages[0] }}"
                             alt="{{ $title }}"
                             onerror="this.onerror=null;this.src='{{ $noImg }}'">
                    </div>

                    <div class="pd-thumb-row">
                        <button type="button" class="pd-thumb-arrow pd-thumb-prev" aria-label="Scroll thumbnails left">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div class="pd-thumbs" id="pdThumbs" aria-label="Product thumbnails">
                            @foreach ($initialImages as $index => $image)
                                <button type="button"
                                        class="pd-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                        data-thumb-src="{{ $image }}"
                                        aria-label="Show image {{ $index + 1 }}">
                                    <img src="{{ $image }}" alt="" onerror="this.onerror=null;this.src='{{ $noImg }}'">
                                </button>
                            @endforeach
                        </div>

                        <button type="button" class="pd-thumb-arrow pd-thumb-next" aria-label="Scroll thumbnails right">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- =====================================================
                 PRODUCT INFORMATION & CONTROLS
            ====================================================== --}}
            <div class="pd-info">

                {{-- BRAND & ACTIONS --}}
                <div class="pd-info-top">
                    <span class="pd-brand">
                        {{ $product->brand?->name ?? '' }}
                    </span>

                    <div class="pd-info-actions">
                        <button type="button" class="pd-icon-btn" id="pdShareBtn" aria-label="Share product">
                            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="pd-icon-btn pd-icon-wish" data-wish="{{ $product->id }}" aria-label="Add to wishlist">
                            <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                {{-- PRODUCT TITLE --}}
                <h1 class="pd-title">{{ $title }}</h1>

                {{-- SHORT DESCRIPTION --}}
                @if ($product->short_description)
                    <p class="pd-specs">{{ $product->short_description }}</p>
                @endif

                {{-- RATINGS & SKU META --}}
                <div class="pd-meta" style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
                    @if ($hasRating)
                        <span class="pd-rating-badge">
                            {{ number_format($product->rating_avg, 1) }}
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <span class="pd-reviews">
                            {{ number_format($product->rating_count) }} Ratings &amp; Reviews
                        </span>
                    @endif

                    <span class="pd-sku-meta" style="font-size: 13px; color: #64748b; margin-left: auto;">
                        SKU: <strong id="pdSkuMeta">{{ $selectedVariant?->sku ?? 'N/A' }}</strong>
                    </span>
                </div>

                {{-- PRICING DISPLAY --}}
                <div class="pd-price-wrap" style="align-items: baseline; gap: 10px;">
                    <span class="pd-price" id="pdPrice">
                        ₹{{ number_format($startPrice, 2) }}
                    </span>

                    @if ($isRetail)
                        <span class="pd-mrp" id="pdMrp" style="{{ ($startMrp > $startPrice && $startMrp > 0) ? '' : 'display: none;' }}">
                            ₹{{ number_format($startMrp, 2) }}
                        </span>
                        <span class="pd-discount" id="pdDiscount" style="{{ $startDiscount > 0 ? '' : 'display: none;' }}">
                            {{ $startDiscount }}% off
                        </span>
                    @else
                        <span class="pd-unit-label" id="pdUnitLabel" style="font-size: 14px; color: #64748b; font-weight: 500;">
                            / {{ $product->unit?->name ?? 'unit' }} (wholesale)
                        </span>
                    @endif
                </div>

                {{-- CROSS-CHANNEL NOTIFICATION IF PRODUCT SUPPORTS BOTH --}}
                @if ($product->sale_type === 'both')
                    <div class="pd-channel-badge">
                        @if ($isRetail)
                            <i class="fa-solid fa-boxes-stacked" style="color: #2563eb;"></i>
                            <span>Wholesale bulk deals available for this product:
                                <a href="{{ route('trading.products.show', $product->slug) }}">View Wholesale Pricing</a>
                            </span>
                        @else
                            <i class="fa-solid fa-bag-shopping" style="color: #2563eb;"></i>
                            <span>Looking for single-unit retail purchase?
                                <a href="{{ route('products.show', $product->slug) }}">Buy at Retail Store</a>
                            </span>
                        @endif
                    </div>
                @endif

                {{-- =====================================================
                     DYNAMIC VARIANT ATTRIBUTES (COLOR, SIZE, ETC.)
                ====================================================== --}}
                @foreach ($axes as $attrId => $axis)
                    @php
                        $attributeName = $axis['attribute']->name;
                        $isColor = strtolower($attributeName) === 'color' || $axis['attribute']->input_type === 'color';
                    @endphp

                    <div class="pd-prop-group">
                        <span class="pd-prop-title">{{ $attributeName }}</span>

                        <div class="pd-prop-options" role="radiogroup" aria-label="{{ $attributeName }}" @if ($isColor) data-gallery-sync="color" @endif>
                            @foreach ($axis['options'] as $option)
                                <label class="pd-prop-option">
                                    <input type="radio"
                                           name="pd-attribute-{{ $product->id }}-{{ $attrId }}"
                                           value="{{ $option->id }}"
                                           data-attribute-id="{{ $attrId }}"
                                           data-option-label="{{ $option->label }}"
                                           @checked((int) ($selectedOptions[(string) $attrId] ?? 0) === (int) $option->id)>
                                    <span>
                                        @if ($isColor && $option->color_hex)
                                            <i class="pd-swatch" style="background-color: {{ $option->color_hex }}" aria-hidden="true"></i>
                                        @endif
                                        {{ $option->label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <input type="hidden" id="selectedVariantId" value="{{ $selectedVariant?->id }}">

                {{-- =====================================================
                     QUANTITY CONTROLS
                ====================================================== --}}
                <div class="pd-qty-row">
                    <span class="pd-qty-label">Quantity:</span>

                    <div class="cart-qty-control" role="group" aria-label="Select quantity">
                        <button type="button" class="cart-qty-btn btn-qty-minus" aria-label="Decrease quantity">
                            <i class="fa-solid fa-minus" aria-hidden="true"></i>
                        </button>
                        <span class="cart-qty-number" id="pdQty" aria-live="polite">{{ $minQty }}</span>
                        <button type="button" class="cart-qty-btn btn-qty-plus" aria-label="Increase quantity">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        </button>
                    </div>

                    <span style="font-size: 14px; color: #475569; font-weight: 500;">
                        {{ $product->unit?->name ?? 'units' }}
                    </span>

                    @if ($minQty > 1)
                        <small style="color: #64748b; font-size: 12px; margin-left: 6px;">
                            (Min: {{ $minQty }})
                        </small>
                    @endif
                </div>

                {{-- =====================================================
                     STOCK STATUS
                ====================================================== --}}
                <p class="pl-stock {{ $isInStock ? 'is-in' : 'is-out' }}" id="pdStock">
                    @if ($isInStock)
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> In Stock
                        @if ($product->track_stock && $availStock > 0 && $availStock <= 5)
                            <small style="margin-left: 8px; color: #d97706; font-weight: 600;">(Only {{ $availStock }} left!)</small>
                        @endif
                    @else
                        <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock
                    @endif
                </p>

                {{-- =====================================================
                     WHOLESALE PRICE TIERS (WHOLESALE MODE)
                ====================================================== --}}
                @if (!$isRetail && $wholesaleTiers->isNotEmpty())
                    <div class="pd-offer" id="pdWholesaleOffers">
                        <div style="font-weight: 700; color: #0f2a4a; margin-bottom: 6px;">
                            <i class="fa-solid fa-tags" aria-hidden="true" style="color: #2563eb;"></i> Wholesale Volume Pricing:
                        </div>
                        <div class="pd-tier-list">
                            @foreach ($wholesaleTiers as $tier)
                                <div class="pd-tier-item {{ ($tier->min_qty <= $minQty && (is_null($tier->max_qty) || $tier->max_qty >= $minQty)) ? 'is-active-tier' : '' }}"
                                     data-min-qty="{{ (float) $tier->min_qty }}"
                                     data-max-qty="{{ $tier->max_qty !== null ? (float) $tier->max_qty : '' }}"
                                     data-price="{{ (float) $tier->price }}"
                                     data-variant-id="{{ $tier->variant_id ?? '' }}">
                                    <span>
                                        {{ $tier->min_qty }}{{ $tier->max_qty ? ' – ' . $tier->max_qty : '+' }} {{ $product->unit?->name ?? 'units' }}
                                    </span>
                                    <span>
                                        ₹{{ number_format($tier->price, 2) }} / unit
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if ($product->min_order_qty)
                            <div style="font-size: 12px; color: #64748b; margin-top: 6px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                                Minimum Order Quantity (MOQ): <strong>{{ $product->min_order_qty }} {{ $product->unit?->name ?? 'units' }}</strong>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- =====================================================
                     PRODUCT ACTIONS: ADD TO CART & BUY NOW
                ====================================================== --}}
                <div class="pd-actions">
                    <button type="button"
                            class="pd-btn pd-btn-cart"
                            id="pdAddCart"
                            data-cart="{{ $product->id }}"
                            @disabled(!$isInStock)>
                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> Add to Cart
                    </button>

                    <button type="button"
                            class="pd-btn pd-btn-buy"
                            id="pdBuyNow"
                            @disabled(!$isInStock)>
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i> Buy Now
                    </button>
                </div>

                {{-- =====================================================
                     PRODUCT SPECIFICATIONS SHEET
                ====================================================== --}}
                <section class="pd-specs-card">
                    <header class="pd-section-head">
                        <h2>Product Details</h2>
                    </header>

                    <div class="pd-spec-grid">
                        <div class="pd-spec-row">
                            <div class="pd-spec-key">SKU</div>
                            <div class="pd-spec-value" id="pdSku">{{ $selectedVariant?->sku ?? 'N/A' }}</div>
                        </div>

                        @foreach ($basicSpecs as $key => $value)
                            <div class="pd-spec-row">
                                <div class="pd-spec-key">{{ $key }}</div>
                                <div class="pd-spec-value">{{ $value }}</div>
                            </div>
                        @endforeach

                        @foreach ($specs as $spec)
                            <div class="pd-spec-row">
                                <div class="pd-spec-key">{{ $spec['name'] }}</div>
                                <div class="pd-spec-value">{{ $spec['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- FULL DESCRIPTION (IF AVAILABLE) --}}
                @if ($product->description && $product->description !== $product->short_description)
                    <section class="pd-specs-card" style="margin-top: 24px;">
                        <header class="pd-section-head">
                            <h2>Description</h2>
                        </header>
                        <div style="padding: 16px 20px; color: #334155; line-height: 1.7; font-size: 14px;">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </section>
                @endif

                {{-- PRODUCT DOCUMENTS (IF AVAILABLE) --}}
                @if ($product->documents && $product->documents->isNotEmpty())
                    <section class="pd-specs-card" style="margin-top: 24px;">
                        <header class="pd-section-head">
                            <h2>Documents &amp; Manuals</h2>
                        </header>
                        <div style="padding: 14px 20px; display: flex; flex-direction: column; gap: 8px;">
                            @foreach ($product->documents as $doc)
                                <a href="{{ asset($doc->path) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 500; text-decoration: none;">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $doc->title }}
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </section>
    </article>
</main>

{{-- Visual Toast Feedback Container --}}
<div class="pd-toast-msg" id="pdToast">
    <i class="fa-solid fa-circle-check" style="color: #22c55e;"></i>
    <span id="pdToastText">Action recorded</span>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const variants       = @json($variantData);
    const imageData      = @json($imageData);
    const fallbackImages = @json($fallbackImages);
    const noImg          = @json($noImg);
    const isRetail       = @json($isRetail);
    const tiers          = @json($tiersData);
    const minimumQty     = @json($minQty);
    const maxOrderQty    = @json($maxOrderQty);
    const colorAxisId    = @json($colorAxisId);

    const main = document.querySelector('.pd-product[data-id="{{ $product->id }}"]');
    if (!main) return;

    const mainImg        = main.querySelector('#pdMainImage');
    const thumbsEl       = main.querySelector('#pdThumbs');
    const priceEl        = main.querySelector('#pdPrice');
    const mrpEl          = main.querySelector('#pdMrp');
    const discountEl     = main.querySelector('#pdDiscount');
    const stockEl        = main.querySelector('#pdStock');
    const idInput        = main.querySelector('#selectedVariantId');
    const skuEl          = main.querySelector('#pdSku');
    const skuMetaEl      = main.querySelector('#pdSkuMeta');
    const cartBtn        = main.querySelector('#pdAddCart');
    const buyBtn         = main.querySelector('#pdBuyNow');
    const qtyEl          = main.querySelector('#pdQty');
    const qtyPlus        = main.querySelector('.btn-qty-plus');
    const qtyMinus       = main.querySelector('.btn-qty-minus');
    const toastEl        = document.getElementById('pdToast');
    const toastText      = document.getElementById('pdToastText');
    const radios         = Array.from(main.querySelectorAll('.pd-prop-option input[data-attribute-id]'));

    let currentGalleryKey = '';

    function showToast(message) {
        if (!toastEl || !toastText) return;
        toastText.textContent = message;
        toastEl.style.display = 'flex';
        clearTimeout(toastEl._timer);
        toastEl._timer = setTimeout(function () {
            toastEl.style.display = 'none';
        }, 3200);
    }

    /* ---------- Selection Helpers ---------- */

    function getSelectedOptions() {
        const selected = {};
        radios.forEach(function (r) {
            if (r.checked) selected[String(r.dataset.attributeId)] = Number(r.value);
        });
        return selected;
    }

    function matchesVariant(variant, selection) {
        return Object.keys(selection).every(function (id) {
            return Number(variant.options[id]) === Number(selection[id]);
        });
    }

    function findVariant(selection) {
        const allAxisIds = [...new Set(radios.map(r => String(r.dataset.attributeId)))];
        if (Object.keys(selection).length !== allAxisIds.length) {
            // Find first variant that matches all currently specified keys
            return variants.find(v => matchesVariant(v, selection)) || null;
        }
        return variants.find(v => matchesVariant(v, selection)) || null;
    }

    function updateAvailableOptions(selection) {
        radios.forEach(function (radio) {
            const attrId = String(radio.dataset.attributeId);
            const optVal = Number(radio.value);

            // Test if keeping all other selections except this axis allows this option
            const test = {};
            Object.keys(selection).forEach(function (k) {
                if (k !== attrId) test[k] = selection[k];
            });
            test[attrId] = optVal;

            radio.disabled = !variants.some(v => matchesVariant(v, test));
        });
    }

    /* ---------- Image Gallery ---------- */

    function renderGallery(images) {
        if (!images || !images.length) images = [noImg];
        const key = JSON.stringify(images);
        if (key === currentGalleryKey) return;
        currentGalleryKey = key;

        mainImg.removeAttribute('srcset');
        mainImg.src = images[0];

        thumbsEl.innerHTML = '';
        images.forEach(function (src, index) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pd-thumb' + (index === 0 ? ' is-active' : '');
            btn.dataset.thumbSrc = src;
            btn.setAttribute('aria-label', 'Show image ' + (index + 1));

            const im = document.createElement('img');
            im.src = src;
            im.alt = '';
            im.onerror = function () { this.onerror = null; this.src = noImg; };

            btn.appendChild(im);
            thumbsEl.appendChild(btn);
        });
    }

    function showGalleryForVariant(variant, selection) {
        let selectedColorOpt = null;
        if (colorAxisId && selection[String(colorAxisId)]) {
            selectedColorOpt = Number(selection[String(colorAxisId)]);
        }

        let images = [];
        // 1. Color option specific images
        if (selectedColorOpt) {
            images = imageData
                .filter(i => i.option_id === selectedColorOpt)
                .map(i => i.url);
        }

        // 2. Variant specific images
        if (!images.length && variant) {
            images = imageData
                .filter(i => i.variant_id === variant.id)
                .map(i => i.url);
        }

        // 3. Fallback to general product images
        if (!images.length) {
            images = fallbackImages;
        }

        // Remove duplicates
        images = Array.from(new Set(images));
        renderGallery(images);
    }

    /* ---------- Stock & Pricing Updates ---------- */

    function getMaxAllowedQuantity(variant) {
        let max = 999999;
        if (maxOrderQty && maxOrderQty > 0) {
            max = maxOrderQty;
        }
        if (variant && variant.track_stock) {
            max = Math.min(max, variant.stock);
        }
        return max;
    }

    function updateStock(variant) {
        const inStock = variant ? (!variant.track_stock || variant.stock >= minimumQty) : false;

        stockEl.className = 'pl-stock ' + (inStock ? 'is-in' : 'is-out');
        if (inStock) {
            let note = '';
            if (variant.track_stock && variant.stock <= 5) {
                note = `<small style="margin-left: 8px; color: #d97706; font-weight: 600;">(Only ${variant.stock} left!)</small>`;
            }
            stockEl.innerHTML = `<i class="fa-solid fa-circle-check" aria-hidden="true"></i> In Stock ${note}`;
        } else {
            stockEl.innerHTML = '<i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock';
        }

        if (cartBtn) cartBtn.disabled = !inStock;
        if (buyBtn)  buyBtn.disabled  = !inStock;
    }

    function updatePrice(variant, qty) {
        if (!priceEl) return;

        if (isRetail) {
            if (!variant || variant.price === null) return;
            priceEl.textContent = '₹' + Number(variant.price).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (mrpEl && discountEl) {
                if (variant.mrp && Number(variant.mrp) > Number(variant.price)) {
                    mrpEl.textContent = '₹' + Number(variant.mrp).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    mrpEl.style.display = '';
                    const disc = Math.round(((variant.mrp - variant.price) / variant.mrp) * 100);
                    discountEl.textContent = disc + '% off';
                    discountEl.style.display = '';
                } else {
                    mrpEl.style.display = 'none';
                    discountEl.style.display = 'none';
                }
            }
        } else {
            // Wholesale tiered pricing based on quantity
            let activeTier = null;
            if (tiers && tiers.length) {
                // First try matching variant-specific tiers
                if (variant) {
                    activeTier = tiers.find(t => t.variant_id === variant.id && t.min_qty <= qty && (!t.max_qty || t.max_qty >= qty));
                }
                // Fallback to general tiers
                if (!activeTier) {
                    activeTier = tiers.find(t => !t.variant_id && t.min_qty <= qty && (!t.max_qty || t.max_qty >= qty));
                }
                // Fallback to highest or lowest tier
                if (!activeTier) {
                    activeTier = tiers.slice().sort((a,b) => a.min_qty - b.min_qty)[0];
                }
            }

            if (activeTier) {
                priceEl.textContent = '₹' + Number(activeTier.price).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Highlight active tier in wholesale list
                document.querySelectorAll('.pd-tier-item').forEach(function (el) {
                    const min = Number(el.dataset.minQty);
                    const max = el.dataset.maxQty !== '' ? Number(el.dataset.maxQty) : null;
                    const match = (min <= qty && (!max || max >= qty));
                    el.classList.toggle('is-active-tier', match);
                });
            }
        }
    }

    /* ---------- Master Update Routine ---------- */

    function updateProduct() {
        let selection = getSelectedOptions();

        // Update disabled state for unavailable combinations
        updateAvailableOptions(selection);

        let variant = findVariant(selection);

        if (!variant) {
            idInput.value = '';
            if (skuEl)     skuEl.textContent     = 'N/A';
            if (skuMetaEl) skuMetaEl.textContent = 'N/A';
            updateStock(null);
            showGalleryForVariant(null, selection);
            return;
        }

        idInput.value = variant.id;
        if (skuEl)     skuEl.textContent     = variant.sku || 'N/A';
        if (skuMetaEl) skuMetaEl.textContent = variant.sku || 'N/A';

        // Clamp quantity to stock limits
        const maxAllowed = getMaxAllowedQuantity(variant);
        let curQty = Number(qtyEl.textContent);
        if (curQty < minimumQty) {
            curQty = minimumQty;
            qtyEl.textContent = curQty;
        } else if (variant.track_stock && curQty > maxAllowed) {
            curQty = Math.max(minimumQty, maxAllowed);
            qtyEl.textContent = curQty;
        }

        updatePrice(variant, curQty);
        updateStock(variant);
        showGalleryForVariant(variant, selection);
    }

    /* ---------- Radio Event Listener (Handles Smart Option Switching) ---------- */

    radios.forEach(function (r) {
        r.addEventListener('change', function (e) {
            const clickedAttrId = String(e.target.dataset.attributeId);
            const clickedOptId  = Number(e.target.value);

            let selection = getSelectedOptions();
            let variant   = findVariant(selection);

            // If the combination of previous selection + new click is unavailable,
            // smartly switch other axes to the first valid variant having this clicked option
            if (!variant) {
                const altVariant = variants.find(v => Number(v.options[clickedAttrId]) === clickedOptId);
                if (altVariant) {
                    radios.forEach(function (radio) {
                        const aId = String(radio.dataset.attributeId);
                        if (aId !== clickedAttrId && Number(altVariant.options[aId]) === Number(radio.value)) {
                            radio.checked = true;
                        }
                    });
                }
            }
            updateProduct();
        });
    });

    /* ---------- Thumbnails & Carousel Arrows ---------- */

    main.addEventListener('click', function (e) {
        const thumb = e.target.closest('.pd-thumb');
        if (thumb && main.contains(thumb)) {
            const src = thumb.dataset.thumbSrc;
            if (!src) return;
            mainImg.removeAttribute('srcset');
            mainImg.src = src;
            thumbsEl.querySelectorAll('.pd-thumb').forEach(t => t.classList.toggle('is-active', t === thumb));
            return;
        }

        const arrow = e.target.closest('.pd-thumb-arrow');
        if (arrow && main.contains(arrow)) {
            const dir = arrow.classList.contains('pd-thumb-next') ? 1 : -1;
            thumbsEl.scrollBy({
                left: dir * Math.max(160, thumbsEl.clientWidth * 0.7),
                behavior: 'smooth'
            });
        }
    });

    /* ---------- Quantity Controls ---------- */

    if (qtyPlus) {
        qtyPlus.addEventListener('click', function () {
            const selection = getSelectedOptions();
            const variant   = findVariant(selection);
            const maxAllowed = getMaxAllowedQuantity(variant);
            const current   = Number(qtyEl.textContent);

            if (current + 1 <= maxAllowed) {
                const nextQty = current + 1;
                qtyEl.textContent = nextQty;
                updatePrice(variant, nextQty);
            } else {
                showToast('Maximum available quantity limit reached (' + maxAllowed + ')');
            }
        });
    }

    if (qtyMinus) {
        qtyMinus.addEventListener('click', function () {
            const selection = getSelectedOptions();
            const variant   = findVariant(selection);
            const current   = Number(qtyEl.textContent);

            if (current - 1 >= minimumQty) {
                const nextQty = current - 1;
                qtyEl.textContent = nextQty;
                updatePrice(variant, nextQty);
            } else {
                showToast('Minimum order quantity is ' + minimumQty);
            }
        });
    }

    /* ---------- Cart / Buy Now Integration ---------- */

    window.pdSelection = function () {
        const selection = getSelectedOptions();
        const variant   = findVariant(selection);
        return {
            product_id: {{ $product->id }},
            variant_id: variant ? variant.id : (idInput.value ? Number(idInput.value) : null),
            sku: variant ? variant.sku : null,
            qty: Number(qtyEl.textContent),
            mode: isRetail ? 'retail' : 'wholesale'
        };
    };

    if (cartBtn) {
        cartBtn.addEventListener('click', function () {
            const payload = window.pdSelection();
            if (!payload.variant_id && variants.length > 0) {
                showToast('Please select all product options.');
                return;
            }
            showToast('Ready for cart: ' + payload.qty + ' item(s) selected.');
        });
    }

    if (buyBtn) {
        buyBtn.addEventListener('click', function () {
            const payload = window.pdSelection();
            if (!payload.variant_id && variants.length > 0) {
                showToast('Please select all product options.');
                return;
            }
            showToast('Proceeding to checkout with ' + payload.qty + ' item(s)...');
        });
    }

    const shareBtn = document.getElementById('pdShareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                }).catch(function () {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(function () {
                    showToast('Product link copied to clipboard!');
                });
            } else {
                showToast('Share link: ' + window.location.href);
            }
        });
    }

    /* ---------- Initial Setup Run ---------- */
    updateProduct();
});
</script>
@endpush
