<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceTier;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit', 'defaultVariant', 'images', 'seller'])->latest();

        // Filter by sale_type
        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'wholesale') {
                $query->whereIn('sale_type', ['wholesale', 'both']);
            } elseif ($request->type === 'retail') {
                $query->whereIn('sale_type', ['retail', 'both']);
            } else {
                $query->where('sale_type', $request->type);
            }
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('slug', 'like', "%{$term}%")
                  ->orWhere('model_no', 'like', "%{$term}%")
                  ->orWhereHas('variants', fn($v) => $v->where('sku', 'like', "%{$term}%"));
            });
        }

        $products = $query->paginate(15)->withQueryString();

        // Counts for filter pills
        $counts = [
            'all' => Product::count(),
            'wholesale' => Product::trading()->count(),
            'retail' => Product::retail()->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'pending' => Product::where('status', 'pending')->count(),
        ];

        $categories = Category::with('parent')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'counts', 'categories'));
    }

    public function create()
    {
        $categories = Category::with('parent')->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $sellers = User::whereHas('role', fn ($q) => $q->whereIn('name', ['member', 'admin']))->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'units', 'sellers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:300',
            'slug'              => 'nullable|string|max:300|unique:products,slug',
            'user_id'           => 'nullable|exists:users,id',
            'category_id'       => 'nullable|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'unit_id'           => 'required|exists:units,id',
            'sale_type'         => 'required|in:retail,wholesale,both',
            'model_no'          => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'offer_text'        => 'nullable|string|max:150',
            'is_featured'       => 'nullable|boolean',
            'is_assured'        => 'nullable|boolean',
            'status'            => 'required|in:active,pending,draft,rejected',
            'price'             => 'required|numeric|min:0',
            'mrp'               => 'nullable|numeric|min:0',
            'stock'             => 'nullable|numeric|min:0',
            'min_order_qty'     => 'nullable|numeric|min:1',
            'max_order_qty'     => 'nullable|numeric|min:1',
            'pack_qty'          => 'nullable|numeric|min:1',
            'image'             => 'nullable|image|max:5120',
            'image_url'         => 'nullable|url|max:500',
            'tiers'             => 'nullable|array',
            'tiers.*.id'        => 'nullable|integer',
            'tiers.*.min_qty'   => 'nullable|numeric|min:1',
            'tiers.*.max_qty'   => 'nullable|numeric|min:1',
            'tiers.*.price'     => 'nullable|numeric|min:0',
            'variants'          => 'nullable|array',
            'variants.*.sku'    => 'nullable|string|max:100',
            'variants.*.price'  => 'nullable|numeric|min:0',
            'variants.*.mrp'    => 'nullable|numeric|min:0',
            'variants.*.stock'  => 'nullable|numeric|min:0',
            'variant_images'    => 'nullable|array',
            'variant_images.*'  => 'nullable|image|max:5120',
        ]);

        $admin = auth('admin')->user() ?: User::first();
        $userId = !empty($validated['user_id']) ? (int) $validated['user_id'] : ($admin ? $admin->id : 1);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(5));
        }

        $product = Product::create([
            'user_id'            => $userId,
            'category_id'        => $validated['category_id'] ?? null,
            'brand_id'           => $validated['brand_id'] ?? null,
            'unit_id'            => $validated['unit_id'],
            'title'              => $validated['title'],
            'slug'               => $slug,
            'model_no'           => $validated['model_no'] ?? null,
            'short_description'  => $validated['short_description'] ?? null,
            'description'        => $validated['description'] ?? null,
            'sale_type'          => $validated['sale_type'],
            'offer_text'         => $validated['offer_text'] ?? null,
            'min_order_qty'      => !empty($validated['min_order_qty']) ? $validated['min_order_qty'] : 1,
            'max_order_qty'      => $validated['max_order_qty'] ?? null,
            'pack_qty'           => !empty($validated['pack_qty']) ? $validated['pack_qty'] : 1,
            'is_featured'        => $request->boolean('is_featured'),
            'is_assured'         => $request->boolean('is_assured'),
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'active' ? now() : null,
            'rating_avg'         => 4.5,
            'rating_count'       => 1,
        ]);

        // Default variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-' . strtoupper(Str::slug($product->title)) . '-' . $product->id,
            'price'      => $validated['price'],
            'mrp'        => !empty($validated['mrp']) ? $validated['mrp'] : $validated['price'],
            'stock'      => $validated['stock'] ?? 100,
            'is_default' => true,
            'is_active'  => true,
        ]);

        // Sync wholesale price tiers
        if (!empty($validated['tiers'])) {
            $this->syncWholesaleTiers($product, $variant, $validated['tiers']);
        }

        // Product image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dir = public_path('uploads/products');
            File::ensureDirectoryExists($dir);
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $imagePath = 'uploads/products/' . $filename;
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        if ($imagePath) {
            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $imagePath,
                'sort_order' => 1,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load([
            'seller.role',
            'category.parent',
            'brand',
            'unit',
            'defaultVariant',
            'images.option',
            'priceTiers' => fn($q) => $q->orderBy('min_qty'),
            'variants.attributeValues.attribute',
            'variants.attributeValues.option',
        ]);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load([
            'seller.role',
            'category.parent',
            'brand',
            'unit',
            'defaultVariant',
            'images.option',
            'priceTiers' => fn($q) => $q->orderBy('min_qty'),
            'variants.attributeValues.attribute',
            'variants.attributeValues.option',
        ]);

        $categories = Category::with('parent')->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $sellers = User::whereHas('role', fn($q) => $q->whereIn('name', ['member', 'admin']))->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'units', 'sellers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'user_id'           => 'nullable|exists:users,id',
            'title'             => 'required|string|max:300',
            'slug'              => 'nullable|string|max:300|unique:products,slug,' . $product->id,
            'category_id'       => 'nullable|exists:categories,id',
            'brand_id'          => 'nullable|exists:brands,id',
            'unit_id'           => 'required|exists:units,id',
            'sale_type'         => 'required|in:retail,wholesale,both',
            'model_no'          => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'offer_text'        => 'nullable|string|max:150',
            'is_featured'       => 'nullable|boolean',
            'is_assured'        => 'nullable|boolean',
            'status'            => 'required|in:active,pending,draft,rejected',
            'price'             => 'required|numeric|min:0',
            'mrp'               => 'nullable|numeric|min:0',
            'stock'             => 'nullable|numeric|min:0',
            'min_order_qty'     => 'nullable|numeric|min:1',
            'max_order_qty'     => 'nullable|numeric|min:1',
            'pack_qty'          => 'nullable|numeric|min:1',
            'image'             => 'nullable|image|max:5120',
            'image_url'         => 'nullable|url|max:500',
            'tiers'             => 'nullable|array',
            'tiers.*.id'        => 'nullable|integer',
            'tiers.*.min_qty'   => 'nullable|numeric|min:1',
            'tiers.*.max_qty'   => 'nullable|numeric|min:1',
            'tiers.*.price'     => 'nullable|numeric|min:0',
            'variants'          => 'nullable|array',
            'variants.*.sku'    => 'nullable|string|max:100',
            'variants.*.price'  => 'nullable|numeric|min:0',
            'variants.*.mrp'    => 'nullable|numeric|min:0',
            'variants.*.stock'  => 'nullable|numeric|min:0',
            'variant_images'    => 'nullable|array',
            'variant_images.*'  => 'nullable|image|max:5120',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : $product->slug;

        $updateData = [
            'category_id'        => $validated['category_id'] ?? null,
            'brand_id'           => $validated['brand_id'] ?? null,
            'unit_id'            => $validated['unit_id'],
            'title'              => $validated['title'],
            'slug'               => $slug,
            'model_no'           => $validated['model_no'] ?? null,
            'short_description'  => $validated['short_description'] ?? null,
            'description'        => $validated['description'] ?? null,
            'sale_type'          => $validated['sale_type'],
            'offer_text'         => $validated['offer_text'] ?? null,
            'min_order_qty'      => !empty($validated['min_order_qty']) ? $validated['min_order_qty'] : 1,
            'max_order_qty'      => $validated['max_order_qty'] ?? null,
            'pack_qty'           => !empty($validated['pack_qty']) ? $validated['pack_qty'] : 1,
            'is_featured'        => $request->boolean('is_featured'),
            'is_assured'         => $request->boolean('is_assured'),
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'active' ? ($product->published_at ?: now()) : null,
        ];
        if (!empty($validated['user_id'])) {
            $updateData['user_id'] = (int) $validated['user_id'];
        }
        $product->update($updateData);

        // Update default variant
        $variant = $product->defaultVariant ?: ProductVariant::firstOrNew(['product_id' => $product->id, 'is_default' => true]);
        $variant->sku = $variant->sku ?: 'SKU-' . strtoupper(Str::slug($product->title)) . '-' . $product->id;
        $variant->price = $validated['price'];
        $variant->mrp = !empty($validated['mrp']) ? $validated['mrp'] : $validated['price'];
        $variant->stock = $validated['stock'] ?? 100;
        $variant->is_default = true;
        $variant->is_active = true;
        $variant->save();

        // Sync wholesale price tiers
        $this->syncWholesaleTiers($product, $variant, $request->input('tiers', []));

        // Image update if new one provided
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $dir = public_path('uploads/products');
            File::ensureDirectoryExists($dir);
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $imagePath = 'uploads/products/' . $filename;

            // replace primary image
            $firstImg = $product->images->first();
            if ($firstImg) {
                if ($firstImg->path && str_starts_with($firstImg->path, 'uploads/')) {
                    File::delete(public_path($firstImg->path));
                }
                $firstImg->update(['path' => $imagePath]);
            } else {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $imagePath,
                    'sort_order' => 1,
                ]);
            }
        } elseif ($request->filled('image_url')) {
            $firstImg = $product->images->first();
            if ($firstImg) {
                $firstImg->update(['path' => $request->image_url]);
            } else {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $request->image_url,
                    'sort_order' => 1,
                ]);
            }
        }

        // Handle variant updates (price, mrp, stock, sku)
        if ($request->has('variants') && is_array($request->input('variants'))) {
            foreach ($request->input('variants') as $vId => $vData) {
                $vRecord = ProductVariant::where('id', $vId)->where('product_id', $product->id)->first();
                if ($vRecord) {
                    if (isset($vData['sku']) && $vData['sku'] !== '') {
                        $vRecord->sku = $vData['sku'];
                    }
                    if (isset($vData['price']) && $vData['price'] !== '') {
                        $vRecord->price = $vData['price'];
                    }
                    if (isset($vData['mrp']) && $vData['mrp'] !== '') {
                        $vRecord->mrp = $vData['mrp'];
                    }
                    if (isset($vData['stock']) && $vData['stock'] !== '') {
                        $vRecord->stock = $vData['stock'];
                    }
                    $vRecord->save();
                }
            }
        }

        // Handle variant-specific image uploads without affecting other variants
        if ($request->hasFile('variant_images')) {
            $dir = public_path('uploads/products');
            File::ensureDirectoryExists($dir);

            foreach ($request->file('variant_images') as $vId => $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $var = ProductVariant::with(['attributeValues.attribute'])->where('id', $vId)->where('product_id', $product->id)->first();
                if (!$var) {
                    continue;
                }

                $filename = time() . '_var_' . $vId . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($dir, $filename);
                $vImgPath = 'uploads/products/' . $filename;

                // Identify color/axis option if exists
                $colorOptionId = null;
                foreach ($var->attributeValues as $av) {
                    if (strtolower($av->attribute?->name ?? '') === 'color') {
                        $colorOptionId = $av->attribute_option_id;
                        break;
                    }
                }
                if (!$colorOptionId && $var->attributeValues->isNotEmpty()) {
                    $colorOptionId = $var->attributeValues->first()->attribute_option_id;
                }

                // Find existing image specifically for this variant
                $existingImg = ProductImage::where('product_id', $product->id)
                    ->where('variant_id', $vId)
                    ->first();

                if ($existingImg) {
                    if ($existingImg->path && str_starts_with($existingImg->path, 'uploads/')) {
                        File::delete(public_path($existingImg->path));
                    }
                    $existingImg->update([
                        'path'                => $vImgPath,
                        'attribute_option_id' => $colorOptionId ?: $existingImg->attribute_option_id,
                    ]);
                } else {
                    ProductImage::create([
                        'product_id'          => $product->id,
                        'variant_id'          => $vId,
                        'attribute_option_id' => $colorOptionId,
                        'path'                => $vImgPath,
                        'sort_order'          => 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    protected function syncWholesaleTiers(Product $product, ?ProductVariant $variant, array $tiersData): void
    {
        $variantId = $variant?->id ?: $product->defaultVariant?->id;

        // 1. Gather all submitted existing tier IDs
        $submittedIds = collect($tiersData)
            ->pluck('id')
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (int) $id)
            ->all();

        // 2. Safely delete removed wholesale tiers before updating/inserting
        // This avoids unique key collisions on (product_id, variant_id, price_type, min_qty)
        $deleteQuery = ProductPriceTier::where('product_id', $product->id)
            ->where('price_type', 'wholesale');
        if (!empty($submittedIds)) {
            $deleteQuery->whereNotIn('id', $submittedIds);
        }
        $deleteQuery->delete();

        // 3. Now update existing or create new tiers
        foreach ($tiersData as $row) {
            $minQty = isset($row['min_qty']) && $row['min_qty'] !== '' ? (float) $row['min_qty'] : null;
            $price  = isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null;
            $maxQty = isset($row['max_qty']) && $row['max_qty'] !== '' ? (float) $row['max_qty'] : null;

            // Skip incomplete or empty rows
            if ($minQty === null || $price === null) {
                continue;
            }

            // Existing tier
            if (!empty($row['id'])) {
                $tier = ProductPriceTier::where('id', $row['id'])
                    ->where('product_id', $product->id)
                    ->first();
                if ($tier) {
                    $tier->update([
                        'variant_id' => $variantId,
                        'price_type' => 'wholesale',
                        'min_qty'    => $minQty,
                        'max_qty'    => $maxQty,
                        'price'      => $price,
                    ]);
                    continue;
                }
            }

            // New tier (or update if duplicate min_qty submitted)
            ProductPriceTier::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'price_type' => 'wholesale',
                    'min_qty'    => $minQty,
                ],
                [
                    'max_qty'    => $maxQty,
                    'price'      => $price,
                ]
            );
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return back()->with('success', 'Product featured status updated.');
    }

    public function toggleAssured(Product $product)
    {
        $product->update(['is_assured' => !$product->is_assured]);
        return back()->with('success', 'Product assured status updated.');
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate(['status' => 'required|in:active,pending,rejected,draft']);
        $product->update([
            'status' => $request->status,
            'published_at' => $request->status === 'active' ? ($product->published_at ?: now()) : null,
        ]);
        return back()->with('success', 'Product status changed to ' . ucfirst($request->status));
    }
}
