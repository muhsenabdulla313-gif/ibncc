@extends('layouts.trading')

@php
    $noImg = asset('assets/images/no-image.avif');

    $resolveImg = function ($path) use ($noImg) {
        if (!$path) return $noImg;
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) return $path;
        $clean = ltrim($path, '/\\');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);
        return $noImg;
    };

    $title    = $product->title ?? $product->name ?? 'Product';
    $isRetail = ($mode === 'retail');
    $unit     = $product->unit?->name ?? 'pieces';

    /* ---------- Images ---------- */
    $allImages = $product->images->sortBy('sort_order')->values();

    $imageData = $allImages->map(fn ($img) => [
        'url'        => $resolveImg($img->path),
        'option_id'  => $img->attribute_option_id ? (int) $img->attribute_option_id : null,
        'variant_id' => $img->variant_id ? (int) $img->variant_id : null,
    ])->values()->all();

    $generalImages  = collect($imageData)->filter(fn ($i) => is_null($i['option_id']) && is_null($i['variant_id']));
    $fallbackImages = $generalImages->isNotEmpty()
        ? $generalImages->pluck('url')->unique()->values()->all()
        : (collect($imageData)->pluck('url')->unique()->values()->all() ?: [$noImg]);

    /* ---------- Variants & axes ---------- */
    $variants = $product->variants->where('is_active', true)->values();

    $axes = $variants
        ->flatMap(fn ($v) => $v->attributeValues)
        ->filter(fn ($val) => $val->attribute && $val->attribute->is_variant_axis && $val->option)
        ->groupBy('attribute_id')
        ->map(fn ($rows) => [
            'attribute' => $rows->first()->attribute,
            'options'   => $rows->pluck('option')->unique('id')->sortBy('sort_order')->values(),
        ]);

    $colorAxis = $axes->first(fn ($a) =>
        strtolower($a['attribute']->name) === 'color' || $a['attribute']->input_type === 'color'
    );
    $colorAxisId = $colorAxis ? (int) $colorAxis['attribute']->id : null;

    $selectedVariant = $variants->firstWhere('is_default', true) ?? $variants->first();

    $selectedOptions = $selectedVariant
        ? $selectedVariant->attributeValues->pluck('attribute_option_id', 'attribute_id')
            ->mapWithKeys(fn ($o, $a) => [(string) $a => (int) $o])->all()
        : [];

    /* ---------- Qty & stock ---------- */
    $minQty      = $isRetail ? 1 : max(1, (int) ($product->min_order_qty ?? 1));
    $maxOrderQty = $product->max_order_qty ? (int) $product->max_order_qty : null;

    $availStock = $selectedVariant
        ? max(0, (int) $selectedVariant->stock - (int) ($selectedVariant->reserved_stock ?? 0))
        : 0;
    $isInStock = $product->track_stock ? ($availStock >= $minQty) : true;

    /* ---------- Tiers & retail price ---------- */
    $wholesaleTiers = $product->priceTiers->where('price_type', 'wholesale')->sortBy('min_qty')->values();

    $startPrice    = (float) ($selectedVariant?->price ?? 0);
    $startMrp      = (float) ($selectedVariant?->mrp ?? 0);
    $startDiscount = ($startMrp > $startPrice && $startMrp > 0)
        ? (int) round((($startMrp - $startPrice) / $startMrp) * 100) : 0;

    /* ---------- Initial gallery ---------- */
    $selColor = $colorAxisId ? ($selectedOptions[(string) $colorAxisId] ?? null) : null;
    $initialImages = $selColor
        ? collect($imageData)->filter(fn ($i) => $i['option_id'] === (int) $selColor)->pluck('url')->unique()->values()->all()
        : [];
    if (empty($initialImages)) $initialImages = $fallbackImages;

    /* ---------- JS payloads ---------- */
    $variantData = $variants->map(function ($v) use ($product) {
        return [
            'id'          => $v->id,
            'sku'         => $v->sku,
            'price'       => (float) ($v->price ?? 0),
            'mrp'         => (float) ($v->mrp ?? 0),
            'stock'       => max(0, (int) $v->stock - (int) ($v->reserved_stock ?? 0)),
            'track_stock' => (bool) $product->track_stock,
            'options'     => $v->attributeValues->pluck('attribute_option_id', 'attribute_id')
                                ->mapWithKeys(fn ($o, $a) => [(string) $a => (int) $o])->all(),
        ];
    })->values()->all();

    $tiersData = $wholesaleTiers->map(fn ($t) => [
        'variant_id' => $t->variant_id ? (int) $t->variant_id : null,
        'min_qty'    => (float) $t->min_qty,
        'max_qty'    => $t->max_qty !== null ? (float) $t->max_qty : null,
        'price'      => (float) $t->price,
    ])->values()->all();

    /* ---------- Specs ---------- */
    $basicSpecs = array_filter([
        'Brand'    => $product->brand?->name,
        'Model'    => $product->model_no ?? null,
        'Category' => $product->category?->name,
        'Unit'     => $product->unit?->name,
        'MOQ'      => (!$isRetail && $product->min_order_qty) ? $product->min_order_qty . ' ' . $unit : null,
    ]);

    $specs = $product->attributeValues
        ->filter(fn ($pav) => $pav->attribute && !$pav->attribute->is_variant_axis)
        ->groupBy('attribute_id')
        ->map(fn ($rows) => [
            'name'  => $rows->first()->attribute->name,
            'value' => $rows->map->display_value->filter()->unique()->implode(', '),
        ])
        ->filter(fn ($s) => $s['value'] !== '')
        ->values();

    $hasRating = (int) ($product->rating_count ?? 0) > 0;

    $isWishlisted = auth()->check()
        ? \App\Models\Wishlist::where('user_id', auth()->id())
                               ->where('product_id', $product->id)
                               ->exists()
        : false;
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
<link rel="stylesheet" href="{{ asset('css/cchub/trading-product-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/trading-product-details.css') }}">

<style>
    .pd-gallery-set[hidden] { display: none !important; }

    /* ---------- Variant group card ---------- */
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

    /* ---------- Pill ---------- */
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

    /* ---------- Quantity ---------- */
    .pd-qty-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 20px 0 6px;
    }
    .pd-qty-label {
        font-size: 15px;
        font-weight: 600;
        color: #0f2a4a;
    }
    .cart-qty-control {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px;
        background: #f4f7fb;
        border: 1px solid #dde4ee;
        border-radius: 12px;
    }
    .cart-qty-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dde4ee;
        border-radius: 9px;
        color: #0f2a4a;
        font-size: 13px;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }
    .cart-qty-btn:hover { background: #eef3fb; border-color: #93b4f5; }
    .cart-qty-btn:active { background: #dbe6f7; }
    .cart-qty-number {
        min-width: 56px;
        width: 70px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        color: #0f2a4a;
        background: transparent;
        border: 0;
        outline: 0;
        -moz-appearance: textfield;
    }
    .cart-qty-number::-webkit-outer-spin-button,
    .cart-qty-number::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    /* ---------- Thumbs ---------- */
    .pd-thumb { cursor: pointer; }
    .pd-thumb.is-active { outline: 2px solid #2563eb; }
    .pd-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ---------- Wholesale tier highlight + toast ---------- */
/* ---------- Wholesale tier boxes ---------- */
.pd-tier-prices {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin: 18px 0 8px;
    padding: 14px 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    max-width: 560px;
}
.pd-tier {
    padding: 12px 14px;
    border: 1px solid transparent;
    border-radius: 12px;
    background: transparent;
    transition: background .15s ease, border-color .15s ease;
}
.pd-tier-price {
    display: flex;
    align-items: flex-start;
    color: #0f172a;
    font-weight: 800;
    line-height: 1;
}
.pd-tier-currency { font-size: 22px; margin-top: 4px; margin-right: 2px; }
.pd-tier-whole    { font-size: 34px; letter-spacing: -.5px; }
.pd-tier-cents    { font-size: 13px; margin-top: 4px; margin-left: 2px; font-weight: 700; }
.pd-tier-qty {
    margin-top: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
}

/* active tier */
.pd-tier.is-active-tier {
    background: #fff7e6;
    border-color: #f3c43a;
}
.pd-tier.is-active-tier .pd-tier-qty {
    color: #d97706;
    font-weight: 700;
}    .pd-toast-msg {
        position: fixed; bottom: 24px; right: 24px;
        background: #0f172a; color: #fff;
        padding: 12px 20px; border-radius: 10px;
        display: none; align-items: center; gap: 10px;
        z-index: 9999; font-size: 14px;
    }

    /* ---------- Wishlist heart button ---------- */
    .pd-icon-wish { transition: transform .15s ease; }
    .pd-icon-wish i { transition: color .2s ease; }
    .pd-icon-wish:hover i { color: #e11d48; }
    .pd-icon-wish.is-wishlisted i { color: #e11d48; }
    .pd-icon-wish.is-wishlisted { animation: none; }
    @keyframes heartPop {
        0%   { transform: scale(1); }
        40%  { transform: scale(1.35); }
        70%  { transform: scale(0.9); }
        100% { transform: scale(1); }
    }
    .pd-icon-wish.heart-pop { animation: heartPop .35s ease forwards; }
</style>
@endpush

@section('body')
<main class="pd-main">
    <article class="pd-product" data-id="{{ $product->id }}">

        <nav class="pl-breadcrumb pd-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('trading') }}">Home</a><span aria-hidden="true">›</span>
            <a href="{{ $isRetail ? route('products.index') : route('trading.products.index') }}">
                {{ $isRetail ? 'Products' : 'Trading Products' }}
            </a>
            @if ($product->category)
                @php
                    $catChain = [];
                    for ($c = $product->category; $c; $c = $c->parent) array_unshift($catChain, $c);
                @endphp
                @foreach ($catChain as $cNode)
                    <span aria-hidden="true">›</span>
                    <a href="{{ $isRetail ? route('products.index', ['cat' => $cNode->slug]) : route('trading.products.index', ['cat' => $cNode->slug]) }}">{{ $cNode->name }}</a>
                @endforeach
            @endif
            <span aria-hidden="true">›</span>
            <span aria-current="page">{{ $title }}</span>
        </nav>

        <section class="pd-hero">

            {{-- GALLERY --}}
            <div class="pd-gallery">
                <div class="pd-gallery-set is-active" id="pdGallery">
                    <div class="pd-main-media">
                        <img class="pd-main-image" id="pdMainImage" src="{{ $initialImages[0] }}" alt="{{ $title }}"
                             onerror="this.onerror=null;this.src='{{ $noImg }}'">
                    </div>

                    <div class="pd-thumb-row">
                        <button type="button" class="pd-thumb-arrow pd-thumb-prev" aria-label="Scroll thumbnails left">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="pd-thumbs" id="pdThumbs" aria-label="Product thumbnails">
                            @foreach ($initialImages as $index => $image)
                                <button type="button" class="pd-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                        data-thumb-src="{{ $image }}" aria-label="Show image {{ $index + 1 }}">
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

            {{-- INFO --}}
            <div class="pd-info">

                <div class="pd-info-top">
                    <span class="pd-brand">{{ $product->brand?->name ?? '' }}</span>
                    <div class="pd-info-actions">
                        <button type="button" class="pd-icon-btn" id="pdShareBtn" aria-label="Share product">
                            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="pd-icon-btn pd-icon-wish {{ $isWishlisted ? 'is-wishlisted' : '' }}"
                                id="pdWishlistBtn"
                                data-wish="{{ $product->id }}"
                                aria-label="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
                                title="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}">
                            <i class="{{ $isWishlisted ? 'fa-solid' : 'fa-regular' }} fa-heart" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <h1 class="pd-title">{{ $title }}</h1>

                @if ($product->short_description)
                    <p class="pd-specs">{{ $product->short_description }}</p>
                @endif

                @if ($hasRating)
                    <div class="pd-meta">
                        <span class="pd-rating-badge">{{ number_format($product->rating_avg, 1) }} <i class="fa-solid fa-star"></i></span>
                        <span class="pd-reviews">{{ number_format($product->rating_count) }} Ratings &amp; Reviews</span>
                    </div>
                @endif

                {{-- PRICE --}}
                @if ($isRetail)
                    <div class="pd-price-wrap">
                        <span class="pd-price" id="pdPrice">₹{{ number_format($startPrice, 2) }}</span>
                        <span class="pd-mrp" id="pdMrp" style="{{ $startMrp > $startPrice ? '' : 'display:none;' }}">₹{{ number_format($startMrp, 2) }}</span>
                        <span class="pd-discount" id="pdDiscount" style="{{ $startDiscount > 0 ? '' : 'display:none;' }}">{{ $startDiscount }}% off</span>
                    </div>
                @else
                    <div class="pd-price-wrap pd-tier-prices" id="pdTierPrices" aria-label="Quantity-based wholesale pricing"></div>
                @endif

                {{-- QUANTITY (same position as static page) --}}
                <div class="pd-qty-row">
                    <span class="pd-qty-label">Quantity:</span>
                    <div class="cart-qty-control" role="group" aria-label="Select quantity">
                        <button type="button" class="cart-qty-btn btn-qty-minus" aria-label="Decrease quantity">
                            <i class="fa-solid fa-minus" aria-hidden="true"></i>
                        </button>
                        <input type="number" class="cart-qty-input cart-qty-number" id="pdQty"
                               min="{{ $minQty }}" @if($maxOrderQty) max="{{ $maxOrderQty }}" @endif
                               value="{{ $minQty }}" inputmode="numeric" aria-label="Item quantity">
                        <button type="button" class="cart-qty-btn btn-qty-plus" aria-label="Increase quantity">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                {{-- VARIANT GROUPS --}}
                @foreach ($axes as $attrId => $axis)
                    @php
                        $attributeName = $axis['attribute']->name;
                        $isColor = strtolower($attributeName) === 'color' || $axis['attribute']->input_type === 'color';
                    @endphp
                    <div class="pd-prop-group">
                        <span class="pd-prop-title">{{ $attributeName }}</span>
                        <div class="pd-prop-options" role="radiogroup" aria-label="{{ $attributeName }}"
                             @if ($isColor) data-gallery-sync="color" @endif>
                            @foreach ($axis['options'] as $option)
                                <label class="pd-prop-option">
                                    <input type="radio"
                                           name="pd-attribute-{{ $product->id }}-{{ $attrId }}"
                                           value="{{ $option->id }}"
                                           data-attribute-id="{{ $attrId }}"
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

                {{-- STOCK --}}
                <p class="pl-stock {{ $isInStock ? 'is-in' : 'is-out' }}" id="pdStock">
                    @if ($isInStock)
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> In Stock
                    @else
                        <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock
                    @endif
                </p>

                {{-- OFFER LINE --}}
                @if (!$isRetail)
                    <p class="pd-offer">
                        <i class="fa-solid fa-tags" aria-hidden="true"></i>
                        Wholesale pricing · MOQ {{ number_format($minQty) }} {{ $unit }} · Volume discounts apply
                    </p>
                @endif

                {{-- ACTIONS --}}
                <div class="pd-actions">
                    <button type="button" class="pd-btn pd-btn-cart" id="pdAddCart" data-cart="{{ $product->id }}" @disabled(!$isInStock)>
                        <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                    </button>
                    <button type="button" class="pd-btn pd-btn-buy" id="pdBuyNow" @disabled(!$isInStock)>
                        <i class="fa-solid fa-bolt"></i> Buy Now
                    </button>
                </div>

                {{-- SPECS --}}
                <section class="pd-specs-card">
                    <header class="pd-section-head"><h2>Product Details</h2></header>
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

                @if ($product->description && $product->description !== $product->short_description)
                    <section class="pd-specs-card">
                        <header class="pd-section-head"><h2>Description</h2></header>
                        <div style="padding:16px 20px;line-height:1.7;font-size:14px;">{!! nl2br(e($product->description)) !!}</div>
                    </section>
                @endif
            </div>
        </section>
    </article>
</main>

<div class="pd-toast-msg" id="pdToast"><i class="fa-solid fa-circle-check" style="color:#22c55e"></i><span id="pdToastText"></span></div>
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
    const unitName       = @json($unit);

    const main = document.querySelector('.pd-product[data-id="{{ $product->id }}"]');
    if (!main) return;

    const mainImg   = main.querySelector('#pdMainImage');
    const thumbsEl  = main.querySelector('#pdThumbs');
    const tiersEl   = main.querySelector('#pdTierPrices');
    const priceEl   = main.querySelector('#pdPrice');
    const mrpEl     = main.querySelector('#pdMrp');
    const discEl    = main.querySelector('#pdDiscount');
    const stockEl   = main.querySelector('#pdStock');
    const idInput   = main.querySelector('#selectedVariantId');
    const skuEl     = main.querySelector('#pdSku');
    const cartBtn   = main.querySelector('#pdAddCart');
    const buyBtn    = main.querySelector('#pdBuyNow');
    const qtyEl     = main.querySelector('#pdQty');
    const qtyPlus   = main.querySelector('.btn-qty-plus');
    const qtyMinus  = main.querySelector('.btn-qty-minus');
    const toastEl   = document.getElementById('pdToast');
    const toastText = document.getElementById('pdToastText');
    const radios    = Array.from(main.querySelectorAll('.pd-prop-option input[data-attribute-id]'));
    const colorInputs = Array.from(main.querySelectorAll('[data-gallery-sync="color"] input[data-attribute-id]'));

    let currentGalleryKey = '';
    const fmt = n => Number(n).toLocaleString('en-IN');

    function showToast(msg) {
        if (!toastEl) return;
        toastText.textContent = msg;
        toastEl.style.display = 'flex';
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => toastEl.style.display = 'none', 3000);
    }

    /* ---------- selection ---------- */
    function getSelection() {
        const s = {};
        radios.forEach(r => { if (r.checked) s[String(r.dataset.attributeId)] = Number(r.value); });
        return s;
    }
    function matches(v, sel) {
        return Object.keys(sel).every(id => Number(v.options[id]) === Number(sel[id]));
    }
    function findVariant(sel) { return variants.find(v => matches(v, sel)) || null; }

    function updateAvailable(sel) {
        radios.forEach(r => {
            const aid = String(r.dataset.attributeId);
            const test = {};
            Object.keys(sel).forEach(k => { if (k !== aid) test[k] = sel[k]; });
            test[aid] = Number(r.value);
            r.disabled = !variants.some(v => matches(v, test));
        });
    }

    /* ---------- gallery (Color axis only) ---------- */
    function renderGallery(images) {
        if (!images.length) images = [noImg];
        const key = JSON.stringify(images);
        if (key === currentGalleryKey) return;
        currentGalleryKey = key;

        mainImg.removeAttribute('srcset');
        mainImg.src = images[0];
        thumbsEl.innerHTML = '';

        images.forEach((src, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pd-thumb' + (i === 0 ? ' is-active' : '');
            btn.dataset.thumbSrc = src;
            btn.setAttribute('aria-label', 'Show image ' + (i + 1));
            const im = document.createElement('img');
            im.src = src; im.alt = '';
            im.onerror = function () { this.onerror = null; this.src = noImg; };
            btn.appendChild(im);
            thumbsEl.appendChild(btn);
        });
    }

    function showGallery(variant) {
        const checked = colorInputs.find(r => r.checked);
        const colorId = checked ? Number(checked.value) : null;

        let images = colorId ? imageData.filter(i => i.option_id === colorId).map(i => i.url) : [];
        if (!images.length && variant) images = imageData.filter(i => i.variant_id === variant.id).map(i => i.url);
        if (!images.length) images = fallbackImages;

        renderGallery(Array.from(new Set(images)));
    }

    /* ---------- tiers (wholesale) ---------- */
    function tiersFor(variant) {
        const own = variant ? tiers.filter(t => t.variant_id === variant.id) : [];
        const list = own.length ? own : tiers.filter(t => !t.variant_id);
        return list.slice().sort((a, b) => a.min_qty - b.min_qty);
    }

    function renderTiers(variant, qty) {
        if (!tiersEl) return;
        const list = tiersFor(variant);
        const active = list.filter(t => t.min_qty <= qty).pop() || list[0];

        tiersEl.innerHTML = list.map(t => {
            const parts = Number(t.price).toFixed(2).split('.');
            const label = t.max_qty ? fmt(t.min_qty) + '-' + fmt(t.max_qty) : '≥' + fmt(t.min_qty);
            return '<div class="pd-tier' + (t === active ? ' is-active-tier' : '') + '">' +
                '<div class="pd-tier-price"><span class="pd-tier-currency">₹</span>' +
                '<span class="pd-tier-whole">' + fmt(parts[0]) + '</span>' +
                '<sup class="pd-tier-cents">' + parts[1] + '</sup></div>' +
                '<div class="pd-tier-qty">' + label + ' ' + unitName + '</div></div>';
        }).join('');
    }

    function updateRetailPrice(variant) {
        if (!priceEl || !variant) return;
        const f = n => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        priceEl.textContent = f(variant.price);
        if (mrpEl && discEl) {
            const show = variant.mrp > variant.price;
            mrpEl.style.display = discEl.style.display = show ? '' : 'none';
            if (show) {
                mrpEl.textContent = f(variant.mrp);
                discEl.textContent = Math.round(((variant.mrp - variant.price) / variant.mrp) * 100) + '% off';
            }
        }
    }

    /* ---------- stock / qty ---------- */
    function maxAllowed(variant) {
        let max = 999999;
        if (maxOrderQty) max = maxOrderQty;
        if (variant && variant.track_stock) max = Math.min(max, variant.stock);
        return max;
    }

    function updateStock(variant) {
        const ok = variant ? (!variant.track_stock || variant.stock >= minimumQty) : false;
        stockEl.className = 'pl-stock ' + (ok ? 'is-in' : 'is-out');
        stockEl.innerHTML = ok
            ? '<i class="fa-solid fa-circle-check" aria-hidden="true"></i> In Stock'
            : '<i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Out of Stock';
        if (cartBtn) cartBtn.disabled = !ok;
        if (buyBtn)  buyBtn.disabled  = !ok;
    }

    function clampQty(variant) {
        let q = parseInt(qtyEl.value, 10);
        if (isNaN(q) || q < minimumQty) q = minimumQty;
        const max = maxAllowed(variant);
        if (q > max) { q = Math.max(minimumQty, max); showToast('Maximum available quantity is ' + max); }
        qtyEl.value = q;
        return q;
    }

    function refreshPricing() {
        const variant = findVariant(getSelection());
        const q = clampQty(variant);
        if (isRetail) updateRetailPrice(variant); else renderTiers(variant, q);
    }

    /* ---------- master update ---------- */
    function updateProduct() {
        const sel = getSelection();
        updateAvailable(sel);
        const variant = findVariant(sel);

        idInput.value = variant ? variant.id : '';
        if (skuEl) skuEl.textContent = variant?.sku || 'N/A';

        updateStock(variant);
        showGallery(variant);
        refreshPricing();
    }

    radios.forEach(r => r.addEventListener('change', function (e) {
        const aid = String(e.target.dataset.attributeId);
        const oid = Number(e.target.value);

        if (!findVariant(getSelection())) {
            const alt = variants.find(v => Number(v.options[aid]) === oid);
            if (alt) {
                radios.forEach(x => {
                    const xa = String(x.dataset.attributeId);
                    if (xa !== aid && Number(alt.options[xa]) === Number(x.value)) x.checked = true;
                });
            }
        }
        updateProduct();
    }));

    /* ---------- thumbs & arrows ---------- */
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
            thumbsEl.scrollBy({ left: dir * Math.max(160, thumbsEl.clientWidth * 0.7), behavior: 'smooth' });
        }
    });

    /* ---------- quantity ---------- */
    qtyPlus  && qtyPlus.addEventListener('click',  () => { qtyEl.value = (parseInt(qtyEl.value, 10) || minimumQty) + 1; refreshPricing(); });
    qtyMinus && qtyMinus.addEventListener('click', () => { qtyEl.value = (parseInt(qtyEl.value, 10) || minimumQty) - 1; refreshPricing(); });
    qtyEl.addEventListener('input',  () => {
        const variant = findVariant(getSelection());
        const q = parseInt(qtyEl.value, 10);
        if (!isNaN(q) && !isRetail) renderTiers(variant, q);
    });
    qtyEl.addEventListener('change', refreshPricing);
    qtyEl.addEventListener('blur',   refreshPricing);

    /* ---------- cart / buy / share ---------- */
    window.pdSelection = function () {
        const variant = findVariant(getSelection());
        return {
            product_id: {{ $product->id }},
            variant_id: variant ? variant.id : (idInput.value ? Number(idInput.value) : null),
            sku: variant ? variant.sku : null,
            qty: parseInt(qtyEl.value, 10) || minimumQty,
            mode: isRetail ? 'retail' : 'wholesale'
        };
    };

    cartBtn && cartBtn.addEventListener('click', function () {
        const p = window.pdSelection();
        if (!p.variant_id && variants.length) {
            showToast('Please select all product options.');
            return;
        }
        if (p.qty <= 0) {
            showToast('Please choose a valid quantity.');
            return;
        }

        cartBtn.disabled = true;
        const originalText = cartBtn.innerHTML;
        cartBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: p.product_id,
                variant_id: p.variant_id,
                qty: p.qty,
                mode: p.mode
            })
        })
        .then(res => res.json())
        .then(data => {
            cartBtn.disabled = false;
            cartBtn.innerHTML = originalText;
            if (data.success) {
                showToast(data.message || 'Added to cart successfully!');
                document.querySelectorAll('.cart-badge').forEach(b => b.textContent = data.cart_count);
            } else {
                showToast(data.message || 'Could not add to cart.');
            }
        })
        .catch(err => {
            cartBtn.disabled = false;
            cartBtn.innerHTML = originalText;
            showToast('Something went wrong. Please try again.');
        });
    });

    buyBtn && buyBtn.addEventListener('click', function () {
        const p = window.pdSelection();
        if (!p.variant_id && variants.length) {
            showToast('Please select all product options.');
            return;
        }
        if (p.qty <= 0) {
            showToast('Please choose a valid quantity.');
            return;
        }

        buyBtn.disabled = true;
        const originalText = buyBtn.innerHTML;
        buyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        fetch('{{ route('cart.buyNow') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: p.product_id,
                variant_id: p.variant_id,
                qty: p.qty,
                mode: p.mode
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                buyBtn.disabled = false;
                buyBtn.innerHTML = originalText;
                showToast(data.message || 'Could not proceed to checkout.');
            }
        })
        .catch(err => {
            buyBtn.disabled = false;
            buyBtn.innerHTML = originalText;
            showToast('Something went wrong. Please try again.');
        });
    });

    const shareBtn = document.getElementById('pdShareBtn');
    shareBtn && shareBtn.addEventListener('click', function () {
        if (navigator.share) navigator.share({ title: document.title, url: location.href }).catch(() => {});
        else if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(() => showToast('Product link copied!'));
    });

    updateProduct();
});
window.pdToast = showToast;
</script>
@endpush