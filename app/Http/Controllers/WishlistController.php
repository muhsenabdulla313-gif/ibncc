<?php

namespace App\Http\Controllers;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
  

public function index()
{
    $items = Wishlist::with('product.images')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return view('wishlist', compact('items'));
}

    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Please log in to use the wishlist.'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $userId    = auth()->id();
        $productId = (int) $request->product_id;

        $existing = Wishlist::where('user_id', $userId)
                            ->where('product_id', $productId)
                            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success'    => true,
                'wishlisted' => false,
                'message'    => 'Removed from wishlist.',
            ]);
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success'    => true,
            'wishlisted' => true,
            'message'    => 'Added to wishlist!',
        ]);
    }
    public function merge(Request $request)
{
    $request->validate([
        'product_ids'   => 'required|array|max:200',
        'product_ids.*' => 'integer',
    ]);

    $userId = auth()->id();
    $ids    = Product::whereIn('id', $request->product_ids)->pluck('id');

    foreach ($ids as $id) {
        Wishlist::firstOrCreate(['user_id' => $userId, 'product_id' => $id]);
    }

    return response()->json([
        'success' => true,
        'merged'  => $ids,
    ]);
}
public function clear()
{
    Wishlist::where('user_id', auth()->id())->delete();

    return response()->json(['success' => true, 'message' => 'Wishlist cleared.']);
}
}
