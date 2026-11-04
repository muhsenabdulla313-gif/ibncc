<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SellerHubController extends Controller
{
    public function index()
    {
        $sellerId = auth()->id();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $units = class_exists(Unit::class)
            ? Unit::query()->orderBy('name')->get()
            : collect();

        $products = Product::query()
            ->where('user_id', $sellerId)
            ->with(['category', 'unit'])
            ->latest()
            ->paginate(12);

        return view('seller.hub', compact('categories', 'units', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            $data['user_id'] = auth()->id();
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['is_active'] = $request->boolean('is_active', true);

            $product = Product::create($data);
            $this->storeImages($request, $product);
        });

        return back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeSellerProduct($product);
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $product, $data) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            $data['is_active'] = $request->boolean('is_active');
            $product->update($data);
            $this->storeImages($request, $product);
        });

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorizeSellerProduct($product);

        DB::transaction(function () use ($product) {
            // Remove image files only when the existing Product model exposes
            // an `images` relation with a `path` column.
            if (method_exists($product, 'images')) {
                foreach ($product->images as $image) {
                    if (!empty($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }
            }
            $product->delete();
        });

        return back()->with('success', 'Product deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'brand_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'product_type' => ['nullable', 'string', 'max:50'],
            'sale_type' => ['required', Rule::in(['retail', 'wholesale', 'both'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'max_order_quantity' => ['nullable', 'integer', 'gte:min_order_quantity'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images') || !method_exists($product, 'images')) {
            return;
        }

        foreach ($request->file('images') as $file) {
            $path = $file->store('products/' . $product->id, 'public');

            // Expected relation: Product::images() -> hasMany(ProductImage::class)
            // Expected fields: path, alt_text, sort_order
            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name,
                'sort_order' => $product->images()->count(),
            ]);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $counter = 1;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function authorizeSellerProduct(Product $product): void
    {
        abort_unless((int) $product->user_id === (int) auth()->id(), 403);
    }
}
