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

        // All root categories with two levels of children (for the mega-menu navbar)
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        // Trading-flagged root categories with children (for the navbar trading section)
        $tradingNavCategories = Category::trading()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        // For the page body gallery section — same trading categories (flat)
        $tradingCategories = $tradingNavCategories->count()
            ? $tradingNavCategories
            : Category::whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->take(6)
                ->get();

        $marketplaceProducts = Product::published()
            ->retail()
            ->with(['images', 'defaultVariant', 'category'])
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        if ($marketplaceProducts->count() < 6) {
            $marketplaceProducts = Product::published()
                ->retail()
                ->with(['images', 'defaultVariant', 'category'])
                ->orderByDesc('published_at')
                ->limit(12)
                ->get();
        }

        $bestsellers = Product::published()
            ->with(['images', 'defaultVariant', 'category'])
            ->orderByDesc('rating_count')
            ->limit(6)
            ->get();

        // Similar to your searches collections
        $similarSearches = [
            [
                'title' => 'Luggage & Travel',
                'subtitle' => 'Built for every journey',
                'lead_bg' => '#e8f0e4',
                'lead_color' => '#2f5d3a',
                'url' => route('products.index', ['cat' => 'men']),
                'products' => Product::published()
                    ->whereIn('slug', ['hard-shell-suitcase', 'travel-duffel-bag', 'cabin-luggage-set'])
                    ->with(['images', 'defaultVariant'])
                    ->get(),
                'fallbacks' => [
                    ['url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=500&q=80', 'alt' => 'Hard-shell suitcase'],
                    ['url' => 'https://images.unsplash.com/photo-1565026057447-bc90a3dceb87?auto=format&fit=crop&w=500&q=80', 'alt' => 'Travel duffel bag'],
                    ['url' => asset('assets/images/lag2.jpg'), 'alt' => 'Cabin luggage set'],
                ],
            ],
            [
                'title' => 'Smartwatches',
                'subtitle' => 'Track fitness in style',
                'lead_bg' => '#eaf4fb',
                'lead_color' => '#0b2a4a',
                'url' => route('products.index', ['cat' => 'electronics']),
                'products' => Product::published()
                    ->whereIn('slug', ['amoled-black-smartwatch', 'sport-smartwatch', 'classic-leather-smartwatch'])
                    ->with(['images', 'defaultVariant'])
                    ->get(),
                'fallbacks' => [
                    ['url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=500&q=80', 'alt' => 'Black smartwatch'],
                    ['url' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?auto=format&fit=crop&w=500&q=80', 'alt' => 'Sport smartwatch'],
                    ['url' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=500&q=80', 'alt' => 'Classic smartwatch'],
                ],
            ],
            [
                'title' => 'Home Essentials',
                'subtitle' => 'Comfort for every room',
                'lead_bg' => '#fbf6e4',
                'lead_color' => '#c9a017',
                'url' => route('products.index', ['cat' => 'home-furniture']),
                'products' => Product::published()
                    ->whereIn('slug', ['modern-bedroom-bed', 'nordic-dining-set', 'plush-living-room-sofa'])
                    ->with(['images', 'defaultVariant'])
                    ->get(),
                'fallbacks' => [
                    ['url' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=500&q=80', 'alt' => 'Bedroom furniture'],
                    ['url' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=500&q=80', 'alt' => 'Dining set'],
                    ['url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80', 'alt' => 'Living room sofa'],
                ],
            ],
            [
                'title' => 'Kitchen Appliances',
                'subtitle' => 'Cook smarter every day',
                'lead_bg' => '#e8f1fb',
                'lead_color' => '#1d4e89',
                'url' => route('products.index', ['cat' => 'home-furniture']),
                'products' => Product::published()
                    ->whereIn('slug', ['digital-microwave-oven', 'rapid-air-fryer', 'high-speed-smoothie-blender'])
                    ->with(['images', 'defaultVariant'])
                    ->get(),
                'fallbacks' => [
                    ['url' => 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=500&q=80', 'alt' => 'Microwave oven'],
                    ['url' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?auto=format&fit=crop&w=500&q=80', 'alt' => 'Air fryer'],
                    ['url' => 'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=500&q=80', 'alt' => 'Blender'],
                ],
            ],
        ];

        return view('trading', compact(
            'categories',
            'banners',
            'tradingCategories',
            'tradingNavCategories',
            'marketplaceProducts',
            'bestsellers',
            'similarSearches'
        ));
    }
}
