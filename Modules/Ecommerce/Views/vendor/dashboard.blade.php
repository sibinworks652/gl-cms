@extends('admin.layouts.app')
    {{-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Vendor Dashboard - {{ $vendor->name }}</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('vendor.dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('vendor.products.index') }}">Products</a>
                <a class="nav-link" href="{{ route('vendor.orders.index') }}">Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </div>
        </div>
    </nav> --}}

    {{-- <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2 class="mb-4">Dashboard</h2>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text display-4">{{ $stats['total_products'] }}</p>
                        <a href="{{ route('vendor.products.index') }}" class="btn btn-sm btn-primary">View Products</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text display-4">{{ $stats['total_orders'] }}</p>
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-primary">View Orders</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Orders</h5>
                        <p class="card-text display-4">{{ $stats['pending_orders'] }}</p>
                        <a href="{{ route('vendor.orders.index') }}?status=pending" class="btn btn-sm btn-primary">View Pending</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Products</h5>
                    </div>
                    <div class="card-body">
                        @if($products->isEmpty())
                            <p class="text-muted">No products yet.</p>
                        @else
                            <table class="table table-sm">
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
                                            <td>{{ $product->stock }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                        <a href="{{ route('vendor.products.create') }}" class="btn btn-sm btn-success">Add Product</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        @if($orders->isEmpty())
                            <p class="text-muted">No orders yet.</p>
                        @else
                            <table class="table table-sm">
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
                                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($order->grand_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-primary">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
{{-- </body>
</html> --}}
