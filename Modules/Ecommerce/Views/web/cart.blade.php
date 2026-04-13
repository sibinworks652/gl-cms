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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <strong>Subtotal: {{ number_format((float) $cart->subtotal, 2) }}</strong>
                    <a href="{{ route('ecommerce.checkout.index') }}" class="btn btn-primary {{ $cart->items->isEmpty() ? 'disabled' : '' }}">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
@endsection
