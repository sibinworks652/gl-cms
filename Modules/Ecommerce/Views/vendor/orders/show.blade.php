@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
                <div class="page-title-right">
                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-primary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-1"></iconify-icon> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vendorOrderItems as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product_name }}
                                            @if($item->variant_name)
                                                <br><small class="text-muted">{{ $item->variant_name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->sku }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.orders.update', $order) }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                    @if($order->customer_phone)
                        <p class="mb-0"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shipping Address</h5>
                </div>
                <div class="card-body">
                    {{ $order->shipping_address }}
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status:</span>
                        <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : ($order->status === 'cancelled' ? 'danger' : 'info')) }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment:</span>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Order Total:</strong></span>
                        <span><strong>{{ number_format($order->grand_total, 2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
