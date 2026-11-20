<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $tradingProducts = Product::trading()->count();
        $retailProducts = Product::retail()->count();
        $totalOrders = Order::count();
        $totalSales = Order::where('status', '!=', 'cancelled')->sum('total');
        $placedOrders = Order::where('status', 'placed')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalUsers = User::count();
        $activeBanners = Banner::where('is_active', true)->count();
        $tradingCategories = Category::trading()->count();

        $recentOrders = Order::with('items')->latest()->take(6)->get();
        $recentProducts = Product::with(['category', 'defaultVariant', 'images'])->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'tradingProducts',
            'retailProducts',
            'totalOrders',
            'totalSales',
            'placedOrders',
            'completedOrders',
            'totalUsers',
            'activeBanners',
            'tradingCategories',
            'recentOrders',
            'recentProducts'
        ));
    }
}
