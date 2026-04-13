@extends('ecommerce::web.layout')

@section('title', 'My Orders')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1 class="h4 mb-4">My Orders</h1>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ ucfirst($order->status) }}</td>
                                <td>{{ strtoupper($order->payment_method) }} / {{ ucfirst($order->payment_status) }}</td>
                                <td>{{ number_format((float) $order->grand_total, 2) }}</td>
                                <td class="text-end"><a href="{{ route('ecommerce.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No orders yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
@endsection
