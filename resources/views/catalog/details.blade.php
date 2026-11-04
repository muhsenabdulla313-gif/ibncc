
@extends('layouts.trading')
@php
    // Get only attributes marked as variant axes
    $variantAxes = $product->variants
        ->flatMap(fn ($variant) => $variant->attributeValues)
        ->filter(fn ($value) => $value->attribute && $value->attribute->is_variant_axis)
        ->groupBy('attribute_id');

    // Get unique options for each variant attribute
    $variantAxes = $variantAxes->map(function ($values) {
        return [
            'attribute' => $values->first()->attribute,
            'options' => $values
                ->filter(fn ($value) => $value->option)
                ->unique('attribute_option_id')
                ->pluck('option')
                ->values(),
        ];
    });

    // Default variant
    $selectedVariant = $product->variants->firstWhere('is_default', true)
        ?? $product->variants->first();

    // Prepare variant data for JavaScript
    $variantData = $product->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'price' => $variant->price,
            'stock' => $variant->stock,

            'options' => $variant->attributeValues
                ->pluck('attribute_option_id', 'attribute_id')
                ->toArray(),

            'images' => $variant->images->map(function ($image) {
                return asset('storage/' . $image->path);
            })->values()->toArray(),
        ];
    })->values()->toArray();
@endphp
@push('styles')

{{-- Google Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@300;400;500;600;700&family=Manrope:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

{{-- Font Awesome --}}
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

{{-- Product Page CSS --}}
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/cchub/ch-product-details.css') }}">

@endpush
@section('body')

@php
    $mainImage = $product->images->first();
@endphp

<main class="pd-main">

    <article class="pd-product">

        {{-- Breadcrumb --}}
        <nav class="pl-breadcrumb pd-breadcrumb">
            <a href="{{ route('trading') }}">Home</a>
            <span>›</span>

            <a href="{{ route('trading.products.index') }}">
                Products
            </a>

            <span>›</span>

            <span>{{ $product->category?->name }}</span>

            <span>›</span>

            <span>{{ $product->name }}</span>
        </nav>

        <section class="pd-hero">

            {{-- PRODUCT IMAGES --}}
            <div class="pd-gallery">

                <div class="pd-gallery-set is-active">

                    <div class="pd-main-media">
                        <img
                            class="pd-main-image"
                            src="{{ $mainImage ? asset('storage/'.$mainImage->path) : asset('assets/images/mb.jpg') }}"
                            alt="{{ $product->name }}"
                        >
                    </div>

                    <div class="pd-thumb-row">

                        <div class="pd-thumbs">

                            @foreach($product->images as $image)
                                <button
                                    type="button"
                                    class="pd-thumb {{ $loop->first ? 'is-active' : '' }}"
                                    data-thumb-src="{{ asset('storage/'.$image->path) }}"
                                >
                                    <img
                                        src="{{ asset('storage/'.$image->path) }}"
                                        alt="{{ $product->name }}"
                                    >
                                </button>
                            @endforeach

                        </div>

                    </div>

                </div>

            </div>


            {{-- PRODUCT INFORMATION --}}
            <div class="pd-info">

                <div class="pd-info-top">

                    <span class="pd-brand">
                        {{ $product->brand?->name ?? 'Brand' }}
                    </span>

                    <div class="pd-info-actions">

                        <button type="button" class="pd-icon-btn">
                            <i class="fa-solid fa-share-nodes"></i>
                        </button>

                        <button
                            type="button"
                            class="pd-icon-btn pd-icon-wish"
                            data-wish="{{ $product->id }}"
                        >
                            <i class="fa-regular fa-heart"></i>
                        </button>

                    </div>

                </div>


                {{-- NAME --}}
                <h1 class="pd-title">
                    {{ $product->name }}
                </h1>


                {{-- DESCRIPTION --}}
                <p class="pd-specs">
                    {{ $product->description }}
                </p>

{{-- PRICE --}}
<div class="pd-price-wrap">

    <span class="pd-price" data-product-price>
        ₹{{ number_format(
            $selectedVariant?->price
                ?? ($product->sale_type === 'market'
                    ? $product->wholesale_price
                    : $product->price)
                ?? 0,
            2
        ) }}
    </span>

</div>

{{-- STOCK --}}
<p
    class="pl-stock {{ ($selectedVariant?->stock ?? $product->stock) > 0 ? 'is-in' : 'is-out' }}"
    data-product-stock
>
    @if(($selectedVariant?->stock ?? $product->stock) > 0)
        <i class="fa-solid fa-circle-check"></i>
        In Stock
    @else
        <i class="fa-solid fa-circle-xmark"></i>
        Out of Stock
    @endif
</p>
                {{-- PRICE --}}
                <div class="pd-price-wrap">

                    @if($product->sale_type === 'market')

                        <span class="pd-price">
                            ₹{{ number_format($product->wholesale_price ?? $product->price, 2) }}
                        </span>

                    @else

                        <span class="pd-price">
                            ₹{{ number_format($product->price, 2) }}
                        </span>

                    @endif

                </div>
{{-- PRODUCT VARIANTS --}}
@if($variantAxes->isNotEmpty())
    <div class="pd-variant-selectors">

        @foreach($variantAxes as $attributeId => $axis)
            @php
                $selectedOptionId = $selectedVariant
    ? $selectedVariant->attributeValues
        ->firstWhere('attribute_id', $attributeId)
        ?->attribute_option_id
    : null;
            @endphp

            <div class="pd-prop-group">

                <span class="pd-prop-title">
                    {{ $axis['attribute']->name }}
                </span>

                <div
                    class="pd-prop-options"
                    role="radiogroup"
                    aria-label="{{ $axis['attribute']->name }}"
                >

                    @foreach($axis['options'] as $option)
                        <label class="pd-prop-option">

                            <input
                                type="radio"
                                name="variant_attribute_{{ $attributeId }}"
                                value="{{ $option->id }}"
                                data-attribute-id="{{ $attributeId }}"
                                @checked($selectedOptionId == $option->id)
                            >

                            <span
                                @if($axis['attribute']->name === 'Color' && $option->color_hex)
                                    style="--variant-color: {{ $option->color_hex }}"
                                @endif
                            >
                                {{ $option->label }}
                            </span>

                        </label>
                    @endforeach

                </div>
            </div>
        @endforeach

    </div>
@endif

{{-- Selected variant ID for cart/order --}}
<input
    type="hidden"
    name="variant_id"
    id="selectedVariantId"
    value="{{ $selectedVariant?->id }}"
>

                {{-- QUANTITY --}}
                <div class="pd-qty-row">

                    <span class="pd-qty-label">Quantity:</span>

                    <div class="cart-qty-control">

                        <button type="button"
                                class="cart-qty-btn btn-qty-minus">
                            <i class="fa-solid fa-minus"></i>
                        </button>

                        <span class="cart-qty-number">1</span>

                        <button type="button"
                                class="cart-qty-btn btn-qty-plus">
                            <i class="fa-solid fa-plus"></i>
                        </button>

                    </div>

                </div>


                {{-- VARIANTS --}}
                @if($product->variants->isNotEmpty())

                    <div class="pd-prop-group">

                        <span class="pd-prop-title">Available Options</span>

                        <div class="pd-prop-options">

                            @foreach($product->variants as $variant)

                                <label class="pd-prop-option">

                                    <input
                                        type="radio"
                                        name="product_variant"
                                        value="{{ $variant->id }}"
                                        {{ $loop->first ? 'checked' : '' }}
                                    >

                                    <span>
                                        {{ $variant->name }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                    </div>

                @endif


                {{-- STOCK --}}
                <p class="pl-stock {{ $product->stock > 0 ? 'is-in' : 'is-out' }}">

                    @if($product->stock > 0)

                        <i class="fa-solid fa-circle-check"></i>
                        In Stock

                    @else

                        <i class="fa-solid fa-circle-xmark"></i>
                        Out of Stock

                    @endif

                </p>


                {{-- WHOLESALE INFORMATION --}}
                @if($product->sale_type === 'market')

                    <div class="pd-offer">

                        Minimum Order:
                        {{ $product->min_order_quantity }}

                        @if($product->max_order_quantity)
                            – {{ $product->max_order_quantity }}
                        @endif

                        {{ $product->unit?->name }}

                    </div>

                @endif


                {{-- ACTIONS --}}
                <div class="pd-actions">

                    <button
                        type="button"
                        class="pd-btn pd-btn-cart"
                        data-cart="{{ $product->id }}"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                    >
                        <i class="fa-solid fa-cart-shopping"></i>
                        Add to Cart
                    </button>

                    <button
                        type="button"
                        class="pd-btn pd-btn-buy"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                    >
                        <i class="fa-solid fa-bolt"></i>
                        Buy Now
                    </button>

                </div>


                {{-- PRODUCT DETAILS --}}
                <section class="pd-specs-card">

                    <header class="pd-section-head">
                        <h2>Product Details</h2>
                    </header>

                    <div class="pd-spec-grid">

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Product</div>
                            <div class="pd-spec-value">
                                {{ $product->name }}
                            </div>
                        </div>

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Category</div>
                            <div class="pd-spec-value">
                                {{ $product->category?->name }}
                            </div>
                        </div>

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Brand</div>
                            <div class="pd-spec-value">
                                {{ $product->brand?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Unit</div>
                            <div class="pd-spec-value">
                                {{ $product->unit?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Selling Type</div>
                            <div class="pd-spec-value">
                                {{ ucfirst($product->sale_type) }}
                            </div>
                        </div>

                        <div class="pd-spec-row">
                            <div class="pd-spec-key">Seller</div>
                            <div class="pd-spec-value">
                                {{ $product->seller?->name ?? 'N/A' }}
                            </div>
                        </div>

                    </div>

                </section>

            </div>

        </section>

    </article>

</main>

@endsection

@push('scripts')



<script>
document.addEventListener('DOMContentLoaded', function () {

    // Get prepared variant data from Blade
    const variants = @json($variantData);

    const variantInput = document.getElementById('selectedVariantId');

    function updateVariant() {

        const selectedOptions = {};

        document.querySelectorAll(
            '.pd-variant-selectors input[type="radio"]:checked'
        ).forEach(input => {
            selectedOptions[input.dataset.attributeId] = Number(input.value);
        });

        if (!Object.keys(selectedOptions).length) return;

        const matchingVariant = variants.find(variant => {

            return Object.entries(selectedOptions).every(
                ([attributeId, optionId]) =>
                    Number(variant.options[attributeId]) === optionId
            );

        });

        if (!matchingVariant) {
            if (variantInput) variantInput.value = '';
            return;
        }

        // Selected variant ID
        if (variantInput) {
            variantInput.value = matchingVariant.id;
        }

        // Update price
        const priceElement = document.querySelector('[data-product-price]');

        if (priceElement && matchingVariant.price !== null) {
            priceElement.textContent =
                Number(matchingVariant.price).toLocaleString('en-IN', {
                    style: 'currency',
                    currency: 'INR'
                });
        }

        // Update stock
        const stockElement = document.querySelector('[data-product-stock]');

        if (stockElement) {

            const inStock = Number(matchingVariant.stock) > 0;

            stockElement.innerHTML = inStock
                ? '<i class="fa-solid fa-circle-check"></i> In Stock'
                : '<i class="fa-solid fa-circle-xmark"></i> Out of Stock';

            stockElement.classList.toggle('is-in', inStock);
            stockElement.classList.toggle('is-out', !inStock);
        }

        // Update variant image
        if (matchingVariant.images && matchingVariant.images.length) {

            const mainImage = document.querySelector('.pd-main-image');

            if (mainImage) {
                mainImage.src = matchingVariant.images[0];
            }
        }
    }

    // Listen for attribute selections
    document.querySelectorAll(
        '.pd-variant-selectors input[type="radio"]'
    ).forEach(input => {
        input.addEventListener('change', updateVariant);
    });

    updateVariant();

});
</script>

<script src="{{ asset('js/cchub/ch-product-details.js') }}"></script>


<script src="{{ asset('js/cchub/ch-product-details.js') }}"></script>

@endpush