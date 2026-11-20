@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Order #{{ $order->order_no }}</h3>
            <p class="text-muted mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }} ({{ $order->created_at->diffForHumans() }})</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-round">
                <i class="fa fa-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Left column: Items table --}}
        <div class="col-md-8">
            <div class="card card-round mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Order Items ({{ $order->items->count() }})</h5>
                    <span class="badge bg-primary">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $img = $item->product?->images?->first()?->path;
                                    $imgUrl = $img ? asset($img) : asset('assets/images/no-image.avif');
                                    if (str_starts_with($img ?? '', 'http')) {
                                        $imgUrl = $img;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $imgUrl }}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                                            <div>
                                                <strong class="d-block">{{ $item->title }}</strong>
                                                <small class="text-muted">SKU: {{ $item->sku ?: 'N/A' }} | Seller: {{ $item->seller_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->price_type === 'wholesale' ? 'warning text-dark' : 'info' }}">
                                            {{ ucfirst($item->price_type) }}
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td><strong>{{ number_format($item->qty) }} {{ $item->unit_name ?: 'pcs' }}</strong></td>
                                    <td><strong class="text-primary">₹{{ number_format($item->line_total, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tax Amount:</span>
                        <span>₹{{ number_format($order->tax_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span>Grand Total:</span>
                        <span class="text-primary">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($order->buyer_note)
                <div class="card card-round">
                    <div class="card-header"><h5 class="card-title mb-0">Buyer Note</h5></div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $order->buyer_note }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column: Customer, Shipping & Status update --}}
        <div class="col-md-4">
            {{-- Status Update Card --}}
            <div class="card card-round mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Update Order Status</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="placed" {{ $order->status === 'placed' ? 'selected' : '' }}>Placed</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-round">
                            <i class="fa fa-save me-1"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            {{-- Buyer Details --}}
            <div class="card card-round mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Buyer Information</h5></div>
                <div class="card-body">
                    <p class="mb-1 fw-bold fs-6">{{ $order->buyer_name }}</p>
                    <p class="mb-1 text-muted"><i class="fa fa-phone me-1"></i> {{ $order->buyer_phone }}</p>
                    @if($order->buyer_email)
                        <p class="mb-0 text-muted"><i class="fa fa-envelope me-1"></i> {{ $order->buyer_email }}</p>
                    @endif
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="card card-round">
                <div class="card-header"><h5 class="card-title mb-0">Shipping Address</h5></div>
                <div class="card-body">
                    @php $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true); @endphp
                    @if($addr)
                        <address class="mb-0 text-muted lh-base">
                            <strong class="text-dark d-block mb-1">{{ $addr['name'] ?? $order->buyer_name }}</strong>
                            {{ $addr['address_line1'] ?? '' }}<br>
                            @if(!empty($addr['address_line2']))
                                {{ $addr['address_line2'] }}<br>
                            @endif
                            {{ $addr['city'] ?? '' }}{{ !empty($addr['state']) ? ', ' . $addr['state'] : '' }} {{ $addr['postal_code'] ?? ($addr['pincode'] ?? '') }}<br>
                            {{ $addr['country'] ?? 'India' }}<br>
                            <span class="d-block mt-2"><i class="fa fa-phone me-1"></i> Phone: {{ $addr['phone'] ?? $order->buyer_phone }}</span>
                        </address>
                    @else
                        <p class="text-muted mb-0">No address recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
