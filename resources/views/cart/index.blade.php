@extends('layouts.trading')

@section('title', 'Shopping Cart — CC Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}" />
<link rel="stylesheet" href="{{ asset('css/cchub/cart.css') }}" />
<style>
.cart-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #0f2a4a;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s ease;
}
.cart-toast.is-visible {
    opacity: 1;
}
.badge-trading {
    background: #e6f4ea;
    color: #137333;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-retail {
    background: #e8f0fe;
    color: #1a73e8;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cart-item-card {
    transition: opacity .2s ease;
}
.cart-item-card.is-updating {
    opacity: 0.5;
    pointer-events: none;
}
</style>
@endpush

@section('body')
<main class="cart-main" id="main-content">
    <div class="cart-container">
        <!-- Breadcrumb -->
        <nav class="cart-breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Home</a>
            <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            <a href="{{ url('/products') }}">Marketplace</a>
            <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            <span class="current">Shopping Cart</span>
        </nav>

        <!-- Page Title -->
        <h1 class="cart-page-title">
            Shopping Cart (<span id="cartCountTitle">{{ $cartCount }}</span> item{{ $cartCount == 1 ? '' : 's' }})
        </h1>

        <!-- Cart Content Grid -->
        <div class="cart-layout-grid" id="cartLayoutGrid" style="{{ empty($items) ? 'display: none;' : '' }}">
            <!-- 1. LEFT COLUMN: Cart Items -->
            <section class="cart-items-column" id="cartItemsList" aria-label="Cart Items">
                @foreach($items as $item)
                <article class="cart-item-card" id="cartItem-{{ $item['variant_id'] }}" data-variant-id="{{ $item['variant_id'] }}">
                    <button type="button" class="cart-item-remove" data-variant-id="{{ $item['variant_id'] }}" aria-label="Remove {{ $item['title'] }} from cart" title="Remove item">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>

                    <div class="cart-item-main">
                        <div class="cart-item-media">
                            <a href="{{ $item['url'] }}">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="cart-item-img" onerror="this.src='{{ asset('assets/images/no-image.avif') }}'" />
                            </a>
                        </div>

                        <div class="cart-item-info">
                            <div style="margin-bottom: 6px;">
                                @if($item['mode'] === 'wholesale')
                                    <span class="badge-trading"><i class="fa-solid fa-boxes-stacked"></i> Wholesale Trading</span>
                                @else
                                    <span class="badge-retail"><i class="fa-solid fa-store"></i> Retail Marketplace</span>
                                @endif
                            </div>

                            <h3 class="pl-card-title cart-item-title">
                                <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                            </h3>

                            @if($item['variant_specs'])
                                <p class="pl-card-specs" style="color: #64748b; font-size: 13px; margin: 4px 0;">
                                    {{ $item['variant_specs'] }}
                                </p>
                            @endif

                            @if($item['sku'])
                                <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 6px;">
                                    SKU: {{ $item['sku'] }}
                                </p>
                            @endif

                            <div class="cart-item-price-qty-row">
                                <div class="pl-price-row">
                                    <span class="pl-price" id="unitPrice-{{ $item['variant_id'] }}">₹{{ number_format($item['unit_price'], 2) }}</span>
                                    @if($item['mrp'] > $item['unit_price'])
                                        <span class="pl-mrp">₹{{ number_format($item['mrp'], 2) }}</span>
                                        <span class="pl-discount">{{ round((($item['mrp'] - $item['unit_price']) / $item['mrp']) * 100) }}% off</span>
                                    @endif
                                </div>

                                <div class="cart-qty-control" role="group" aria-label="Quantity for {{ $item['title'] }}">
                                    <button type="button" class="cart-qty-btn btn-qty-minus" data-variant-id="{{ $item['variant_id'] }}" aria-label="Decrease quantity">
                                        <i class="fa-solid fa-minus" aria-hidden="true"></i>
                                    </button>
                                    <input type="number" class="cart-qty-input cart-qty-number" id="qtyInput-{{ $item['variant_id'] }}"
                                           data-variant-id="{{ $item['variant_id'] }}" value="{{ (int) $item['qty'] }}"
                                           min="{{ $item['min_qty'] }}" @if($item['max_qty']) max="{{ $item['max_qty'] }}" @endif
                                           style="width: 58px; text-align: center; border: 1px solid #dcdcdc; border-radius: 6px; padding: 4px;" />
                                    <button type="button" class="cart-qty-btn btn-qty-plus" data-variant-id="{{ $item['variant_id'] }}" aria-label="Increase quantity">
                                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                                <span style="font-size: 13px; color: #475569;">
                                    Item Total: <strong style="color: #0f2a4a; font-size: 15px;" id="lineTotal-{{ $item['variant_id'] }}">₹{{ number_format($item['line_total'], 2) }}</strong>
                                </span>
                                @if($item['mode'] === 'wholesale')
                                    <span style="font-size: 11px; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                                        Min Order: {{ $item['min_qty'] }} {{ $item['unit_name'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach
            </section>

            <!-- 2. RIGHT COLUMN: Order Summary -->
            <aside class="cart-summary-column" aria-label="Order Summary">
                <div class="cart-summary-card">
                    <h2 class="cart-summary-title">Order Summary</h2>

                    <div class="cart-summary-table">
                        <div class="summary-row">
                            <span class="summary-row-label">Sub Total</span>
                            <span class="summary-row-value" id="summarySubTotal">₹{{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="summary-row summary-row--discount" id="summaryDiscountRow" style="{{ $discount > 0 ? '' : 'display: none;' }}">
                            <span class="summary-row-label">Product Savings</span>
                            <span class="summary-row-value" id="summaryDiscount">- ₹{{ number_format($discount, 2) }}</span>
                        </div>

                        <div class="summary-row summary-row--delivery">
                            <span class="summary-row-label">Delivery Charge</span>
                            <span class="summary-row-value" id="summaryDelivery">FREE</span>
                        </div>

                        <div class="summary-divider" aria-hidden="true"></div>

                        <div class="summary-row summary-row--total">
                            <span class="summary-row-label">TOTAL</span>
                            <span class="summary-row-value" id="summaryTotal">₹{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="btn-checkout" id="btnProceedCheckout" style="display: block; text-align: center; text-decoration: none; margin-top: 16px;">
                        Proceed to Checkout
                    </a>

                    <a href="{{ url('/products') }}" style="display: block; text-align: center; margin-top: 14px; font-size: 14px; color: #002b49; text-decoration: underline;">
                        <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                    </a>

                    <div class="cart-trust-note">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <span>Safe & Secure Inquiry & Transactions</span>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Empty Cart State Container -->
        <div class="cart-empty-state" id="cartEmptyState" style="{{ !empty($items) ? 'display: none;' : '' }}">
            <div class="cart-empty-icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h2 class="cart-empty-title">Your Cart is Currently Empty</h2>
            <p class="cart-empty-desc">
                Looks like you haven't added anything to your cart yet. Explore our curated collections to find retail products, wholesale deals, and commodity offers.
            </p>
            <a href="{{ url('/products') }}" class="btn-explore-shop">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                Explore Products
            </a>
        </div>
    </div>
</main>

<!-- Toast Notification -->
<div class="cart-toast" id="cartToast" role="status" aria-live="polite">
    <i class="fa-solid fa-circle-check"></i>
    <span id="cartToastMsg">Cart updated successfully.</span>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('cartToast');
    const toastMsg = document.getElementById('cartToastMsg');
    let toastTimer = null;

    function showToast(msg) {
        if (!toast) return;
        toastMsg.textContent = msg;
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3000);
    }

    function updateHeaderBadge(count) {
        document.querySelectorAll('.cart-badge').forEach(b => b.textContent = count);
        const titleCount = document.getElementById('cartCountTitle');
        if (titleCount) titleCount.textContent = count;
    }

    // Update quantity via AJAX
    function sendUpdate(variantId, newQty) {
        const card = document.getElementById('cartItem-' + variantId);
        if (card) card.classList.add('is-updating');

        fetch('{{ route('cart.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                variant_id: variantId,
                qty: newQty
            })
        })
        .then(res => res.json())
        .then(data => {
            if (card) card.classList.remove('is-updating');
            if (data.success) {
                // Update unit price (in case tier changed!)
                const unitPriceEl = document.getElementById('unitPrice-' + variantId);
                if (unitPriceEl && data.unit_price_formatted) {
                    unitPriceEl.textContent = data.unit_price_formatted;
                }
                // Update line total
                const lineTotalEl = document.getElementById('lineTotal-' + variantId);
                if (lineTotalEl && data.line_total_formatted) {
                    lineTotalEl.textContent = data.line_total_formatted;
                }
                // Update summary values
                document.getElementById('summarySubTotal').textContent = data.subtotal_formatted;
                document.getElementById('summaryTotal').textContent = data.total_formatted;

                const discountRow = document.getElementById('summaryDiscountRow');
                if (discountRow) {
                    if (data.discount > 0) {
                        discountRow.style.display = 'flex';
                        document.getElementById('summaryDiscount').textContent = data.discount_formatted;
                    } else {
                        discountRow.style.display = 'none';
                    }
                }

                updateHeaderBadge(data.cart_count);
                showToast('Cart updated.');
            } else {
                showToast(data.message || 'Could not update quantity.');
                // Revert input to previous value if available
                location.reload();
            }
        })
        .catch(err => {
            if (card) card.classList.remove('is-updating');
            showToast('Something went wrong. Please try again.');
        });
    }

    // Remove item via AJAX
    function sendRemove(variantId) {
        const card = document.getElementById('cartItem-' + variantId);
        if (card) card.classList.add('is-updating');

        fetch('{{ route('cart.remove') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                variant_id: variantId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (card) card.remove();
                document.getElementById('summarySubTotal').textContent = data.subtotal_formatted;
                document.getElementById('summaryTotal').textContent = data.total_formatted;

                const discountRow = document.getElementById('summaryDiscountRow');
                if (discountRow) {
                    if (data.discount > 0) {
                        discountRow.style.display = 'flex';
                        document.getElementById('summaryDiscount').textContent = data.discount_formatted;
                    } else {
                        discountRow.style.display = 'none';
                    }
                }

                updateHeaderBadge(data.cart_count);
                showToast('Item removed from cart.');

                if (data.is_empty) {
                    document.getElementById('cartLayoutGrid').style.display = 'none';
                    document.getElementById('cartEmptyState').style.display = 'block';
                }
            } else {
                if (card) card.classList.remove('is-updating');
                showToast(data.message || 'Could not remove item.');
            }
        })
        .catch(err => {
            if (card) card.classList.remove('is-updating');
            showToast('Something went wrong. Please try again.');
        });
    }

    // Plus button click
    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const variantId = this.dataset.variantId;
            const input = document.getElementById('qtyInput-' + variantId);
            if (!input) return;
            const max = input.getAttribute('max') ? parseInt(input.getAttribute('max'), 10) : null;
            let current = parseInt(input.value, 10) || 1;
            if (max !== null && current >= max) {
                showToast('Maximum available stock reached.');
                return;
            }
            current += 1;
            input.value = current;
            sendUpdate(variantId, current);
        });
    });

    // Minus button click
    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const variantId = this.dataset.variantId;
            const input = document.getElementById('qtyInput-' + variantId);
            if (!input) return;
            const min = parseInt(input.getAttribute('min'), 10) || 1;
            let current = parseInt(input.value, 10) || 1;
            if (current <= min) {
                showToast('Minimum order quantity is ' + min + '.');
                return;
            }
            current -= 1;
            input.value = current;
            sendUpdate(variantId, current);
        });
    });

    // Manual quantity input change
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', function () {
            const variantId = this.dataset.variantId;
            const min = parseInt(this.getAttribute('min'), 10) || 1;
            const max = this.getAttribute('max') ? parseInt(this.getAttribute('max'), 10) : null;
            let val = parseInt(this.value, 10);
            if (isNaN(val) || val < min) val = min;
            if (max !== null && val > max) val = max;
            this.value = val;
            sendUpdate(variantId, val);
        });
    });

    // Remove item button click
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function () {
            const variantId = this.dataset.variantId;
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                sendRemove(variantId);
            }
        });
    });
});
</script>
@endpush
