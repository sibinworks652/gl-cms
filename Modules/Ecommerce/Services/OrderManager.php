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
        protected PricingManager $pricing,
    ) {
    }

    public function placeOrder(Cart $cart, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($cart, $data, $user) {
            $cart->loadMissing(['items.product.category.discounts', 'items.product.discounts', 'items.product.vendor', 'items.variant', 'coupon']);
            $totals = $this->pricing->cartTotals($cart, $cart->coupon);

            $order = Order::create([
                'user_id' => $user?->id,
                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon_code,
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'status' => 'pending',
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'processing',
                'payment_method' => $data['payment_method'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_total'],
                'shipping_amount' => $totals['shipping_total'],
                'discount_amount' => $totals['product_discount_total'] + $totals['coupon_discount_total'],
                'grand_total' => $totals['grand_total'],
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

            if ($cart->coupon) {
                $cart->coupon->increment('used_count');
            }

            $this->payments->createForOrder($order, $data['payment_method']);
            $cart->items()->delete();
            $cart->update([
                'coupon_id' => null,
                'coupon_code' => null,
                'coupon_discount_amount' => 0,
            ]);

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
