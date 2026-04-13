<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Requests\OrderStatusRequest;
use Modules\Ecommerce\Services\OrderManager;

class OrderController extends Controller
{
    public function __construct(
        protected OrderManager $orders,
    ) {
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latestFirst()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::admin.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        return view('ecommerce::admin.orders.show', [
            'order' => $order->load(['items.vendor', 'payments']),
        ]);
    }

    public function update(OrderStatusRequest $request, Order $order)
    {
        $this->orders->updateStatus($order, $request->validated());

        return redirect()->route('admin.ecommerce.orders.show', $order)->with('success', 'Order updated successfully.');
    }
}
