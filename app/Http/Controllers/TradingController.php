<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Product;

class TradingController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->with('children');
            }])
            ->orderBy('sort_order')
            ->get();

        $tradingCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $marketplaceProducts = Product::published()
            ->retail()
            ->with(['images', 'defaultVariant'])
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        $bestsellers = Product::published()
            ->with(['images', 'defaultVariant'])
            ->orderByDesc('rating_count')
            ->limit(12)
            ->get();

        return view('trading', compact('categories', 'banners', 'tradingCategories', 'marketplaceProducts', 'bestsellers'));
    }
}
