<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index()
    {
        $rawCart = $this->getCartItems();
        $processed = $this->buildCartData($rawCart);

        return view('cart.index', [
            'items'     => $processed['items'],
            'subtotal'  => $processed['subtotal'],
            'discount'  => $processed['discount'],
            'total'     => $processed['total'],
            'cartCount' => $processed['count'],
        ]);
    }

    /**
     * Add an item to the shopping cart.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'qty'        => 'required|numeric|min:0.001',
            'mode'       => 'nullable|string|in:retail,wholesale',
        ]);

        $product = Product::with(['variants', 'priceTiers', 'unit'])
            ->where('status', 'active')
            ->findOrFail($request->product_id);

        // Find or fallback to default variant
        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = $product->variants->firstWhere('id', (int) $request->variant_id);
        }
        if (!$variant) {
            $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
        }

        if (!$variant || !$variant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'The selected product option is currently unavailable.',
            ], 422);
        }

        $qty  = (float) $request->qty;
        $mode = $request->input('mode') ?: ($product->sale_type === 'wholesale' ? 'wholesale' : 'retail');

        // Wholesale MOQ & Max order validation
        if ($mode === 'wholesale') {
            $minOrderQty = max(1, (float) ($product->min_order_qty ?? 1));
            $unitName    = $product->unit?->name ?? 'units';

            if ($qty < $minOrderQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order quantity for this wholesale item is {$minOrderQty} {$unitName}.",
                ], 422);
            }

            if ($product->max_order_qty && $qty > (float) $product->max_order_qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum order quantity for this wholesale item is {$product->max_order_qty} {$unitName}.",
                ], 422);
            }
        } else {
            if ($qty < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity must be at least 1.',
                ], 422);
            }
        }

        // Cart storage: Session + Database
        $cart = session()->get('cart', []);
        $variantId = (int) $variant->id;

        $existingQty = isset($cart[$variantId]) ? (float) $cart[$variantId]['qty'] : 0;
        $newQty      = $existingQty + $qty;

        // Stock check against total requested quantity
        if ($product->track_stock) {
            $avail = $variant->available_stock;
            if ($newQty > $avail) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot add {$qty} more. Only {$avail} item(s) available in stock.",
                ], 422);
            }
        }

        $cart[$variantId] = [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'qty'        => $newQty,
            'mode'       => $mode,
        ];

        session()->put('cart', $cart);

        // Sync with database if logged in
        if (Auth::check()) {
            CartItem::updateOrCreate(
                ['user_id' => Auth::id(), 'variant_id' => $variantId],
                ['product_id' => $product->id, 'qty' => $newQty]
            );
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Added to cart successfully!',
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|integer',
            'qty'        => 'required|numeric|min:0.001',
        ]);

        $variantId = (int) $request->variant_id;
        $qty       = (float) $request->qty;

        $cart = session()->get('cart', []);
        if (!isset($cart[$variantId])) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.',
            ], 404);
        }

        $variant = ProductVariant::with(['product.unit', 'product.priceTiers'])->findOrFail($variantId);
        $product = $variant->product;
        $mode    = $cart[$variantId]['mode'] ?? 'retail';

        // Validate MOQ for wholesale
        if ($mode === 'wholesale') {
            $minOrderQty = max(1, (float) ($product->min_order_qty ?? 1));
            $unitName    = $product->unit?->name ?? 'units';

            if ($qty < $minOrderQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order quantity for this item is {$minOrderQty} {$unitName}.",
                ], 422);
            }

            if ($product->max_order_qty && $qty > (float) $product->max_order_qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum order quantity for this item is {$product->max_order_qty} {$unitName}.",
                ], 422);
            }
        }

        // Validate stock
        if ($product->track_stock && $qty > $variant->available_stock) {
            return response()->json([
                'success' => false,
                'message' => "Only {$variant->available_stock} item(s) available in stock.",
            ], 422);
        }

        // Update in session
        $cart[$variantId]['qty'] = $qty;
        session()->put('cart', $cart);

        // Update in DB if logged in
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())
                ->where('variant_id', $variantId)
                ->update(['qty' => $qty]);
        }

        // Recalculate cart totals
        $processed = $this->buildCartData($cart);
        $itemData  = collect($processed['items'])->firstWhere('variant_id', $variantId);

        return response()->json([
            'success'              => true,
            'unit_price'           => $itemData['unit_price'] ?? 0,
            'unit_price_formatted' => '₹' . number_format($itemData['unit_price'] ?? 0, 2),
            'line_total'           => $itemData['line_total'] ?? 0,
            'line_total_formatted' => '₹' . number_format($itemData['line_total'] ?? 0, 2),
            'subtotal'             => $processed['subtotal'],
            'subtotal_formatted'   => '₹' . number_format($processed['subtotal'], 2),
            'discount'             => $processed['discount'],
            'discount_formatted'   => '- ₹' . number_format($processed['discount'], 2),
            'total'                => $processed['total'],
            'total_formatted'      => '₹' . number_format($processed['total'], 2),
            'cart_count'           => $processed['count'],
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|integer',
        ]);

        $variantId = (int) $request->variant_id;
        $cart = session()->get('cart', []);

        if (isset($cart[$variantId])) {
            unset($cart[$variantId]);
            session()->put('cart', $cart);
        }

        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())
                ->where('variant_id', $variantId)
                ->delete();
        }

        $processed = $this->buildCartData($cart);

        return response()->json([
            'success'            => true,
            'message'            => 'Item removed from cart.',
            'subtotal'           => $processed['subtotal'],
            'subtotal_formatted' => '₹' . number_format($processed['subtotal'], 2),
            'discount'           => $processed['discount'],
            'discount_formatted' => '- ₹' . number_format($processed['discount'], 2),
            'total'              => $processed['total'],
            'total_formatted'    => '₹' . number_format($processed['total'], 2),
            'cart_count'         => $processed['count'],
            'is_empty'           => count($processed['items']) === 0,
        ]);
    }

    /**
     * Get active cart count for badges.
     */
    public function count(): JsonResponse
    {
        $cart = $this->getCartItems();
        return response()->json([
            'count' => count($cart),
        ]);
    }

    /**
     * Direct Buy Now without modifying regular cart.
     */
    public function buyNow(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'qty'        => 'required|numeric|min:0.001',
            'mode'       => 'nullable|string|in:retail,wholesale',
        ]);

        $product = Product::with(['variants', 'priceTiers', 'unit'])
            ->where('status', 'active')
            ->findOrFail($request->product_id);

        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = $product->variants->firstWhere('id', (int) $request->variant_id);
        }
        if (!$variant) {
            $variant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
        }

        if (!$variant || !$variant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'The selected product option is currently unavailable.',
            ], 422);
        }

        $qty  = (float) $request->qty;
        $mode = $request->input('mode') ?: ($product->sale_type === 'wholesale' ? 'wholesale' : 'retail');

        // Wholesale MOQ & Max order validation
        if ($mode === 'wholesale') {
            $minOrderQty = max(1, (float) ($product->min_order_qty ?? 1));
            $unitName    = $product->unit?->name ?? 'units';

            if ($qty < $minOrderQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum order quantity for this wholesale item is {$minOrderQty} {$unitName}.",
                ], 422);
            }

            if ($product->max_order_qty && $qty > (float) $product->max_order_qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum order quantity for this wholesale item is {$product->max_order_qty} {$unitName}.",
                ], 422);
            }
        } else {
            if ($qty < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity must be at least 1.',
                ], 422);
            }
        }

        // Stock check
        if ($product->track_stock && $qty > $variant->available_stock) {
            return response()->json([
                'success' => false,
                'message' => "Only {$variant->available_stock} item(s) available in stock.",
            ], 422);
        }

        // Store isolated Buy Now item in session — does NOT disturb cart!
        session()->put('buy_now_item', [
            'product_id' => $product->id,
            'variant_id' => (int) $variant->id,
            'qty'        => $qty,
            'mode'       => $mode,
        ]);

        return response()->json([
            'success'  => true,
            'redirect' => route('checkout.index', ['type' => 'buy_now']),
        ]);
    }

    /**
     * Retrieve cart items combining session and database.
     */
    private function getCartItems(): array
    {
        $cart = session()->get('cart', []);

        if (Auth::check()) {
            $dbItems = CartItem::where('user_id', Auth::id())->get();
            foreach ($dbItems as $dbItem) {
                if (!isset($cart[$dbItem->variant_id])) {
                    $cart[$dbItem->variant_id] = [
                        'product_id' => $dbItem->product_id,
                        'variant_id' => $dbItem->variant_id,
                        'qty'        => (float) $dbItem->qty,
                        'mode'       => 'retail',
                    ];
                }
            }
            session()->put('cart', $cart);
        }

        return $cart;
    }

    /**
     * Build rich, validated cart data with server-side price calculations.
     */
    public function buildCartData(array $rawCart): array
    {
        $items    = [];
        $subtotal = 0.0;
        $discount = 0.0;

        foreach ($rawCart as $entry) {
            $variantId = (int) ($entry['variant_id'] ?? 0);
            $qty       = (float) ($entry['qty'] ?? 1);
            $mode      = $entry['mode'] ?? 'retail';

            $variant = ProductVariant::with([
                'product.brand',
                'product.unit',
                'product.images',
                'product.priceTiers',
                'attributeValues.attribute',
                'attributeValues.option',
                'images',
            ])->find($variantId);

            if (!$variant || !$variant->product || $variant->product->status !== 'active') {
                continue;
            }

            $product = $variant->product;

            // Recalculate price server-side
            $pricing = $this->calculateItemPrice($product, $variant, $qty, $mode);

            // Format variant specifications (e.g. Color: Blue, Size: XL)
            $specParts = [];
            foreach ($variant->attributeValues as $av) {
                $attrName = $av->attribute?->name ?? 'Option';
                $optVal   = $av->option?->value ?? '';
                if ($optVal) {
                    $specParts[] = "{$attrName}: {$optVal}";
                }
            }
            $variantSpecs = implode(' | ', $specParts);

            // Resolve product image
            $imgPath  = $variant->images->first()?->path ?? $product->images->first()?->path;
            $imageUrl = $this->resolveImageUrl($imgPath);

            $minQty = ($mode === 'wholesale') ? max(1, (int) ($product->min_order_qty ?? 1)) : 1;
            $maxQty = $product->max_order_qty ? (int) $product->max_order_qty : (int) $variant->available_stock;

            $items[] = [
                'product_id'    => $product->id,
                'variant_id'    => $variant->id,
                'title'         => $product->title ?? $product->name,
                'slug'          => $product->slug,
                'sku'           => $variant->sku,
                'brand'         => $product->brand?->name,
                'unit_name'     => $product->unit?->name ?? 'units',
                'variant_specs' => $variantSpecs,
                'image_url'     => $imageUrl,
                'mode'          => $mode,
                'qty'           => $qty,
                'min_qty'       => $minQty,
                'max_qty'       => $maxQty,
                'unit_price'    => $pricing['unit_price'],
                'mrp'           => $pricing['mrp'],
                'line_total'    => $pricing['line_total'],
                'discount'      => $pricing['discount'],
                'url'           => ($mode === 'wholesale')
                    ? route('trading.products.show', $product->slug)
                    : route('products.show', $product->slug),
            ];

            $subtotal += $pricing['line_total'];
            $discount += $pricing['discount'];
        }

        $total = max(0, $subtotal);

        return [
            'items'    => $items,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($total, 2),
            'count'    => count($items),
        ];
    }

    /**
     * Pure server-side pricing engine for retail and tiered wholesale products.
     */
    public function calculateItemPrice(Product $product, ProductVariant $variant, float $qty, string $mode): array
    {
        $unitPrice   = (float) ($variant->price ?: ($product->base_price ?: 0));
        $mrp         = (float) ($variant->mrp ?: 0);
        $tierApplied = false;

        if ($mode === 'wholesale') {
            // Check for matching volume price tier
            $tier = ProductPriceTier::where('product_id', $product->id)
                ->where('price_type', 'wholesale')
                ->where(function ($q) use ($variant) {
                    $q->where('variant_id', $variant->id)->orWhereNull('variant_id');
                })
                ->where('min_qty', '<=', $qty)
                ->where(function ($q) use ($qty) {
                    $q->whereNull('max_qty')->orWhere('max_qty', '>=', $qty);
                })
                ->orderByDesc('variant_id') // variant-specific tiers take precedence
                ->orderByDesc('min_qty')
                ->first();

            if ($tier) {
                $unitPrice   = (float) $tier->price;
                $tierApplied = true;
            }
        }

        $lineTotal = round($unitPrice * $qty, 2);
        $discount  = ($mrp > $unitPrice) ? round(($mrp - $unitPrice) * $qty, 2) : 0;

        return [
            'unit_price'   => $unitPrice,
            'line_total'   => $lineTotal,
            'mrp'          => $mrp,
            'discount'     => $discount,
            'tier_applied' => $tierApplied,
        ];
    }

    /**
     * Image resolution helper with safety fallback.
     */
    public function resolveImageUrl(?string $path): string
    {
        $fallback = asset('assets/images/no-image.avif');
        if (!$path) {
            return $fallback;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        $clean = ltrim($path, '/\\');
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }

        return $fallback;
    }
}
