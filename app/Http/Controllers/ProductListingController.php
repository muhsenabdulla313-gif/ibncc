<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProductListingController extends Controller
{
      public function retail(Request $request)
    {
        
        return $this->listing($request, 'retail');
    }

    public function trading(Request $request)
    {
  
        return $this->listing($request, 'wholesale');
    }

    private function listing(Request $request, string $mode)
    {
        
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with('children.children')
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $this->selectedCategory($request);

        $categoryIds = $selectedCategory
            ? $this->descendantCategoryIds($selectedCategory)
            : [];

        $query = Product::query()
            ->published()
            ->with([
                'category',
                'brand',
                'unit',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true),
                'priceTiers' => fn ($q) => $q->orderBy('min_qty'),
            'attributes.group',
'attributes.options',
            ]);

        if ($mode === 'retail') {
            $query->retail();
        } else {
            $query->trading()
                ->whereHas('priceTiers', function ($q) {
                    $q->where('price_type', 'wholesale');
                });
        }

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('model_no', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('brand')) {
            $brands = (array) $request->brand;

            $query->whereHas('brand', function ($q) use ($brands) {
                $q->whereIn('slug', $brands)
                    ->orWhereIn('id', $brands);
            });
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->input('min_price');
            $max = $request->input('max_price');

            if ($mode === 'retail') {
                $query->whereHas('variants', function ($q) use ($min, $max) {
                    $q->where('is_active', true);

                    if ($min !== null && $min !== '') {
                        $q->where('price', '>=', $min);
                    }

                    if ($max !== null && $max !== '') {
                        $q->where('price', '<=', $max);
                    }
                });
            } else {
                $query->whereHas('priceTiers', function ($q) use ($min, $max) {
                    $q->where('price_type', 'wholesale');

                    if ($min !== null && $min !== '') {
                        $q->where('price', '>=', $min);
                    }

                    if ($max !== null && $max !== '') {
                        $q->where('price', '<=', $max);
                    }
                });
            }
        }

        if ($request->filled('availability')) {
            if ($request->availability === 'in_stock') {
                $query->whereHas('variants', function ($q) {
                    $q->where('is_active', true)
                        ->whereColumn('stock', '>', 'reserved_stock');
                });
            }

            if ($request->availability === 'out_of_stock') {
                $query->whereDoesntHave('variants', function ($q) {
                    $q->where('is_active', true)
                        ->whereColumn('stock', '>', 'reserved_stock');
                });
            }
        }

        if ($request->filled('rating')) {
            $query->where('rating_avg', '>=', $request->rating);
        }

        if ($request->filled('min_moq') && $mode === 'wholesale') {
            $query->where('min_order_qty', '>=', $request->min_moq);
        }

        if ($request->filled('max_moq') && $mode === 'wholesale') {
            $query->where('min_order_qty', '<=', $request->max_moq);
        }

        $attributeFilters = (array) $request->input('attribute', []);

        foreach ($attributeFilters as $attributeId => $values) {
            $values = array_filter((array) $values);

            if (!$values) {
                continue;
            }

            $query->whereHas('attributes', function ($q) use ($attributeId, $values) {
                $q->where('attribute_id', $attributeId)
                    ->whereIn('attribute_option_id', $values);
            });
        }

        if ($mode === 'retail') {
            $query->withMin(
                ['variants as listing_price' => fn ($q) => $q->where('is_active', true)],
                'price'
            );
        } else {
            $query->withMin(
                ['priceTiers as listing_price' => fn ($q) => $q->where('price_type', 'wholesale')],
                'price'
            );
        }

        switch ($request->input('sort', 'latest')) {
            case 'price_asc':
                $query->orderBy('listing_price');
                break;

            case 'price_desc':
                $query->orderByDesc('listing_price');
                break;

            case 'rating':
                $query->orderByDesc('rating_avg');
                break;

            case 'popular':
                $query->orderByDesc('rating_count');
                break;

            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        $filterCategoryIds = $selectedCategory
            ? $this->ancestorCategoryIds($selectedCategory)
            : [];

      $filtersQuery = Attribute::query()
    ->where('is_filterable', true)
    ->whereHas('categories', function ($q) use ($mode, $filterCategoryIds) {

        $q->where(function ($pivot) use ($mode) {
            $pivot->where('category_attributes.applies_to', $mode)
                ->orWhere('category_attributes.applies_to', 'both');
        });

        if ($filterCategoryIds) {
            $q->whereIn('categories.id', $filterCategoryIds);
        }
    })
    ->with([
        'options' => fn ($q) => $q->orderBy('sort_order'),
        'group',
    ])
    ->orderBy('sort_order');

$filters = $filtersQuery->get();

        $filters = $filtersQuery->get();

        $brands = DB::table('brands')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('catalog.listing', [
            'mode' => $mode,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'products' => $products,
            'filters' => $filters,
            'brands' => $brands,
        ]);
    }

    private function selectedCategory(Request $request): ?Category
    {
        foreach (['item', 'group', 'cat'] as $parameter) {
            $value = $request->input($parameter);

            if (!$value) {
                continue;
            }

            $category = Category::where('slug', $value)
                ->orWhere('id', $value)
                ->first();

            if ($category) {
                return $category;
            }
        }

        return null;
    }

    private function descendantCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        if ($category->path) {
            return Category::where('id', $category->id)
                ->orWhere('path', 'like', rtrim($category->path, '/') . '/%')
                ->pluck('id')
                ->all();
        }

        $children = Category::where('parent_id', $category->id)->pluck('id');

        foreach ($children as $childId) {
            $child = Category::find($childId);

            if ($child) {
                $ids = array_merge($ids, $this->descendantCategoryIds($child));
            }
        }

        return array_values(array_unique($ids));
    }

    private function ancestorCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        if ($category->path) {
            $pathIds = array_filter(explode('/', trim($category->path, '/')));

            return array_values(array_unique(array_merge(
                $ids,
                array_map('intval', $pathIds)
            )));
        }

        $parent = $category->parent;

        while ($parent) {
            $ids[] = $parent->id;
            $parent = $parent->parent;
        }

        return $ids;
    }
    public function showRetail(string $slug)
{

    $product = Product::published()
        ->retail()
        ->with([
            'category',
            'brand',
            'unit',
            'images',
            'variants',
            'priceTiers',
            'attributes.group',
'attributes.options',
            'videos',
            'documents',
        ])
        ->where('slug', $slug)
        ->firstOrFail();

    return view('catalog.details', compact('product'));
}

public function showTrading(string $slug)
{
    $product = Product::published()
        ->trading()
        ->with([
            'category',
            'brand',
            'unit',
            'images',
            'variants',
            'priceTiers',
'attributes.group',
            'attributes.option',
            'videos',
            'documents',
        ])
        ->where('slug', $slug)
        ->firstOrFail();

    return view('catalog.details', compact('product'));
}
}
