@extends('layouts.trading')

@section('title', 'Checkout & Inquiry — CC Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cchub/payment.css') }}" />
<style>
.checkout-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 14px;
}
.checkout-form-grid .full-width {
    grid-column: span 2;
}
.pay-input-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.pay-input-group label span.req {
    color: #dc2626;
}
.pay-input-group input,
.pay-input-group textarea,
.pay-input-group select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.pay-input-group input:focus,
.pay-input-group textarea:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.addr-pill-btn {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 12px;
    color: #1e293b;
    cursor: pointer;
    text-align: left;
    transition: all .15s ease;
}
.addr-pill-btn:hover {
    border-color: #2563eb;
    background: #eff6ff;
}
.badge-trading-sm {
    background: #e6f4ea;
    color: #137333;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}
.badge-retail-sm {
    background: #e8f0fe;
    color: #1a73e8;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}
</style>
@endpush

@section('body')
<main class="payment-main" id="main-content">
    <div class="payment-container">
        <!-- Breadcrumbs -->
        <nav class="payment-breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}"><i class="fa-solid fa-house"></i> Home</a>
            <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            @if($checkoutType === 'cart')
                <a href="{{ route('cart.index') }}">Shopping Cart</a>
                <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            @else
                <a href="{{ url('/products') }}">Direct Purchase</a>
                <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
            @endif
            <span class="current">Checkout</span>
        </nav>

        @if(session('error'))
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <strong style="display: block; margin-bottom: 4px;">Please correct the following errors:</strong>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.process') }}" id="checkoutForm">
            @csrf
            <input type="hidden" name="checkout_type" value="{{ $checkoutType }}" />

            <div class="payment-layout-grid">
                <!-- 1. LEFT COLUMN: Contact & Delivery Address -->
                <div class="payment-details-col">
                    <!-- Section: Delivery Information -->
                    <section class="pay-address-card" aria-labelledby="deliveryAddressTitle">
                        <div class="pay-address-head">
                            <div class="pay-address-title-group">
                                <span class="pay-address-icon-badge" aria-hidden="true">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>
                                <h2 class="pay-address-title" id="deliveryAddressTitle">
                                    Contact & Delivery Information
                                </h2>
                            </div>
                        </div>

                        @if(!empty($savedAddresses))
                            <div style="margin-top: 14px;">
                                <span style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Saved Addresses:</span>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;">
                                    @foreach($savedAddresses as $sa)
                                        <button type="button" class="addr-pill-btn"
                                                onclick="fillAddress('{{ addslashes($sa['name']) }}', '{{ addslashes($sa['phone']) }}', '{{ addslashes($sa['street']) }}', '{{ addslashes($sa['city']) }}', '{{ addslashes($sa['state']) }}', '{{ addslashes($sa['pin']) }}')">
                                            <strong>{{ ucfirst($sa['type']) }}:</strong> {{ $sa['street'] }}, {{ $sa['city'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="checkout-form-grid">
                            <div class="pay-input-group">
                                <label for="buyer_name">Full Name <span class="req">*</span></label>
                                <input type="text" id="buyer_name" name="buyer_name" value="{{ old('buyer_name', $defaultAddress['name']) }}" required placeholder="e.g. John Doe" />
                            </div>

                            <div class="pay-input-group">
                                <label for="buyer_phone">Phone Number <span class="req">*</span></label>
                                <input type="tel" id="buyer_phone" name="buyer_phone" value="{{ old('buyer_phone', $defaultAddress['phone']) }}" required placeholder="e.g. +91 98765 43210" />
                            </div>

                            <div class="pay-input-group full-width">
                                <label for="buyer_email">Email Address <span style="color: #64748b; font-weight: 400;">(Optional)</span></label>
                                <input type="email" id="buyer_email" name="buyer_email" value="{{ old('buyer_email', $defaultAddress['email']) }}" placeholder="e.g. you@example.com" />
                            </div>

                            <div class="pay-input-group full-width">
                                <label for="street">Street Address / House <span class="req">*</span></label>
                                <input type="text" id="street" name="street" value="{{ old('street', $defaultAddress['street']) }}" required placeholder="House name, Flat No, Street, Landmark" />
                            </div>

                            <div class="pay-input-group">
                                <label for="city">City / District <span class="req">*</span></label>
                                <input type="text" id="city" name="city" value="{{ old('city', $defaultAddress['city']) }}" required placeholder="e.g. Ernakulam" />
                            </div>

                            <div class="pay-input-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" value="{{ old('state', $defaultAddress['state']) }}" placeholder="e.g. Kerala" />
                            </div>

                            <div class="pay-input-group">
                                <label for="pin">PIN / Postal Code</label>
                                <input type="text" id="pin" name="pin" value="{{ old('pin', $defaultAddress['pin']) }}" placeholder="e.g. 682001" />
                            </div>
                        </div>
                    </section>

                    <!-- Section: Additional Notes / Message -->
                    <section class="pay-contact-card" style="margin-top: 24px;" aria-labelledby="orderContactTitle">
                        <div class="pay-address-head">
                            <div class="pay-address-title-group">
                                <span class="pay-address-icon-badge" aria-hidden="true">
                                    <i class="fa-regular fa-comment-dots"></i>
                                </span>
                                <h2 class="pay-address-title" id="orderContactTitle">
                                    Inquiry & Delivery Notes
                                    <span style="font-size: 13px; font-weight: 400; color: #64748b;">(Optional)</span>
                                </h2>
                            </div>
                        </div>

                        <div style="margin-top: 14px;">
                            <div class="pay-input-group full-width">
                                <textarea id="buyer_note" name="buyer_note" rows="4"
                                          placeholder="Provide any specific delivery requirements, logistics instructions, or questions for the seller…">{{ old('buyer_note') }}</textarea>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- 2. RIGHT COLUMN: Order Summary & Item Previews -->
                <aside class="payment-summary-col" aria-label="Order Summary">
                    <div class="pay-summary-card">
                        <h2 class="pay-summary-title">Summary & Inquiry</h2>

                        <div class="pay-summary-table">
                            <div class="pay-sum-row">
                                <span class="pay-sum-label">Items Sub Total</span>
                                <span class="pay-sum-val" id="summarySubTotal">₹{{ number_format($subtotal, 2) }}</span>
                            </div>

                            @if($discount > 0)
                                <div class="pay-sum-row pay-sum-row--discount">
                                    <span class="pay-sum-label">Savings</span>
                                    <span class="pay-sum-val">- ₹{{ number_format($discount, 2) }}</span>
                                </div>
                            @endif

                            <div class="pay-sum-row pay-sum-row--delivery">
                                <span class="pay-sum-label">Delivery Charge</span>
                                <span class="pay-sum-val">FREE</span>
                            </div>

                            <div class="pay-sum-divider" aria-hidden="true"></div>

                            <div class="pay-sum-row pay-sum-row--total">
                                <span class="pay-sum-label">TOTAL ESTIMATE</span>
                                <span class="pay-sum-val pay-sum-total-highlight">₹{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Submit Order Button -->
                        <button type="submit" class="btn-pay-now" id="btnPayNow" style="margin-top: 20px;">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            Send Inquiry / Place Order
                        </button>

                        <div class="pay-security-note">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <span>Direct Seller Communication & Verified IBNCC Trade</span>
                        </div>
                    </div>

                    <!-- Order Items Mini Preview (Collapsible) -->
                    <details class="pay-items-preview" open style="margin-top: 20px;">
                        <summary style="font-weight: 600; color: #0f2a4a; cursor: pointer; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <span>Items in this Order ({{ count($items) }})</span>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </summary>

                        <div class="pay-items-mini-list" style="border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; padding: 12px 16px; background: #fff;">
                            @foreach($items as $item)
                                <div class="mini-item-row" style="display: flex; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="mini-item-img"
                                         style="width: 52px; height: 52px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;"
                                         onerror="this.src='{{ asset('assets/images/no-image.avif') }}'" />
                                    <div class="mini-item-info" style="flex: 1;">
                                        <div style="margin-bottom: 2px;">
                                            @if($item['mode'] === 'wholesale')
                                                <span class="badge-trading-sm">Wholesale</span>
                                            @else
                                                <span class="badge-retail-sm">Retail</span>
                                            @endif
                                        </div>
                                        <p class="mini-item-title" style="font-weight: 600; font-size: 13px; margin: 0; color: #0f2a4a;">
                                            {{ $item['title'] }}
                                        </p>
                                        @if($item['variant_specs'])
                                            <p style="font-size: 11px; color: #64748b; margin: 2px 0;">
                                                {{ $item['variant_specs'] }}
                                            </p>
                                        @endif
                                        <p class="mini-item-qty" style="font-size: 12px; color: #475569; margin: 0;">
                                            Qty: {{ (int) $item['qty'] }} {{ $item['unit_name'] }} &times; ₹{{ number_format($item['unit_price'], 2) }}
                                        </p>
                                    </div>
                                    <span class="mini-item-price" style="font-weight: 700; font-size: 14px; color: #0f2a4a;">
                                        ₹{{ number_format($item['line_total'], 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </aside>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
function fillAddress(name, phone, street, city, state, pin) {
    if (name)   document.getElementById('buyer_name').value = name;
    if (phone)  document.getElementById('buyer_phone').value = phone;
    if (street) document.getElementById('street').value = street;
    if (city)   document.getElementById('city').value = city;
    if (state)  document.getElementById('state').value = state;
    if (pin)    document.getElementById('pin').value = pin;
}

document.getElementById('checkoutForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnPayNow');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing Order...';
    }
});
</script>
@endpush
