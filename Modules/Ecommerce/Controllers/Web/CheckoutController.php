<?php

namespace Modules\Ecommerce\Controllers\Web;

use App\Http\Controllers\Controller;
use Modules\Ecommerce\Requests\CheckoutRequest;
use Modules\Ecommerce\Services\CartManager;
use Modules\Ecommerce\Services\OrderManager;
use Modules\Ecommerce\Support\EcommerceSettings;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartManager $cartManager,
        protected OrderManager $orders,
    ) {
    }

    public function index()
    {
        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());

        abort_if($cart->items->isEmpty(), 404);

        return view('ecommerce::web.checkout', [
            'cart' => $cart,
            'totals' => $this->cartManager->totals($cart),
            'paymentMethods' => EcommerceSettings::enabledPaymentMethods(),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $cart = $this->cartManager->resolve(auth()->user(), session()->getId());
        $order = $this->orders->placeOrder($cart, $request->validated(), auth()->user());

        return redirect()->route('ecommerce.orders.show', $order)->with('success', 'Order placed successfully.');
    }
}
