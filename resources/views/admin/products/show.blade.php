@extends('layouts.admin')

@section('body')
<div class="page-inner">
    @php
        $variant = $product->defaultVariant;
        $price = $variant?->price ?: $product->min_price;
        $mrp = $variant?->mrp ?: $product->max_price;
        $stock = $variant?->stock ?? 0;
        $firstImg = $product->images->first()?->path;
        $curImg = $firstImg ? asset($firstImg) : asset('assets/images/no-image.avif');
        if (str_starts_with($firstImg ?? '', 'http')) {
            $curImg = $firstImg;
        }
        $wholesaleTiers = $product->priceTiers ? $product->priceTiers->where('price_type', 'wholesale')->sortBy('min_qty')->values() : collect();
    @endphp

    {{-- Top Action Bar / Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-bold mb-0">{{ $product->title }}</h3>
                @if($product->sale_type === 'wholesale')
                    <span class="badge badge-warning text-dark"><i class="fas fa-handshake me-1"></i> Wholesale / Trading</span>
                @elseif($product->sale_type === 'retail')
                    <span class="badge badge-info"><i class="fas fa-store me-1"></i> Retail / Marketplace</span>
                @else
                    <span class="badge badge-primary"><i class="fas fa-cubes me-1"></i> Retail &amp; Wholesale</span>
                @endif

                @if($product->status === 'active')
                    <span class="badge badge-success"><i class="fa fa-check me-1"></i> Active</span>
                @elseif($product->status === 'pending')
                    <span class="badge badge-warning text-dark"><i class="fa fa-clock me-1"></i> Pending</span>
                @elseif($product->status === 'draft')
                    <span class="badge badge-secondary">Draft</span>
                @else
                    <span class="badge badge-danger">Rejected</span>
                @endif

                @if($product->is_featured)
                    <span class="badge badge-warning text-dark"><i class="fa fa-star me-1"></i> Featured</span>
                @endif
                @if($product->is_assured)
                    <span class="badge badge-success"><i class="fa fa-check-shield me-1"></i> Assured</span>
                @endif
            </div>
            <p class="text-muted mb-0">Product ID: <strong>#{{ $product->id }}</strong> &bull; Slug: <code>{{ $product->slug }}</code> &bull; Added {{ $product->created_at?->format('d M, Y') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline-info btn-round">
                <i class="fa fa-external-link-alt me-1"></i> View on Frontend
            </a>
            <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn btn-primary btn-round">
                <i class="fa fa-edit me-1"></i> Edit Product
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-round">
                <i class="fa fa-arrow-left me-1"></i> Back to Products
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
        {{-- Left column: Key Information & Wholesale Price Tiers --}}
        <div class="col-lg-8">
            {{-- Product Summary Card --}}
            <div class="card card-round mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Overview &amp; Base Pricing</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block text-uppercase fw-semibold">Base Price</small>
                                <h4 class="fw-bold text-primary mb-0">₹{{ number_format($price, 2) }}</h4>
                                <small class="text-muted">per {{ $product->unit?->name ?? 'unit' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block text-uppercase fw-semibold">MRP (List Price)</small>
                                <h4 class="fw-bold text-secondary mb-0">
                                    {{ $mrp ? '₹' . number_format($mrp, 2) : '—' }}
                                </h4>
                                @if($mrp && $mrp > $price)
                                    <small class="text-success fw-bold">{{ round((($mrp - $price) / $mrp) * 100) }}% OFF</small>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block text-uppercase fw-semibold">Available Stock</small>
                                <h4 class="fw-bold {{ $stock > 0 ? 'text-success' : 'text-danger' }} mb-0">{{ number_format($stock) }}</h4>
                                <small class="text-muted">{{ $product->unit?->name ?? 'Units' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block text-uppercase fw-semibold">SKU</small>
                                <h6 class="fw-bold text-dark mb-0 text-truncate" title="{{ $variant?->sku ?? 'N/A' }}">
                                    {{ $variant?->sku ?? 'N/A' }}
                                </h6>
                                <small class="text-muted">Default Variant</small>
                            </div>
                        </div>
                    </div>

                    @if($product->short_description)
                        <div class="mt-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Short Description</h6>
                            <p class="mb-0 text-dark">{{ $product->short_description }}</p>
                        </div>
                    @endif

                    @if($product->offer_text)
                        <div class="mt-3">
                            <span class="badge badge-success px-3 py-2">
                                <i class="fas fa-tag me-1"></i> Offer Badge: {{ $product->offer_text }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Wholesale / Trading Specifications Card --}}
            <div class="card card-round mb-3 border-warning">
                <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-warning-emphasis"><i class="fas fa-boxes me-2"></i>Wholesale &amp; Bulk Trading Specifications</h5>
                        <small class="text-muted">Order limits and packaging rules configured for wholesale buyers.</small>
                    </div>
                    <span class="badge {{ in_array($product->sale_type, ['wholesale', 'both']) ? 'badge-warning text-dark' : 'badge-light' }}">
                        {{ strtoupper($product->sale_type) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-1">Minimum Order Qty (MOQ)</small>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h3 class="fw-bold text-warning-emphasis mb-0">{{ $product->min_order_qty ?? 1 }}</h3>
                                    <span class="text-muted">{{ $product->unit?->name ?? 'units' }}</span>
                                </div>
                                <small class="text-muted d-block mt-1">Minimum quantity required to checkout in wholesale.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-1">Maximum Order Qty</small>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h3 class="fw-bold text-dark mb-0">{{ $product->max_order_qty ? number_format($product->max_order_qty) : 'Unlimited' }}</h3>
                                    @if($product->max_order_qty)
                                        <span class="text-muted">{{ $product->unit?->name ?? 'units' }}</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">Maximum cap per single order transaction.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-1">Pack / Case Quantity</small>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h3 class="fw-bold text-info mb-0">{{ $product->pack_qty ?? 1 }}</h3>
                                    <span class="text-muted">units / pack</span>
                                </div>
                                <small class="text-muted d-block mt-1">Number of individual units packed per master carton.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wholesale Volume Price Tiers Card --}}
            <div class="card card-round mb-3 border-primary">
                <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-primary"><i class="fas fa-layer-group me-2"></i>Wholesale Volume Price Tiers</h5>
                        <small class="text-muted">Automated volume pricing tiers applied to orders based on purchase quantity.</small>
                    </div>
                    <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn btn-sm btn-outline-primary btn-round">
                        <i class="fa fa-edit me-1"></i> Edit Tiers
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($wholesaleTiers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 12%;">Tier</th>
                                        <th style="width: 28%;">Quantity Range</th>
                                        <th style="width: 25%;">Unit Price</th>
                                        <th style="width: 20%;">Savings vs Base</th>
                                        <th class="pe-3 text-end" style="width: 15%;">Min Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wholesaleTiers as $idx => $tier)
                                        @php
                                            $tierPrice = (float) $tier->price;
                                            $savings = $price > $tierPrice ? $price - $tierPrice : 0;
                                            $savingsPct = $price > 0 && $savings > 0 ? round(($savings / $price) * 100, 1) : 0;
                                            $minTotal = $tierPrice * $tier->min_qty;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge badge-primary rounded-pill">#{{ $idx + 1 }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ number_format($tier->min_qty) }}</strong>
                                                @if($tier->max_qty)
                                                    <span class="text-muted">&ndash;</span> <strong class="text-dark">{{ number_format($tier->max_qty) }}</strong> {{ $product->unit?->name ?? 'units' }}
                                                @else
                                                    <span class="text-muted">+ units</span> <span class="badge badge-light border ms-1">No limit</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fs-6 fw-bold text-success">₹{{ number_format($tierPrice, 2) }}</span>
                                                <small class="text-muted">/ {{ $product->unit?->name ?? 'unit' }}</small>
                                            </td>
                                            <td>
                                                @if($savingsPct > 0)
                                                    <span class="badge badge-success">Save {{ $savingsPct }}%</span>
                                                    <small class="text-muted d-block">(₹{{ number_format($savings, 2) }} off)</small>
                                                @else
                                                    <span class="text-muted">Base rate</span>
                                                @endif
                                            </td>
                                            <td class="pe-3 text-end">
                                                <strong class="text-dark">₹{{ number_format($minTotal, 2) }}</strong>
                                                <small class="text-muted d-block">for {{ $tier->min_qty }} {{ $product->unit?->name ?? 'units' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 px-3">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3 opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Wholesale Price Tiers Configured</h6>
                            <p class="text-muted mb-3" style="max-width: 480px; margin: 0 auto;">
                                @if($product->sale_type === 'retail')
                                    This product is marked as <strong>Retail / Marketplace</strong>. If you want to enable wholesale trading and volume-based pricing for bulk buyers, edit the product and set the sale type to <strong>Wholesale</strong> or <strong>Both</strong>.
                                @else
                                    This product does not have volume discount tiers yet. Bulk buyers will be charged the base price of <strong>₹{{ number_format($price, 2) }}</strong>.
                                @endif
                            </p>
                            <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn btn-primary btn-round">
                                <i class="fa fa-plus me-1"></i> Add Wholesale Price Tiers
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if($product->description)
                <div class="card card-round mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Detailed Description</h5></div>
                    <div class="card-body">
                        <div class="text-muted">{!! nl2br(e($product->description)) !!}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column: Media, Category, Brand, Specs --}}
        <div class="col-lg-4">
            {{-- Image Card --}}
            <div class="card card-round mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Product Image</h5></div>
                <div class="card-body text-center">
                    <img src="{{ $curImg }}" alt="{{ $product->title }}" class="img-fluid border rounded p-2" style="max-height: 220px; object-fit: contain;">
                    @if($product->images->count() > 1)
                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                            @foreach($product->images as $img)
                                <img src="{{ str_starts_with($img->path, 'http') ? $img->path : asset($img->path) }}" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" class="border">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Catalog Meta Card --}}
            <div class="card card-round mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Catalog Details</h5></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted">Category</span>
                            <strong>{{ $product->category?->name ?? 'Uncategorized' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted">Brand</span>
                            <strong>{{ $product->brand?->name ?? 'Generic / None' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted">Measurement Unit</span>
                            <strong>{{ $product->unit?->name ?? 'Piece' }} ({{ $product->unit?->code ?? 'pc' }})</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted">Model Number</span>
                            <strong>{{ $product->model_no ?: '—' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted">Ratings</span>
                            <span class="text-warning">
                                <i class="fa fa-star"></i> {{ number_format($product->rating_avg ?? 0, 1) }}
                                <span class="text-muted">({{ $product->rating_count ?? 0 }})</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card card-round">
                <div class="card-header"><h5 class="card-title mb-0">Management Actions</h5></div>
                <div class="card-body">
                    <a href="{{ route('admin.products.edit', $product->slug) }}" class="btn btn-primary w-100 btn-round mb-2">
                        <i class="fa fa-edit me-1"></i> Edit Product &amp; Tiers
                    </a>
                    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline-info w-100 btn-round mb-2">
                        <i class="fa fa-external-link-alt me-1"></i> View on Store
                    </a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product->slug) }}" onsubmit="return confirm('Are you sure you want to delete this product? All tiers will also be deleted.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 btn-round">
                            <i class="fa fa-trash me-1"></i> Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
