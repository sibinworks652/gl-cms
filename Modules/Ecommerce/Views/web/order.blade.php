@extends('ecommerce::web.layout')

@section('title', $order->order_number)

@section('content')
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h4 mb-4">{{ $order->order_number }}</h1>
                        @foreach($order->items as $item)
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <div>
                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                    <div class="small text-muted">{{ $item->variant_name }} @if($item->vendor) / {{ $item->vendor->name }} @endif</div>
                                </div>
                                <div>{{ $item->quantity }} x {{ number_format((float) $item->unit_price, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Status</h5>
                        <p class="mb-1">Order: {{ ucfirst($order->status) }}</p>
                        <p class="mb-1">Payment: {{ ucfirst($order->payment_status) }}</p>
                        <p class="mb-3">Method: {{ strtoupper($order->payment_method) }}</p>
                        <strong>Total: {{ number_format((float) $order->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
