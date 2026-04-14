@extends('ecommerce::web.layout')

@section('title', 'Checkout')

@section('content')
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h4 mb-4">Checkout</h1>
                        <form method="POST" action="{{ route('ecommerce.checkout.store') }}" class="row g-3">
                            @csrf
                            <div class="col-md-6"><input name="customer_name" class="form-control" placeholder="Full name" value="{{ old('customer_name', auth()->user()?->name) }}" required></div>
                            <div class="col-md-6"><input name="customer_email" type="email" class="form-control" placeholder="Email" value="{{ old('customer_email', auth()->user()?->email) }}" required></div>
                            <div class="col-md-6"><input name="customer_phone" class="form-control" placeholder="Phone" value="{{ old('customer_phone') }}"></div>
                            <div class="col-12"><textarea name="shipping_address" class="form-control" rows="4" placeholder="Shipping address" required>{{ old('shipping_address') }}</textarea></div>
                            <div class="col-12"><textarea name="billing_address" class="form-control" rows="4" placeholder="Billing address">{{ old('billing_address') }}</textarea></div>
                            <div class="col-md-4">
                                <select name="payment_method" class="form-select">
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>
                                            {{ match($method) {
                                                'cod' => 'Cash on Delivery',
                                                'razorpay' => 'Razorpay',
                                                'stripe' => 'Stripe',
                                                'paypal' => 'PayPal',
                                                'paystack' => 'Paystack',
                                                default => strtoupper($method),
                                            } }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if(empty($paymentMethods))
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0">No payment methods are active right now. Please contact support.</div>
                                </div>
                            @endif
                            <div class="col-12"><textarea name="notes" class="form-control" rows="3" placeholder="Order notes">{{ old('notes') }}</textarea></div>
                            <div class="col-12"><button class="btn btn-primary w-100" type="submit" @disabled(empty($paymentMethods))>Place Order</button></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5>Order Summary</h5>
                        @foreach($cart->items as $item)
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>{{ $item->product?->name }} x {{ $item->quantity }}</span>
                                <span>{{ number_format((float) $item->line_total, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between pt-3"><span>Subtotal</span><strong>{{ number_format((float) $totals['subtotal'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between pt-2"><span>Product Discounts</span><span>-{{ number_format((float) $totals['product_discount_total'], 2) }}</span></div>
                        <div class="d-flex justify-content-between pt-2"><span>Coupon Discount</span><span>-{{ number_format((float) $totals['coupon_discount_total'], 2) }}</span></div>
                        <div class="d-flex justify-content-between pt-2"><span>Tax</span><span>{{ number_format((float) $totals['tax_total'], 2) }}</span></div>
                        <div class="d-flex justify-content-between pt-2"><span>Shipping</span><span>{{ number_format((float) $totals['shipping_total'], 2) }}</span></div>
                        <div class="d-flex justify-content-between pt-3 border-top mt-3">
                            <strong>Grand Total</strong>
                            <strong>{{ number_format((float) $totals['grand_total'], 2) }}</strong>
                        </div>
                        @if($cart->coupon_code)
                            <div class="small text-success mt-2">Coupon applied: <code>{{ $cart->coupon_code }}</code></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
