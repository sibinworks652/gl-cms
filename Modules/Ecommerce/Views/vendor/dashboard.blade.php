@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vendor Dashboard</h4>
                <div class="page-title-right">
                    <span class="badge bg-soft-primary">{{ $vendor->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card overflow-hidden h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="avatar-md bg-soft-primary rounded">
                                <iconify-icon icon="mdi:package-variant-closed" class="avatar-title fs-32 text-primary"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-0">Total Products</p>
                            <h3 class="text-dark mt-1 mb-0">{{ $stats['total_products'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light bg-opacity-50">
                    <a href="{{ route('vendor.products.index') }}" class="text-reset fw-semibold fs-12">View Products</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card overflow-hidden h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="avatar-md bg-soft-success rounded">
                                <iconify-icon icon="mdi:cart-outline" class="avatar-title fs-32 text-success"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-0">Total Orders</p>
                            <h3 class="text-dark mt-1 mb-0">{{ $stats['total_orders'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light bg-opacity-50">
                    <a href="{{ route('vendor.orders.index') }}" class="text-reset fw-semibold fs-12">View Orders</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card overflow-hidden h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="avatar-md bg-soft-warning rounded">
                                <iconify-icon icon="mdi:currency-usd" class="avatar-title fs-32 text-warning"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-0">Total Earnings</p>
                            <h3 class="text-dark mt-1 mb-0">${{ number_format($stats['total_earnings'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light bg-opacity-50">
                    <a href="{{ route('vendor.earnings.index') }}" class="text-reset fw-semibold fs-12">View Earnings</a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card overflow-hidden h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="avatar-md bg-soft-danger rounded">
                                <iconify-icon icon="mdi:alert-circle-outline" class="avatar-title fs-32 text-danger"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-0">Low Stock</p>
                            <h3 class="text-dark mt-1 mb-0">{{ $stats['low_stock_alerts'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2 bg-light bg-opacity-50">
                    <a href="{{ route('vendor.inventory.index') }}?status=low" class="text-reset fw-semibold fs-12">View Inventory</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Products</h5>
                </div>
                <div class="card-body">
                    @if($products->isEmpty())
                        <p class="text-muted">No products yet.</p>
                        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">Add Your First Product</a>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <a href="{{ route('vendor.products.edit', $product) }}">{{ $product->name }}</a>
                                            </td>
                                            <td>{{ number_format($product->sale_price ?: $product->base_price, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $product->stock }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">Add Product</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if($orders->isEmpty())
                        <p class="text-muted">No orders yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $order) }}">{{ $order->order_number }}</a>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : ($order->status === 'processing' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($order->grand_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('vendor.orders.index') }}" class="btn btn-primary btn-sm">View All Orders</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('vendor.settings.profile') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="mdi:account-cog" class="me-1"></iconify-icon> Settings
                        </a>
                        <a href="{{ route('vendor.earnings.index') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="mdi:chart-line" class="me-1"></iconify-icon> Earnings Report
                        </a>
                        <a href="{{ route('vendor.inventory.index') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="mdi:warehouse" class="me-1"></iconify-icon> Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
