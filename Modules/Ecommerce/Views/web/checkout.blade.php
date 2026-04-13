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
                                    <option value="cod">Cash on Delivery</option>
                                    <option value="razorpay">Razorpay</option>
                                    <option value="stripe">Stripe</option>
                                </select>
                            </div>
                            <div class="col-md-4"><input name="shipping_amount" type="number" step="0.01" class="form-control" placeholder="Shipping" value="{{ old('shipping_amount', 0) }}"></div>
                            <div class="col-md-4"><input name="tax_amount" type="number" step="0.01" class="form-control" placeholder="Tax" value="{{ old('tax_amount', 0) }}"></div>
                            <div class="col-12"><textarea name="notes" class="form-control" rows="3" placeholder="Order notes">{{ old('notes') }}</textarea></div>
                            <div class="col-12"><button class="btn btn-primary w-100" type="submit">Place Order</button></div>
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
                        <div class="d-flex justify-content-between pt-3">
                            <strong>Subtotal</strong>
                            <strong>{{ number_format((float) $cart->subtotal, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
