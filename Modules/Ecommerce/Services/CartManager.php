<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Ecommerce\Models\Cart;
use Modules\Ecommerce\Models\CartItem;
use Modules\Ecommerce\Models\Coupon;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;

class CartManager
{
    public function __construct(
        protected PricingManager $pricing,
    ) {
    }

    public function resolve(?User $user, ?string $sessionId): Cart
    {
        $query = Cart::query()->with(['items.product.vendor', 'items.variant', 'coupon']);

        $cart = $user
            ? $query->firstOrCreate(['user_id' => $user->id])
            : $query->firstOrCreate(['session_id' => $sessionId ?: request()->session()->getId()]);

        if ($user && $sessionId) {
            Cart::query()
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->whereKeyNot($cart->id)
                ->with('items')
                ->get()
                ->each(function (Cart $guestCart) use ($cart) {
                    foreach ($guestCart->items as $item) {
                        if ($item->product) {
                            $this->add($cart, $item->product, $item->quantity, $item->variant);
                        }
                    }

                    $guestCart->delete();
                });
        }

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function add(Cart $cart, Product $product, int $quantity = 1, ?ProductVariant $variant = null): Cart
    {
        $unitPrice = (float) ($variant?->price ?: $product->sale_price ?: $product->base_price);
        $requestedQuantity = max(1, $quantity);

        $item = CartItem::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        $targetQuantity = max(1, (int) $item->quantity + $requestedQuantity);
        $available = $variant?->inventory ?? $product->inventory ?? null;

        if ($available && ! $available->canFulfill($targetQuantity)) {
            abort(422, 'Requested quantity is not available in inventory.');
        }

        $item->quantity = $targetQuantity;
        $item->unit_price = $unitPrice;
        $item->save();

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function updateQuantity(CartItem $item, int $quantity): Cart
    {
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        return $item->cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function remove(CartItem $item): Cart
    {
        $cart = $item->cart;
        $item->delete();

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function clear(Cart $cart): Cart
    {
        $cart->items()->delete();

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function itemsGroupedByVendor(Cart $cart): Collection
    {
        return $cart->items->groupBy(fn (CartItem $item) => $item->product?->vendor?->name ?: 'Marketplace');
    }

    public function applyCoupon(Cart $cart, string $code): Cart
    {
        $coupon = Coupon::query()->active()->where('code', strtoupper(trim($code)))->first();

        abort_unless($coupon, 422, 'Invalid or expired coupon code.');

        $totals = $this->pricing->cartTotals($cart, $coupon);

        abort_if($totals['coupon_discount_total'] <= 0, 422, 'This coupon is not applicable to the current cart.');

        $cart->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'coupon_discount_amount' => $totals['coupon_discount_total'],
        ]);

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function removeCoupon(Cart $cart): Cart
    {
        $cart->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount_amount' => 0,
        ]);

        return $cart->fresh(['items.product.vendor', 'items.variant', 'coupon']);
    }

    public function totals(Cart $cart): array
    {
        return $this->pricing->cartTotals($cart, $cart->coupon);
    }
}
