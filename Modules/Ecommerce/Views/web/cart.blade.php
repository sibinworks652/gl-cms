@extends('ecommerce::web.layout')

@section('title', 'Cart')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1 class="h4 mb-4">Shopping Cart</h1>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cart->items as $item)
                            <tr>
                                <td>{{ $item->product?->name }}</td>
                                <td>{{ $item->variant?->label ?: 'Default' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">Your cart is empty.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="row g-4 mt-2">
                    <div class="col-lg-6">
                        <div class="border rounded p-3">
                            <label class="form-label">Coupon Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="couponCode" value="{{ $cart->coupon_code }}" placeholder="SAVE10">
                                <button class="btn btn-outline-primary" type="button" id="applyCouponBtn">Apply</button>
                                @if($cart->coupon_code)
                                    <button class="btn btn-outline-danger" type="button" id="removeCouponBtn">Remove</button>
                                @endif
                            </div>
                            <div id="couponFeedback" class="small mt-2"></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ number_format((float) $totals['subtotal'], 2) }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Product Discounts</span><span>-{{ number_format((float) $totals['product_discount_total'], 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Coupon Discount</span><span>-{{ number_format((float) $totals['coupon_discount_total'], 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Tax</span><span>{{ number_format((float) $totals['tax_total'], 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-3"><span>Shipping</span><span>{{ number_format((float) $totals['shipping_total'], 2) }}</span></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Grand Total: {{ number_format((float) $totals['grand_total'], 2) }}</strong>
                                @auth
                                    <a href="{{ route('ecommerce.checkout.index') }}" class="btn btn-primary {{ $cart->items->isEmpty() ? 'disabled' : '' }}">Proceed to Checkout</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary {{ $cart->items->isEmpty() ? 'disabled' : '' }}">Login to Checkout</a>
                                @endauth
                            </div>
                            @guest
                                @if($cart->items->isNotEmpty())
                                    <div class="small text-muted mt-2">Please login first to continue checkout.</div>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const feedback = document.getElementById('couponFeedback');
    const codeInput = document.getElementById('couponCode');
    document.getElementById('applyCouponBtn')?.addEventListener('click', async function () {
        const form = new FormData();
        form.append('coupon_code', codeInput.value);
        try {
            const response = await fetch(@json(route('ecommerce.cart.coupon.apply')), {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
                body: form
            });
            const result = await response.json();
            feedback.innerHTML = '<span class="' + (result.success ? 'text-success' : 'text-danger') + '">' + result.message + '</span>';
            if (result.success) window.location.reload();
        } catch (error) {
            feedback.innerHTML = '<span class="text-danger">Coupon could not be applied.</span>';
        }
    });
    document.getElementById('removeCouponBtn')?.addEventListener('click', async function () {
        await fetch(@json(route('ecommerce.cart.coupon.remove')), {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'}
        });
        window.location.reload();
    });
});
</script>
@endpush
