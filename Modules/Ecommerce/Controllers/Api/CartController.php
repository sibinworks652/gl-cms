<?php

namespace Modules\Ecommerce\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\CartItem;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;
use Modules\Ecommerce\Requests\CartItemRequest;
use Modules\Ecommerce\Resources\CartResource;
use Modules\Ecommerce\Services\CartManager;

class CartController extends Controller
{
    public function __construct(
        protected CartManager $cartManager,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartManager->resolve($request->user(), $request->header('X-Cart-Session'));

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }

    public function store(CartItemRequest $request): JsonResponse
    {
        $product = Product::query()->active()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::query()->findOrFail($request->integer('product_variant_id'))
            : null;

        $cart = $this->cartManager->resolve($request->user(), $request->header('X-Cart-Session'));
        $cart = $this->cartManager->add($cart, $product, $request->integer('quantity', 1), $variant);

        return response()->json([
            'message' => 'Product added to cart.',
            'data' => new CartResource($cart),
        ]);
    }

    public function update(CartItemRequest $request, CartItem $item): JsonResponse
    {
        $cart = $this->cartManager->updateQuantity($item, $request->integer('quantity', 1));

        return response()->json([
            'message' => 'Cart updated successfully.',
            'data' => new CartResource($cart),
        ]);
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $cart = $this->cartManager->remove($item);

        return response()->json([
            'message' => 'Item removed from cart.',
            'data' => new CartResource($cart),
        ]);
    }
}
