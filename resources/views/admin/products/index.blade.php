@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Products Management</h3>
            <p class="text-muted mb-0">Manage retail and wholesale trading products, pricing, stock and statuses.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-round">
            <i class="fa fa-plus me-1"></i> Add Product
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER PILLS --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.products.index') }}"
           class="btn btn-sm {{ !request('type') && !request('featured') && !request('status') ? 'btn-primary' : 'btn-outline-primary' }} btn-round">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.products.index', ['type' => 'wholesale']) }}"
           class="btn btn-sm {{ request('type') === 'wholesale' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} btn-round">
            <i class="fas fa-handshake me-1"></i> Wholesale / Trading ({{ $counts['wholesale'] }})
        </a>
        <a href="{{ route('admin.products.index', ['type' => 'retail']) }}"
           class="btn btn-sm {{ request('type') === 'retail' ? 'btn-info' : 'btn-outline-info' }} btn-round">
            <i class="fas fa-store me-1"></i> Retail / Marketplace ({{ $counts['retail'] }})
        </a>
        <a href="{{ route('admin.products.index', ['featured' => '1']) }}"
           class="btn btn-sm {{ request('featured') ? 'btn-success' : 'btn-outline-success' }} btn-round">
            <i class="fas fa-star me-1"></i> Featured ({{ $counts['featured'] }})
        </a>
        @if($counts['pending'] > 0)
            <a href="{{ route('admin.products.index', ['status' => 'pending']) }}"
               class="btn btn-sm {{ request('status') === 'pending' ? 'btn-danger' : 'btn-outline-danger' }} btn-round">
                <i class="fas fa-clock me-1"></i> Pending Approval ({{ $counts['pending'] }})
            </a>
        @endif
    </div>

    {{-- FILTER FORM --}}
    <div class="card card-round mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 align-items-center">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <div class="col-md-4">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search by title, SKU...">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->parent ? $cat->parent->name . ' > ' : '' }}{{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fa fa-search me-1"></i> Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- PRODUCTS TABLE --}}
    <div class="card card-round">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Price / MRP</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        @php
                            $price = $p->defaultVariant?->price ?: $p->min_price;
                            $mrp = $p->defaultVariant?->mrp ?: $p->max_price;
                            $stock = $p->defaultVariant?->stock ?? 0;
                            $sku = $p->defaultVariant?->sku ?? 'N/A';
                            $firstImg = $p->images->first()?->path;
                            $img = $firstImg ? asset($firstImg) : asset('assets/images/no-image.avif');
                            if (str_starts_with($firstImg ?? '', 'http')) {
                                $img = $firstImg;
                            }
                            $statusColor = match($p->status) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <tr>
                            <td>
                                <img src="{{ $img }}" alt="" style="width: 46px; height: 46px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $p->slug) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $p->title }}
                                </a>
                                <small class="text-muted d-block">SKU: {{ $sku }} &bull; Seller: <span class="fw-semibold text-secondary">{{ $p->seller?->name ?? 'Admin / In-House' }}</span></small>
                                @if($p->offer_text)
                                    <span class="badge bg-light text-danger border mt-1">{{ $p->offer_text }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $p->category?->name ?: 'Uncategorized' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->sale_type === 'wholesale' ? 'warning text-dark' : ($p->sale_type === 'both' ? 'info' : 'primary') }}">
                                    {{ ucfirst($p->sale_type) }}
                                </span>
                            </td>
                            <td>
                                <strong>₹{{ number_format($price ?? 0) }}</strong>
                                @if($mrp && $mrp > $price)
                                    <small class="text-muted text-decoration-line-through d-block">₹{{ number_format($mrp) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $stock > 0 ? 'success' : 'danger' }}">
                                    {{ number_format($stock) }} {{ $p->unit?->name ?: 'pcs' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-{{ $statusColor }} dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ ucfirst($p->status) }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" action="{{ route('admin.products.status', $p->slug) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="dropdown-item text-success"><i class="fa fa-check me-1"></i> Active</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.products.status', $p->slug) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="dropdown-item text-warning"><i class="fa fa-clock me-1"></i> Pending</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.products.status', $p->slug) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="draft">
                                                <button type="submit" class="dropdown-item text-secondary"><i class="fa fa-pencil me-1"></i> Draft</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.products.status', $p->slug) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="dropdown-item text-danger"><i class="fa fa-times me-1"></i> Rejected</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.toggle-featured', $p->slug) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $p->is_featured ? 'btn-warning text-dark' : 'btn-light border' }}" title="Toggle Featured">
                                        <i class="fa{{ $p->is_featured ? 's' : 'r' }} fa-star"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.products.show', $p->slug) }}" class="btn btn-sm btn-icon btn-light" title="View Product Details">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('products.show', $p->slug) }}" target="_blank" class="btn btn-sm btn-icon btn-light" title="Preview on Frontend">
                                    <i class="fa fa-external-link-alt"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $p->slug) }}" class="btn btn-sm btn-icon btn-info" title="Edit Product">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $p->slug) }}" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete Product">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-boxes fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No products match your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-footer d-flex justify-content-end p-2">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
