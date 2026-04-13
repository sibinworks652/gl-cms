<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Cart;
use Modules\Ecommerce\Models\Order;

class OrderManager
{
    public function __construct(
        protected PaymentManager $payments,
    ) {
    }

    public function placeOrder(Cart $cart, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($cart, $data, $user) {
            $cart->loadMissing(['items.product.vendor', 'items.variant']);

            $subtotal = (float) $cart->items->sum(fn ($item) => $item->line_total);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $shippingAmount = (float) ($data['shipping_amount'] ?? 0);
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $grandTotal = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

            $order = Order::create([
                'user_id' => $user?->id,
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'status' => 'pending',
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'processing',
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'vendor_id' => $item->product?->vendor_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product?->name ?? 'Product',
                    'variant_name' => $item->variant?->label,
                    'sku' => $item->variant?->sku ?: $item->product?->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);

                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } elseif ($item->product) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            $this->payments->createForOrder($order, $data['payment_method']);
            $cart->items()->delete();

            return $order->fresh(['items.vendor', 'payments']);
        }, 3);
    }

    public function updateStatus(Order $order, array $data): Order
    {
        $order->update([
            'status' => $data['status'] ?? $order->status,
            'payment_status' => $data['payment_status'] ?? $order->payment_status,
        ]);

        return $order->fresh(['items.vendor', 'payments']);
    }
}
