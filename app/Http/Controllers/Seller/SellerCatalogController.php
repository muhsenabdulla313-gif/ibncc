<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SellerProductService;
use Illuminate\Http\Request;

class SellerCatalogController extends Controller
{
    /** Children of a category (or root categories when parent_id is empty). Feeds the 3 cascading category boxes. */
    public function children(Request $request)
    {
        $q = Category::active()->withCount('children')->orderBy('sort_order')->orderBy('name');
        $request->filled('parent_id') ? $q->where('parent_id', (int) $request->parent_id) : $q->whereNull('parent_id');

        return $q->get(['id', 'name', 'parent_id'])->map(fn ($c) => [
            'id' => $c->id, 'name' => $c->name, 'has_children' => $c->children_count > 0,
        ]);
    }

    /** Attribute definitions (details fields + variant axes) for a category. */
    public function attributes(Request $request, Category $category, SellerProductService $svc)
    {
        return $svc->attributesFor($category, $request->query('sale_type'))->values();
    }
}
