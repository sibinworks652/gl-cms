<?php

namespace Modules\Ecommerce\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Requests\CheckoutRequest;
use Modules\Ecommerce\Resources\OrderResource;
use Modules\Ecommerce\Services\CartManager;
use Modules\Ecommerce\Services\OrderManager;

class OrderController extends Controller
{
    public function __construct(
        protected CartManager $cartManager,
        protected OrderManager $orders,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()?->id)
            ->with(['items.vendor', 'payments'])
            ->latestFirst()
            ->paginate(10);

        return response()->json([
            'data' => OrderResource::collection($orders->getCollection()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $cart = $this->cartManager->resolve($request->user(), $request->header('X-Cart-Session'));
        $order = $this->orders->placeOrder($cart, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        return response()->json([
            'data' => new OrderResource($order->load(['items.vendor', 'payments'])),
        ]);
    }
}
