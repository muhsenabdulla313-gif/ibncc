@extends('layouts.trading')

@php
    $noImg = asset('assets/images/no-image.avif');
    $mode  = 'wholesale'; // change to 'retail' if this wishlist is for the retail site

    $resolveImg = function ($path) use ($noImg) {
        if (!$path) return $noImg;
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//'])) return $path;
        $clean = ltrim($path, '/\\');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('storage/' . $clean))) return asset('storage/' . $clean);
        return $noImg;
    };
@endphp

@section('title')
    My Wishlist — CC Hub
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cchub/ch-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-trading.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/wish-list.css') }}">
@endpush

@section('body')
<main class="wishlist-main" id="main-content">
    <div class="wishlist-container">

        <nav class="wishlist-breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ route('trading') }}"><i class="fa-solid fa-house"></i> Home</a>
            <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            <a href="{{ route('trading.products.index') }}">Trading</a>
            <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            <span class="current">My Wishlist</span>
        </nav>

        <div class="wishlist-header">
            <h1 class="wishlist-title">My Wishlist</h1>
            <p class="wishlist-subtitle">Your curated collection of favorites</p>
        </div>

        <div class="wishlist-toolbar" id="wishlistToolbar" @if($items->isEmpty()) style="display:none" @endif>
            <span class="wishlist-count">Showing <strong id="itemCountNumber">{{ $items->count() }}</strong> items</span>
            <button type="button" class="wishlist-clear-btn" id="btnClearWishlist">
                <i class="fa-regular fa-trash-can" aria-hidden="true"></i> Clear All
            </button>
        </div>

        <div class="wishlist-grid" id="wishlistGrid">
            @foreach ($items as $item)
                @php
                    $product = $item->product;
                    $title   = $product->title ?? $product->name ?? 'Product';
                    $unit    = $product->unit?->name ?? 'pieces';

                    $variants = $product->variants->values();
                    $variant  = $variants->firstWhere('is_default', true) ?? $variants->first();

                    $price = (float) ($variant?->price ?? 0);
                    $mrp   = (float) ($variant?->mrp ?? 0);
                    $disc  = ($mrp > $price && $price > 0) ? (int) round((($mrp - $price) / $mrp) * 100) : 0;

                    // wholesale: show lowest tier price if variant price is empty
                    $tierMin = $product->priceTiers->where('price_type', 'wholesale')->min('price');
                    $fromTier = ($price <= 0 && $tierMin);
                    if ($fromTier) $price = (float) $tierMin;

                    $minQty = $mode === 'retail' ? 1 : max(1, (int) ($product->min_order_qty ?? 1));
                    $avail  = $variant ? max(0, (int) $variant->stock - (int) ($variant->reserved_stock ?? 0)) : 0;
                    $inStock = $variant && ($product->track_stock ? $avail >= $minQty : true);

                    $imgs = $product->images->sortBy('sort_order');
                    $img  = $resolveImg($imgs->first()?->path);

                    $hasRating = (int) ($product->rating_count ?? 0) > 0;

                    // CHANGE this to your real product details route
                    $detailsUrl = route('trading.products.show', $product->slug ?? $product->id);
                @endphp

                <article class="wishlist-card" data-product-id="{{ $product->id }}">
                    <div class="wishlist-media">
                        <img src="{{ $img }}" alt="{{ $title }}" class="wishlist-img" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ $noImg }}'">
                        <button type="button" class="wishlist-remove-btn" data-remove="{{ $product->id }}"
                                aria-label="Remove {{ $title }} from wishlist" title="Remove item">
                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="wishlist-content">
                        <h3 class="pl-card-title"><a href="{{ $detailsUrl }}">{{ $title }}</a></h3>

                        @if ($product->short_description)
                            <p class="pl-card-specs">{{ \Illuminate\Support\Str::limit($product->short_description, 90) }}</p>
                        @endif

                        @if ($hasRating)
                            <div class="pl-rating-row">
                                <span class="pl-rating-badge">{{ number_format($product->rating_avg, 1) }} <i class="fa-solid fa-star"></i></span>
                                <span class="pl-reviews">({{ number_format($product->rating_count) }})</span>
                            </div>
                        @endif

                        <div class="pl-price-row">
                            @if ($price > 0)
                                <span class="pl-price">{{ $fromTier ? 'From ' : '' }}₹{{ number_format($price, 2) }}</span>
                                @if ($mrp > $price && !$fromTier)
                                    <span class="pl-mrp">₹{{ number_format($mrp, 2) }}</span>
                                    <span class="pl-discount">{{ $disc }}% off</span>
                                @endif
                            @else
                                <span class="pl-price">Price on request</span>
                            @endif
                        </div>

                        <p class="pl-stock {{ $inStock ? 'is-in' : 'is-out' }}">{{ $inStock ? 'In Stock' : 'Out of Stock' }}</p>

                        <button type="button" class="wishlist-btn-cart"
                                data-action="move-to-cart"
                                data-product-id="{{ $product->id }}"
                                data-variant-id="{{ $variant?->id }}"
                                data-qty="{{ $minQty }}"
                                @disabled(!$inStock)>
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> MOVE TO CART
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="wishlist-empty" id="wishlistEmptyState" @if($items->isNotEmpty()) style="display:none" @endif>
            <div class="wishlist-empty-icon"><i class="fa-regular fa-heart"></i></div>
            <h2 class="wishlist-empty-title">Your Wishlist is Empty</h2>
            <p class="wishlist-empty-desc">Explore our exclusive collections and save your favorite items for later.</p>
            <a href="{{ route('trading.products.index') }}" class="btn-explore-shop">
                <i class="fa-solid fa-arrow-left"></i> Explore Products
            </a>
        </div>
    </div>
</main>

<div class="wishlist-toast" id="wishlistToast" role="status" aria-live="polite">
    <i class="fa-solid fa-circle-check"></i>
    <span id="wishlistToastMsg">Item updated successfully.</span>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const csrf      = @json(csrf_token());
    const toggleUrl = @json(route('wishlist.toggle'));
    const clearUrl  = @json(route('wishlist.clear'));
    const cartUrl   = @json(route('cart.add'));
    const mode      = @json($mode);

    const grid    = document.getElementById('wishlistGrid');
    const empty   = document.getElementById('wishlistEmptyState');
    const toolbar = document.getElementById('wishlistToolbar');
    const countEl = document.getElementById('itemCountNumber');
    const toastEl = document.getElementById('wishlistToast');
    const toastMsg = document.getElementById('wishlistToastMsg');

    function toast(msg) {
        toastMsg.textContent = msg;
        toastEl.classList.add('show', 'is-visible');
        clearTimeout(toastEl._t);
        toastEl._t = setTimeout(() => toastEl.classList.remove('show', 'is-visible'), 3000);
    }
    window.pdToast = toast; // used by wishlist.js

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body || {})
        }).then(r => r.json().catch(() => ({})));
    }

    function refresh() {
        const n = grid.querySelectorAll('.wishlist-card').length;
        countEl.textContent = n;
        toolbar.style.display = n ? '' : 'none';
        empty.style.display   = n ? 'none' : '';
    }

    function removeCard(card) {
        card.remove();
        refresh();
    }

    grid.addEventListener('click', function (e) {
        /* remove */
        const rm = e.target.closest('[data-remove]');
        if (rm) {
            const card = rm.closest('.wishlist-card');
            rm.disabled = true;
            post(toggleUrl, { product_id: Number(rm.dataset.remove) }).then(d => {
                if (d.success) {
                    if (d.wishlisted) { rm.disabled = false; return; } // was re-added, keep card
                    removeCard(card);
                    toast('Removed from wishlist.');
                } else {
                    rm.disabled = false;
                    toast(d.message || 'Could not remove item.');
                }
            }).catch(() => { rm.disabled = false; toast('Something went wrong.'); });
            return;
        }

        /* move to cart */
        const btn = e.target.closest('[data-action="move-to-cart"]');
        if (btn && !btn.disabled) {
            const card = btn.closest('.wishlist-card');
            const html = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Moving...';

            post(cartUrl, {
                product_id: Number(btn.dataset.productId),
                variant_id: btn.dataset.variantId ? Number(btn.dataset.variantId) : null,
                qty: Number(btn.dataset.qty) || 1,
                mode: mode
            }).then(d => {
                if (!d.success) {
                    btn.disabled = false; btn.innerHTML = html;
                    return toast(d.message || 'Could not add to cart.');
                }
                document.querySelectorAll('.cart-badge').forEach(b => b.textContent = d.cart_count);
                // added to cart -> remove from wishlist
                return post(toggleUrl, { product_id: Number(btn.dataset.productId) }).then(t => {
                    if (t.success && t.wishlisted) {
                        // toggle re-added it somehow; flip back
                        return post(toggleUrl, { product_id: Number(btn.dataset.productId) });
                    }
                }).then(() => {
                    removeCard(card);
                    toast('Moved to cart.');
                });
            }).catch(() => {
                btn.disabled = false; btn.innerHTML = html;
                toast('Something went wrong. Please try again.');
            });
        }
    });

    /* clear all */
    document.getElementById('btnClearWishlist').addEventListener('click', function () {
        if (!confirm('Remove all items from your wishlist?')) return;
        post(clearUrl).then(d => {
            if (d.success) {
                grid.innerHTML = '';
                refresh();
                toast('Wishlist cleared.');
            } else {
                toast(d.message || 'Could not clear wishlist.');
            }
        }).catch(() => toast('Something went wrong.'));
    });
});
</script>
@endpush