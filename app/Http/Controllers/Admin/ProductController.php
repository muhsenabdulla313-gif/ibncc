<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
        $query = Product::with(['category', 'brand', 'unit', 'defaultVariant', 'images'])->latest();

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

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'counts', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:300',
            'slug'              => 'nullable|string|max:300|unique:products,slug',
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
            'image'             => 'nullable|image|max:5120',
            'image_url'         => 'nullable|url|max:500',
        ]);

        $admin = auth('admin')->user() ?: User::first();
        $userId = $admin ? $admin->id : 1;

        $slug = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']);
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
            'is_featured'        => $request->boolean('is_featured'),
            'is_assured'         => $request->boolean('is_assured'),
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'active' ? now() : null,
            'rating_avg'         => 4.5,
            'rating_count'       => 1,
        ]);

        // Default variant
        ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => 'SKU-' . strtoupper(Str::slug($product->title)) . '-' . $product->id,
            'price'      => $validated['price'],
            'mrp'        => $validated['mrp'] ?: $validated['price'],
            'stock'      => $validated['stock'] ?? 100,
            'is_default' => true,
            'is_active'  => true,
        ]);

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
        // Admin product show just redirects to edit
        return redirect()->route('admin.products.edit', $product->slug);
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'defaultVariant', 'images']);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
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
            'image'             => 'nullable|image|max:5120',
            'image_url'         => 'nullable|url|max:500',
        ]);

        $slug = $validated['slug'] ? Str::slug($validated['slug']) : $product->slug;

        $product->update([
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
            'is_featured'        => $request->boolean('is_featured'),
            'is_assured'         => $request->boolean('is_assured'),
            'status'             => $validated['status'],
            'published_at'       => $validated['status'] === 'active' ? ($product->published_at ?: now()) : null,
        ]);

        // Update default variant
        $variant = $product->defaultVariant ?: ProductVariant::firstOrNew(['product_id' => $product->id, 'is_default' => true]);
        $variant->sku = $variant->sku ?: 'SKU-' . strtoupper(Str::slug($product->title)) . '-' . $product->id;
        $variant->price = $validated['price'];
        $variant->mrp = $validated['mrp'] ?: $validated['price'];
        $variant->stock = $validated['stock'] ?? 100;
        $variant->is_default = true;
        $variant->is_active = true;
        $variant->save();

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

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
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
