@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Earnings Overview</h4>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Gross Earnings (Grand Total)</p>
                    <h4 class="mb-0">${{ number_format($report['total_grand_total'] ?? ($report['total_sales'] ?? 0), 2) }}</h4>
                    <small class="text-muted">Subtotal: ${{ number_format($report['total_subtotal'] ?? 0, 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Commission Deducted ({{ $report['commission_rate'] ?? 0 }}%)</p>
                    <h4 class="mb-0 text-danger">-${{ number_format($report['commission_amount'] ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Net Earnings (After Commission)</p>
                    <h4 class="mb-0 text-success">${{ number_format($report['net_earnings'] ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Orders</p>
                    <h4 class="mb-0">{{ $report['order_count'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sales Report</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-light" type="submit">Filter</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Sales</th>
                                    <th>Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyEarnings as $month)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::create()->month($month->month)->format('F') }}</td>
                                        <td>${{ number_format($month->total, 2) }}</td>
                                        <td>-</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.earnings.transactions') }}" class="btn btn-outline-secondary">View Transactions</a>
                        <a href="{{ route('vendor.earnings.payouts') }}" class="btn btn-outline-secondary">Payouts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection