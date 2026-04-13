<?php

namespace Modules\Ecommerce\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Models\Payment;
use Modules\Ecommerce\Resources\PaymentResource;
use Modules\Ecommerce\Services\PaymentManager;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $payments,
    ) {
    }

    public function index(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        return response()->json([
            'data' => PaymentResource::collection($order->payments),
        ]);
    }

    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        $request->validate([
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        abort_unless($payment->order?->user_id === $request->user()?->id, 403);

        $payment = $this->payments->markSucceeded($payment, $request->input('transaction_reference'));

        return response()->json([
            'message' => 'Payment marked as successful.',
            'data' => new PaymentResource($payment),
        ]);
    }
}
