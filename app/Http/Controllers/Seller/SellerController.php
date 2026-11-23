<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->id;

        // 1. Seller Dashboard Statistics (all from database)
        $productsQuery = Product::where('user_id', $sellerId);
        $totalProducts = (clone $productsQuery)->count();
        $activeProducts = (clone $productsQuery)->where('status', 'active')->count();
        $inactiveProducts = (clone $productsQuery)->whereIn('status', ['inactive', 'draft', 'rejected'])->count();

        $totalStock = (float) ProductVariant::whereHas('product', fn ($q) => $q->where('user_id', $sellerId))->sum('stock');

        $orderItemsQuery = OrderItem::where('seller_id', $sellerId);
        $totalOrders = (clone $orderItemsQuery)->count();
        $pendingOrders = (clone $orderItemsQuery)->where('status', 'placed')->count();
        $processingOrders = (clone $orderItemsQuery)->whereIn('status', ['contacted', 'confirmed', 'shipped'])->count();
        $completedOrders = (clone $orderItemsQuery)->where('status', 'delivered')->count();

        $stats = [
            'total_products'    => $totalProducts,
            'active_products'   => $activeProducts,
            'inactive_products' => $inactiveProducts,
            'total_stock'       => $totalStock,
            'total_orders'      => $totalOrders,
            'pending_orders'    => $pendingOrders,
            'processing_orders' => $processingOrders,
            'completed_orders'  => $completedOrders,
        ];

        // 2. Recent Products (last 5)
        $recentProducts = Product::where('user_id', $sellerId)
            ->with(['category', 'unit', 'variants', 'images', 'priceTiers'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // 3. Recent Orders (last 5)
        $recentOrders = OrderItem::with('order:id,order_no,buyer_name,buyer_phone,created_at')
            ->where('seller_id', $sellerId)
            ->latest()
            ->limit(5)
            ->get();

        // 4. Order items for retail & trading tables
        $items = OrderItem::with('order:id,order_no,buyer_name,created_at')
            ->where('seller_id', $sellerId)
            ->latest()->limit(300)->get();

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        return view('seller.index', [
            'mode'           => $request->query('mode') === 'trading' ? 'trading' : 'marketplace',
            'stats'          => $stats,
            'recentProducts' => $recentProducts,
            'recentOrders'   => $recentOrders,
            'retailItems'    => $items->where('price_type', 'retail')->values(),
            'bulkItems'      => $items->where('price_type', 'wholesale')->values(),
            'rootCats'       => Category::active()->withCount('children')->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get(),
            'units'          => Unit::orderBy('name')->get(['id', 'name', 'short_name', 'allow_decimal']),
            'brands'         => Brand::where('is_active', true)->orderBy('name')->pluck('name'),
            'user'           => $user,
            'profile'        => $profile,
        ]);
    }

    /** View seller's public storefront with their products & orders */
    public function storeView(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $marketplaceProducts = Product::where('user_id', $user->id)
            ->retail()
            ->with(['category', 'unit', 'variants', 'images'])
            ->latest('updated_at')
            ->get();

        $tradingProducts = Product::where('user_id', $user->id)
            ->trading()
            ->with(['category', 'unit', 'variants', 'images', 'priceTiers'])
            ->latest('updated_at')
            ->get();

        $orders = OrderItem::with('order')
            ->where('seller_id', $user->id)
            ->latest()
            ->limit(100)
            ->get();

        return view('seller.store', compact('user', 'profile', 'marketplaceProducts', 'tradingProducts', 'orders'));
    }

    /** Update seller's store profile (name, logo, banner, about, contact) */
    public function updateStore(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:150',
            'about'      => 'nullable|string|max:2000',
            'phone'      => 'nullable|string|max:20',
            'whatsapp'   => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:100',
            'logo'       => 'nullable|image|max:4096',
            'banner'     => 'nullable|image|max:8192',
        ]);

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('profiles/logos', 'public');
            $profile->photo = $path;
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('profiles/banners', 'public');
            $profile->cover = $path;
        }

        if (array_key_exists('store_name', $validated)) {
            $profile->organization = $validated['store_name'];
        }
        if (array_key_exists('about', $validated)) {
            $profile->about = $validated['about'];
        }
        if (array_key_exists('whatsapp', $validated)) {
            $profile->whatsapp = $validated['whatsapp'];
        }

        $profile->save();

        $userUpdate = [];
        if (!empty($validated['phone'])) {
            $userUpdate['phone'] = $validated['phone'];
        }
        if (!empty($validated['email'])) {
            $userUpdate['email'] = $validated['email'];
        }
        if (!empty($userUpdate)) {
            $user->update($userUpdate);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Store information updated successfully.',
                'store'   => [
                    'name'     => $profile->organization ?: $user->name,
                    'about'    => $profile->about,
                    'photo'    => $profile->photo_url,
                    'cover'    => $profile->cover_url,
                    'phone'    => $user->phone,
                    'whatsapp' => $profile->whatsapp,
                    'email'    => $user->email,
                ],
            ]);
        }

        return back()->with('success', 'Store information updated successfully.');
    }
}
