<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Ecommerce\Models\Cart;
use Modules\Ecommerce\Models\CartItem;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;

class CartManager
{
    public function resolve(?User $user, ?string $sessionId): Cart
    {
        $query = Cart::query()->with(['items.product.vendor', 'items.variant']);

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

        return $cart->fresh(['items.product.vendor', 'items.variant']);
    }

    public function add(Cart $cart, Product $product, int $quantity = 1, ?ProductVariant $variant = null): Cart
    {
        $unitPrice = (float) ($variant?->price ?: $product->sale_price ?: $product->base_price);

        $item = CartItem::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        $item->quantity = max(1, (int) $item->quantity + $quantity);
        $item->unit_price = $unitPrice;
        $item->save();

        return $cart->fresh(['items.product.vendor', 'items.variant']);
    }

    public function updateQuantity(CartItem $item, int $quantity): Cart
    {
        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        return $item->cart->fresh(['items.product.vendor', 'items.variant']);
    }

    public function remove(CartItem $item): Cart
    {
        $cart = $item->cart;
        $item->delete();

        return $cart->fresh(['items.product.vendor', 'items.variant']);
    }

    public function clear(Cart $cart): Cart
    {
        $cart->items()->delete();

        return $cart->fresh(['items.product.vendor', 'items.variant']);
    }

    public function itemsGroupedByVendor(Cart $cart): Collection
    {
        return $cart->items->groupBy(fn (CartItem $item) => $item->product?->vendor?->name ?: 'Marketplace');
    }
}
