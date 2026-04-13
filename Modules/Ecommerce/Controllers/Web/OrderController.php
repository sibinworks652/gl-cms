<?php

namespace Modules\Ecommerce\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Ecommerce\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return view('ecommerce::web.orders', [
            'orders' => Order::query()
                ->where('user_id', auth()->id())
                ->latestFirst()
                ->paginate(10),
        ]);
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('ecommerce::web.order', [
            'order' => $order->load(['items.vendor', 'payments']),
        ]);
    }
}
