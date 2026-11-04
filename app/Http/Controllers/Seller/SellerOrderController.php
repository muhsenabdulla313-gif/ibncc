<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class SellerOrderController extends Controller
{
    /** allowed next statuses for an order line */
    private const FLOW = [
        'placed'    => ['contacted', 'confirmed', 'cancelled'],
        'contacted' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped'   => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    private function mine(OrderItem $item): OrderItem
    {
        abort_unless($item->seller_id === auth()->id(), 404);
        return $item->load('order');
    }

    public function show(OrderItem $item)
    {
        $item = $this->mine($item);
        $o = $item->order;

        return [
            'id' => $item->id, 'order_no' => $o->order_no, 'date' => $o->created_at->format('d M Y, H:i'),
            'status' => $item->status, 'next' => self::FLOW[$item->status] ?? [],
            'price_type' => $item->price_type,
            'buyer' => ['name' => $o->buyer_name, 'phone' => $o->buyer_phone, 'email' => $o->buyer_email],
            'shipping_address' => $o->shipping_address,
            'buyer_note' => $o->buyer_note,
            'title' => $item->title, 'sku' => $item->sku, 'options' => $item->options, 'unit_name' => $item->unit_name,
            'qty' => (float) $item->qty, 'unit_price' => (float) $item->unit_price,
            'tax_rate' => (float) $item->tax_rate, 'tax_amount' => (float) $item->tax_amount, 'line_total' => (float) $item->line_total,
            'seller_note' => $item->seller_note, 'contacted_at' => $item->contacted_at?->format('d M Y, H:i'),
        ];
    }

    public function update(Request $request, OrderItem $item)
    {
        $item = $this->mine($item);
        $in = $request->validate([
            'status'      => 'nullable|in:placed,contacted,confirmed,shipped,delivered,cancelled',
            'seller_note' => 'nullable|string|max:1000',
        ]);

        $data = ['seller_note' => $in['seller_note'] ?? null];

        if (!empty($in['status']) && $in['status'] !== $item->status) {
            if (!in_array($in['status'], self::FLOW[$item->status] ?? [])) {
                return response()->json(['message' => 'That status change is not allowed.', 'errors' => ['status' => ['Invalid status change.']]], 422);
            }
            $data['status'] = $in['status'];
            if ($in['status'] === 'contacted') $data['contacted_at'] = now();
        }
        $item->update($data);

        $this->syncOrderStatus($item->order);

        return response()->json(['message' => 'Order updated.', 'order' => $this->show($item->fresh())]);
    }

    /** Parent order status follows its lines. */
    private function syncOrderStatus($order): void
    {
        $s = $order->items()->pluck('status');
        $status = match (true) {
            $s->every(fn ($x) => $x === 'cancelled') => 'cancelled',
            $s->every(fn ($x) => in_array($x, ['delivered', 'cancelled'])) => 'completed',
            $s->contains(fn ($x) => in_array($x, ['contacted', 'confirmed', 'shipped', 'delivered'])) => 'processing',
            default => 'placed',
        };
        if ($order->status !== $status) $order->update(['status' => $status]);
    }
}
