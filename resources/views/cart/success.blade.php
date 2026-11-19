@extends('layouts.trading')

@section('title', 'Order Placed — CC Hub')

@push('styles')
<style>
.success-container {
    max-width: 780px;
    margin: 40px auto 60px;
    padding: 0 16px;
}
.success-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 36px 32px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.success-icon-wrap {
    width: 72px;
    height: 72px;
    background: #e6f4ea;
    color: #137333;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin-bottom: 20px;
}
.success-title {
    font-family: 'DM Sans', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: #0f2a4a;
    margin: 0 0 8px;
}
.success-subtitle {
    color: #64748b;
    font-size: 15px;
    margin: 0 0 24px;
}
.order-pill {
    display: inline-block;
    background: #f1f5f9;
    padding: 6px 16px;
    border-radius: 9999px;
    font-family: monospace;
    font-size: 15px;
    font-weight: 700;
    color: #0f2a4a;
    letter-spacing: 0.5px;
    margin-bottom: 24px;
}
.success-details-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    text-align: left;
    margin-bottom: 28px;
}
.success-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.success-item-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #cbd5e1;
    font-size: 14px;
}
.success-item-row:last-child {
    border-bottom: none;
}
.success-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0f2a4a;
    color: #fff;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s ease;
}
.btn-primary-action:hover {
    background: #1b497c;
}
.btn-secondary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    color: #334155;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    transition: background .2s ease;
}
.btn-secondary-action:hover {
    background: #e2e8f0;
}
</style>
@endpush

@section('body')
<div class="success-container">
    <div class="success-card">
        <div class="success-icon-wrap">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 class="success-title">Inquiry / Order Placed Successfully!</h1>
        <p class="success-subtitle">
            Thank you, <strong>{{ $order->buyer_name }}</strong>. Your inquiry has been submitted directly to the seller(s).
        </p>

        <div class="order-pill">
            ORDER #{{ $order->order_no }}
        </div>

        <div class="success-details-box">
            <h3 class="success-section-title">
                <i class="fa-solid fa-box-open" style="color: #2563eb;"></i> Items Ordered ({{ $order->items->count() }})
            </h3>
            @foreach($order->items as $item)
                <div class="success-item-row">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        @if($item->sku) <span style="color: #64748b; font-size: 12px;">({{ $item->sku }})</span> @endif
                        <br />
                        <span style="font-size: 12px; color: #64748b;">
                            Qty: {{ (int) $item->qty }} {{ $item->unit_name }} &times; ₹{{ number_format($item->unit_price, 2) }}
                            ({{ ucfirst($item->price_type) }})
                        </span>
                    </div>
                    <div style="font-weight: 700; color: #0f2a4a;">
                        ₹{{ number_format($item->line_total, 2) }}
                    </div>
                </div>
            @endforeach

            <div style="margin-top: 14px; padding-top: 12px; border-top: 2px solid #cbd5e1; display: flex; justify-content: space-between; font-size: 16px;">
                <span style="font-weight: 700; color: #0f2a4a;">Total Amount:</span>
                <span style="font-weight: 800; color: #0f2a4a; font-size: 18px;">₹{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="success-details-box">
            <h3 class="success-section-title">
                <i class="fa-solid fa-location-dot" style="color: #dc2626;"></i> Delivery Destination
            </h3>
            @php $ship = $order->shipping_address ?? []; @endphp
            <p style="margin: 0; font-size: 14px; color: #334155; line-height: 1.6;">
                <strong>{{ $order->buyer_name }}</strong><br />
                {{ $ship['street'] ?? '' }}<br />
                {{ $ship['city'] ?? '' }}@if(!empty($ship['state'])), {{ $ship['state'] }}@endif @if(!empty($ship['pin'])) - {{ $ship['pin'] }}@endif<br />
                Phone: <strong>{{ $order->buyer_phone }}</strong>
                @if($order->buyer_email) | Email: {{ $order->buyer_email }} @endif
            </p>
        </div>

        <div class="success-actions">
            <a href="{{ url('/products') }}" class="btn-primary-action">
                <i class="fa-solid fa-store"></i> Continue Shopping
            </a>
            <a href="{{ url('/') }}" class="btn-secondary-action">
                <i class="fa-solid fa-house"></i> Home
            </a>
        </div>
    </div>
</div>
@endsection
