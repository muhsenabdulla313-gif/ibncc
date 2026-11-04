<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Unit;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $items = OrderItem::with('order:id,order_no,buyer_name,created_at')
            ->where('seller_id', $request->user()->id)
            ->latest()->limit(300)->get();

        return view('seller.index', [
            'mode'        => $request->query('mode') === 'trading' ? 'trading' : 'marketplace',
            'retailItems' => $items->where('price_type', 'retail')->values(),
            'bulkItems'   => $items->where('price_type', 'wholesale')->values(),
            'rootCats'    => Category::active()->withCount('children')->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get(),
            'units'       => Unit::orderBy('name')->get(['id', 'name', 'short_name', 'allow_decimal']),
            'brands'      => Brand::where('is_active', true)->orderBy('name')->pluck('name'),
        ]);
    }
}
