<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected CartController $cartController;

    public function __construct(CartController $cartController)
    {
        $this->cartController = $cartController;
    }

    /**
     * Display the checkout inquiry & payment page.
     */
    public function index(Request $request)
    {
        $isBuyNow = ($request->query('type') === 'buy_now');

        if ($isBuyNow) {
            $buyNowItem = session()->get('buy_now_item');
            if (!$buyNowItem) {
                return redirect()->route('products.index')
                    ->with('error', 'No active direct purchase found. Please select a product.');
            }
            $rawItems = [$buyNowItem['variant_id'] => $buyNowItem];
            $checkoutType = 'buy_now';
        } else {
            $rawItems = session()->get('cart', []);
            if (Auth::check() && empty($rawItems)) {
                $dbItems = CartItem::where('user_id', Auth::id())->get();
                foreach ($dbItems as $dbItem) {
                    $rawItems[$dbItem->variant_id] = [
                        'product_id' => $dbItem->product_id,
                        'variant_id' => $dbItem->variant_id,
                        'qty'        => (float) $dbItem->qty,
                        'mode'       => 'retail',
                    ];
                }
            }
            if (empty($rawItems)) {
                return redirect()->route('cart.index')
                    ->with('info', 'Your cart is empty. Please add items before checking out.');
            }
            $checkoutType = 'cart';
        }

        $processed = $this->cartController->buildCartData($rawItems);

        if (empty($processed['items'])) {
            return redirect()->route('cart.index')
                ->with('error', 'The selected products are no longer available.');
        }

        // Pre-fill user delivery information
        $user = Auth::user();
        $defaultAddress = [
            'name'   => $user?->name ?? '',
            'phone'  => $user?->phone ?? '',
            'email'  => $user?->email ?? '',
            'street' => '',
            'city'   => '',
            'state'  => '',
            'pin'    => '',
        ];

        $savedAddresses = [];
        if ($user) {
            $addresses = $user->addresses()->with(['state', 'district', 'country'])->get();
            foreach ($addresses as $addr) {
                $savedAddresses[] = [
                    'id'     => $addr->id,
                    'type'   => $addr->type,
                    'street' => $addr->house ?? '',
                    'city'   => $addr->district?->name ?? '',
                    'state'  => $addr->state?->name ?? '',
                    'pin'    => $addr->pin ?? '',
                    'phone'  => $user->phone ?? '',
                    'name'   => $user->name,
                ];
            }

            if (!empty($savedAddresses)) {
                $first = $savedAddresses[0];
                $defaultAddress['street'] = $first['street'];
                $defaultAddress['city']   = $first['city'];
                $defaultAddress['state']  = $first['state'];
                $defaultAddress['pin']    = $first['pin'];
            }
        }

        return view('cart.checkout', [
            'items'          => $processed['items'],
            'subtotal'       => $processed['subtotal'],
            'discount'       => $processed['discount'],
            'total'          => $processed['total'],
            'checkoutType'   => $checkoutType,
            'defaultAddress' => $defaultAddress,
            'savedAddresses' => $savedAddresses,
            'user'           => $user,
        ]);
    }

    /**
     * Process order / inquiry placement.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'checkout_type' => 'required|string|in:cart,buy_now',
            'buyer_name'    => 'required|string|max:150',
            'buyer_phone'   => 'required|string|max:20',
            'buyer_email'   => 'nullable|email|max:150',
            'street'        => 'required|string|max:300',
            'city'          => 'required|string|max:100',
            'state'         => 'nullable|string|max:100',
            'pin'           => 'nullable|string|max:20',
            'buyer_note'    => 'nullable|string|max:1000',
        ]);

        $checkoutType = $validated['checkout_type'];

        if ($checkoutType === 'buy_now') {
            $buyNowItem = session()->get('buy_now_item');
            if (!$buyNowItem) {
                return redirect()->route('products.index')
                    ->with('error', 'Session expired. Please select the product again.');
            }
            $rawItems = [$buyNowItem['variant_id'] => $buyNowItem];
        } else {
            $rawItems = session()->get('cart', []);
            if (Auth::check() && empty($rawItems)) {
                $dbItems = CartItem::where('user_id', Auth::id())->get();
                foreach ($dbItems as $dbItem) {
                    $rawItems[$dbItem->variant_id] = [
                        'product_id' => $dbItem->product_id,
                        'variant_id' => $dbItem->variant_id,
                        'qty'        => (float) $dbItem->qty,
                        'mode'       => 'retail',
                    ];
                }
            }
        }

        if (empty($rawItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your order could not be placed because there are no items.');
        }

        $processed = $this->cartController->buildCartData($rawItems);
        if (empty($processed['items'])) {
            return redirect()->route('cart.index')
                ->with('error', 'The items in your order are no longer available.');
        }

        // Determine buyer user ID for foreign key constraint
        $buyerId = Auth::id();
        if (!$buyerId) {
            // Check if guest user already exists by phone or email
            $existingUser = User::where('phone', $validated['buyer_phone'])
                ->orWhere(function ($q) use ($validated) {
                    if (!empty($validated['buyer_email'])) {
                        $q->where('email', $validated['buyer_email']);
                    }
                })->first();

            if ($existingUser) {
                $buyerId = $existingUser->id;
            } else {
                // Create a guest user account
                $guestUser = User::create([
                    'role_id'  => 3, // guest role
                    'name'     => $validated['buyer_name'],
                    'phone'    => $validated['buyer_phone'],
                    'email'    => $validated['buyer_email'] ?: ('guest_' . time() . '_' . Str::random(5) . '@cchub.local'),
                    'password' => Hash::make(Str::random(24)),
                ]);
                $buyerId = $guestUser->id;
            }
        }

        $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_no'         => $orderNo,
                'buyer_id'         => $buyerId,
                'buyer_name'       => $validated['buyer_name'],
                'buyer_phone'      => $validated['buyer_phone'],
                'buyer_email'      => $validated['buyer_email'] ?? null,
                'shipping_address' => [
                    'name'   => $validated['buyer_name'],
                    'phone'  => $validated['buyer_phone'],
                    'street' => $validated['street'],
                    'city'   => $validated['city'],
                    'state'  => $validated['state'] ?? '',
                    'pin'    => $validated['pin'] ?? '',
                ],
                'buyer_note'       => $validated['buyer_note'] ?? null,
                'tax_total'        => 0.00,
                'total'            => $processed['total'],
                'status'           => 'placed',
            ]);

            foreach ($processed['items'] as $item) {
                $product = Product::with('seller')->find($item['product_id']);
                $variant = ProductVariant::find($item['variant_id']);

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['product_id'],
                    'variant_id'   => $item['variant_id'],
                    'seller_id'    => $product?->user_id ?? 1,
                    'seller_name'  => $product?->seller?->name ?? 'CC Hub Seller',
                    'seller_phone' => $product?->seller?->phone ?? null,
                    'title'        => $item['title'],
                    'sku'          => $item['sku'],
                    'options'      => $variant?->options,
                    'unit_name'    => $item['unit_name'],
                    'hsn_code'     => $product?->hsn_code,
                    'price_type'   => ($item['mode'] === 'wholesale') ? 'wholesale' : 'retail',
                    'qty'          => $item['qty'],
                    'unit_price'   => $item['unit_price'],
                    'tax_rate'     => (float) ($product?->tax_rate ?? 0),
                    'tax_amount'   => 0.00,
                    'line_total'   => $item['line_total'],
                    'status'       => 'placed',
                ]);

                // Inventory management: decrement stock if tracked
                if ($product && $product->track_stock && $variant) {
                    $variant->decrement('stock', $item['qty']);

                    StockMovement::create([
                        'variant_id'     => $variant->id,
                        'user_id'        => $buyerId,
                        'type'           => 'sale',
                        'qty_change'     => -$item['qty'],
                        'qty_after'      => $variant->fresh()->stock,
                        'note'           => "Order #{$orderNo}",
                        'reference_type' => Order::class,
                        'reference_id'   => $order->id,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to place order: ' . $e->getMessage());
        }

        // Cleanup: If Buy Now, forget buy_now_item; if Cart, clear cart
        if ($checkoutType === 'buy_now') {
            session()->forget('buy_now_item');
            if ($request->hasSession()) {
                $request->session()->forget('buy_now_item');
            }
        } else {
            session()->forget('cart');
            if ($request->hasSession()) {
                $request->session()->forget('cart');
            }
            if (Auth::check()) {
                CartItem::where('user_id', Auth::id())->delete();
            }
        }

        return redirect()->route('order.success', $order->order_no)
            ->with('success', 'Your inquiry / order has been placed successfully!');
    }

    /**
     * Display order success confirmation page.
     */
    public function success(string $order_no)
    {
        $order = Order::with(['items.product', 'items.variant'])
            ->where('order_no', $order_no)
            ->firstOrFail();

        return view('cart.success', [
            'order' => $order,
        ]);
    }
}
