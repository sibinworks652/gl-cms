<?php

namespace Modules\Ecommerce\Services;

use Modules\Ecommerce\Models\Cart;
use Modules\Ecommerce\Models\Coupon;
use Modules\Ecommerce\Models\Discount;
use Modules\Ecommerce\Models\Product;

class PricingManager
{
    public function productDiscount(Product $product): ?Discount
    {
        $product->loadMissing(['discounts', 'category.discounts']);

        $discounts = $product->discounts
            ->merge($product->category?->discounts ?? collect())
            ->filter(fn ($discount) => $this->isDiscountActive($discount))
            ->unique('id');

        return $discounts
            ->sortByDesc(fn (Discount $discount) => $discount->calculateAmount((float) ($product->sale_price ?: $product->base_price)))
            ->first();
    }

    public function productPricing(Product $product, float $baseAmount, int $quantity = 1): array
    {
        $discount = $this->productDiscount($product);
        $baseLine = round($baseAmount * $quantity, 2);
        $discountAmount = $discount ? $discount->calculateAmount($baseLine) : 0.0;
        $taxable = max($baseLine - $discountAmount, 0);
        $taxAmount = round($taxable * ((float) ($product->tax_percentage ?? 0) / 100), 2);
        $shippingAmount = round((float) ($product->shipping_cost ?? 0) * $quantity, 2);

        return [
            'base_line' => $baseLine,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'final_line_total' => max($taxable + $taxAmount + $shippingAmount, 0),
        ];
    }

    public function cartTotals(Cart $cart, ?Coupon $coupon = null): array
    {
        $cart->loadMissing(['items.product.category.discounts', 'items.product.discounts', 'items.variant']);

        $subtotal = 0.0;
        $productDiscountTotal = 0.0;
        $taxTotal = 0.0;
        $shippingTotal = 0.0;

        foreach ($cart->items as $item) {
            if (! $item->product) {
                continue;
            }

            $pricing = $this->productPricing($item->product, (float) $item->unit_price, (int) $item->quantity);
            $subtotal += $pricing['base_line'];
            $productDiscountTotal += $pricing['discount_amount'];
            $taxTotal += $pricing['tax_amount'];
            $shippingTotal += $pricing['shipping_amount'];
        }

        $discountedSubtotal = max($subtotal - $productDiscountTotal, 0);
        $couponDiscount = $coupon ? $coupon->calculateAmount($discountedSubtotal) : 0.0;
        $grandTotal = max($discountedSubtotal - $couponDiscount + $taxTotal + $shippingTotal, 0);

        return [
            'subtotal' => round($subtotal, 2),
            'product_discount_total' => round($productDiscountTotal, 2),
            'coupon_discount_total' => round($couponDiscount, 2),
            'tax_total' => round($taxTotal, 2),
            'shipping_total' => round($shippingTotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    protected function isDiscountActive(Discount $discount): bool
    {
        if (! $discount->status) {
            return false;
        }
        if ($discount->start_date && $discount->start_date->isFuture()) {
            return false;
        }
        if ($discount->end_date && $discount->end_date->isPast()) {
            return false;
        }
        if ($discount->usage_limit !== null && $discount->used_count >= $discount->usage_limit) {
            return false;
        }

        return true;
    }
}
