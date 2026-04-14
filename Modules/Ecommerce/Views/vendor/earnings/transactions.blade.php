@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Transactions</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-light" type="submit">Filter</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td><a href="{{ route('vendor.orders.show', $tx->order_id) }}">{{ $tx->order?->order_number }}</a></td>
                                <td>{{ $tx->product_name }}</td>
                                <td>{{ $tx->quantity }}</td>
                                <td>${{ number_format($tx->line_total, 2) }}</td>
                                <td>
                                    @php($orderStatus = $tx->order?->status ?? 'pending')
                                    <span class="badge bg-{{ $orderStatus === 'delivered' ? 'success' : ($orderStatus === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($orderStatus) }}</span>
                                </td>
                                <td>{{ $tx->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $transactions->links('admin.vendor.pagination') }}</div>
    </div>
</div>
@endsection