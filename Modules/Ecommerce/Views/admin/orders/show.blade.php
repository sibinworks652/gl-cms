@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">{{ $order->order_number }}</h4>
                <p class="text-muted mb-0">Placed {{ optional($order->placed_at)->format('d M Y h:i A') }}</p>
            </div>
            <a href="{{ route('admin.ecommerce.orders.index') }}" class="btn btn-light">Back</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Order Items</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Vendor</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}<div class="small text-muted">{{ $item->variant_name }}</div></td>
                                        <td>{{ $item->vendor?->name ?? 'Marketplace' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td>{{ number_format((float) $item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Update Status</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.ecommerce.orders.update', $order) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Order Status</label>
                                <select name="status" class="form-select">
                                    @foreach(['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled'] as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    @foreach(['pending', 'processing', 'paid', 'failed', 'refunded'] as $status)
                                        <option value="{{ $status }}" @selected($order->payment_status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Save Status</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header"><h5 class="mb-0">Payment Details</h5></div>
                    <div class="card-body">
                        @foreach($order->payments as $payment)
                            <div class="border rounded p-3 mb-3">
                                <div class="fw-semibold">{{ strtoupper($payment->method) }}</div>
                                <div class="small text-muted">{{ $payment->transaction_reference }}</div>
                                <div>Status: {{ ucfirst($payment->status) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
