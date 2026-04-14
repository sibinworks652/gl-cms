<?php

namespace Modules\Ecommerce\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\CartItem;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;
use Modules\Ecommerce\Requests\CartItemRequest;
use Modules\Ecommerce\Services\CartManager;

class CartController extends Controller
{
    public function __construct(
        protected CartManager $cartManager,
    ) {
    }

    public function index()
    {
        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());

        return view('ecommerce::web.cart', [
            'cart' => $cart,
            'totals' => $this->cartManager->totals($cart),
        ]);
    }

    public function store(CartItemRequest $request): JsonResponse
    {
        $product = Product::query()->active()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::query()->findOrFail($request->integer('product_variant_id'))
            : null;

        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());
        $cart = $this->cartManager->add($cart, $product, $request->integer('quantity', 1), $variant);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart.',
            'cart_count' => $cart->items_count,
            'cart' => new \Modules\Ecommerce\Resources\CartResource($cart),
        ]);
    }

    public function update(CartItemRequest $request, CartItem $item): JsonResponse
    {
        $cart = $this->cartManager->updateQuantity($item, $request->integer('quantity', 1));

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'cart_count' => $cart->items_count,
            'cart' => new \Modules\Ecommerce\Resources\CartResource($cart),
        ]);
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $cart = $this->cartManager->remove($item);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => $cart->items_count,
            'cart' => new \Modules\Ecommerce\Resources\CartResource($cart),
        ]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());
        $cart = $this->cartManager->applyCoupon($cart, $request->input('coupon_code'));

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'cart' => new \Modules\Ecommerce\Resources\CartResource($cart),
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());
        $cart = $this->cartManager->removeCoupon($cart);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
            'cart' => new \Modules\Ecommerce\Resources\CartResource($cart),
        ]);
    }
}
