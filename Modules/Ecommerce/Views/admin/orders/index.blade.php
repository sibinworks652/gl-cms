@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="mb-4">
            <h4 class="mb-1">Orders</h4>
            <p class="text-muted mb-0">Track order progress, payment states, and fulfillment status.</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    <div>{{ $order->customer_name }}</div>
                                    <div class="small text-muted">{{ $order->customer_email }}</div>
                                </td>
                                <td>{{ $order->items_count }}</td>
                                <td>{{ strtoupper($order->payment_method) }} / {{ ucfirst($order->payment_status) }}</td>
                                <td>{{ number_format((float) $order->grand_total, 2) }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ ucfirst($order->status) }}</span></td>
                                <td class="text-end"><a href="{{ route('admin.ecommerce.orders.show', $order) }}" class="btn btn-soft-primary btn-sm">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No orders found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $orders->links('admin.vendor.pagination') }}</div>
        </div>
    </div>
@endsection
