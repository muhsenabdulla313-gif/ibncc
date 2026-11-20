@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-1">Trading &amp; Commerce Dashboard</h3>
            <h6 class="op-7 mb-0">Overview of products, wholesale trading, orders and catalog</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-round">
                <i class="fa fa-plus me-1"></i> Add Product
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-label-info btn-round">
                <i class="fa fa-shopping-cart me-1"></i> View Orders
            </a>
            <a href="{{ url('/trading') }}" target="_blank" class="btn btn-label-success btn-round">
                <i class="fa fa-external-link-alt me-1"></i> View Trading Site
            </a>
        </div>
    </div>

    {{-- STATS ROW --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Products</p>
                                <h4 class="card-title">{{ number_format($totalProducts) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-handshake"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Wholesale Trading</p>
                                <h4 class="card-title">{{ number_format($tradingProducts) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Sales</p>
                                <h4 class="card-title">₹{{ number_format($totalSales, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Orders Placed</p>
                                <h4 class="card-title">{{ number_format($totalOrders) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK STATS SUB-ROW --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted d-block small">Retail Products</span>
                        <strong class="fs-5">{{ $retailProducts }}</strong>
                    </div>
                    <span class="badge bg-primary"><i class="fas fa-store"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted d-block small">Trading Categories</span>
                        <strong class="fs-5">{{ $tradingCategories }}</strong>
                    </div>
                    <span class="badge bg-warning"><i class="fas fa-layer-group"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted d-block small">Pending Orders</span>
                        <strong class="fs-5 text-warning">{{ $placedOrders }}</strong>
                    </div>
                    <span class="badge bg-danger"><i class="fas fa-clock"></i></span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stats card-round p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted d-block small">Active Banners</span>
                        <strong class="fs-5">{{ $activeBanners }}</strong>
                    </div>
                    <span class="badge bg-info"><i class="fas fa-images"></i></span>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT ORDERS & PRODUCTS --}}
    <div class="row">
        {{-- Recent Orders --}}
        <div class="col-md-7">
            <div class="card card-round">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Orders</h4>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link">View All &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Buyer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    @php
                                        $badgeColor = match($order->status) {
                                            'placed' => 'warning',
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $order->order_no }}</strong></td>
                                        <td>{{ $order->buyer_name }}</td>
                                        <td>₹{{ number_format($order->total, 2) }}</td>
                                        <td><span class="badge bg-{{ $badgeColor }}">{{ ucfirst($order->status) }}</span></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-xs btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Products --}}
        <div class="col-md-5">
            <div class="card card-round">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Products</h4>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-link">View All &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $p)
                                    @php
                                        $price = $p->defaultVariant?->price ?: $p->min_price;
                                        $img = $p->images->first()?->path ? asset($p->images->first()->path) : asset('assets/images/no-image.avif');
                                        if (str_starts_with($p->images->first()?->path ?? '', 'http')) {
                                            $img = $p->images->first()->path;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $img }}" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                                <div>
                                                    <a href="{{ route('admin.products.edit', $p->slug) }}" class="text-dark fw-bold text-decoration-none">
                                                        {{ Str::limit($p->title, 24) }}
                                                    </a>
                                                    <small class="text-muted d-block">{{ $p->category?->name ?: 'General' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $p->sale_type === 'wholesale' ? 'warning' : ($p->sale_type === 'both' ? 'info' : 'primary') }}">
                                                {{ ucfirst($p->sale_type) }}
                                            </span>
                                        </td>
                                        <td>₹{{ number_format($price ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No products found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
