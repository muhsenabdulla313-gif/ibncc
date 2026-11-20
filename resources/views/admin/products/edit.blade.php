@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Edit Product: {{ $product->title }}</h3>
            <p class="text-muted mb-0">Update pricing, sale type, status, and catalog properties.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline-info btn-round">
                <i class="fa fa-eye me-1"></i> View on Frontend
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $variant = $product->defaultVariant;
        $price = $variant?->price ?: $product->min_price;
        $mrp = $variant?->mrp ?: $product->max_price;
        $stock = $variant?->stock ?? 100;
        $firstImg = $product->images->first()?->path;
        $curImg = $firstImg ? asset($firstImg) : asset('assets/images/no-image.avif');
        if (str_starts_with($firstImg ?? '', 'http')) {
            $curImg = $firstImg;
        }
    @endphp

    <form method="POST" action="{{ route('admin.products.update', $product->slug) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            {{-- Left column: Basic info & Pricing --}}
            <div class="col-md-8">
                <div class="card card-round mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $product->title) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Model / Item No</label>
                                <input type="text" name="model_no" class="form-control" value="{{ old('model_no', $product->model_no) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Brand</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Unit <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Short Description / Subtitle</label>
                            <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $product->short_description) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Description</label>
                            <textarea name="description" rows="5" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pricing & Stock Card --}}
                <div class="card card-round mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Pricing &amp; Stock</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Selling Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $price) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">MRP (₹)</label>
                                <input type="number" step="0.01" name="mrp" class="form-control" value="{{ old('mrp', $mrp) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $stock) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Offer / Discount Badge Text</label>
                            <input type="text" name="offer_text" class="form-control" value="{{ old('offer_text', $product->offer_text) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right column: Organization, Media & Status --}}
            <div class="col-md-4">
                {{-- Type & Status --}}
                <div class="card card-round mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Trading Configuration</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sale Type <span class="text-danger">*</span></label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="sale_type" id="typeWholesale" value="wholesale" {{ old('sale_type', $product->sale_type) === 'wholesale' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeWholesale">
                                    <strong class="text-warning">Wholesale / Trading Hub</strong>
                                    <small class="d-block text-muted">Bulk trading, MOQ &amp; volume tiers</small>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="sale_type" id="typeRetail" value="retail" {{ old('sale_type', $product->sale_type) === 'retail' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeRetail">
                                    <strong class="text-primary">Retail / Marketplace</strong>
                                    <small class="d-block text-muted">Standard e-commerce single purchase</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sale_type" id="typeBoth" value="both" {{ old('sale_type', $product->sale_type) === 'both' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeBoth">
                                    <strong class="text-info">Both (Retail &amp; Wholesale)</strong>
                                    <small class="d-block text-muted">Available across both portals</small>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active (Published)</option>
                                <option value="pending" {{ old('status', $product->status) === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="rejected" {{ old('status', $product->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="isFeatured">
                                <i class="fas fa-star text-warning me-1"></i> Featured Product
                            </label>
                            <small class="text-muted d-block">Shows in Marketplace carousel / Featured picks</small>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_assured" value="1" id="isAssured" {{ old('is_assured', $product->is_assured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="isAssured">
                                <i class="fas fa-check-shield text-success me-1"></i> IBNCC Assured
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Primary Image --}}
                <div class="card card-round mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Product Image</h5></div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="{{ $curImg }}" alt="" style="max-height: 140px; border-radius: 8px;" class="border p-1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Replace with File</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="text-center my-2 text-muted fw-bold">OR</div>
                        <div class="mb-3">
                            <label class="form-label">Replace with Image URL</label>
                            <input type="url" name="image_url" class="form-control" value="{{ old('image_url', str_starts_with($firstImg ?? '', 'http') ? $firstImg : '') }}" placeholder="https://images.unsplash.com/...">
                        </div>
                    </div>
                </div>

                <div class="card card-round">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 btn-round mb-2">
                            <i class="fa fa-save me-1"></i> Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light w-100 btn-round">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
