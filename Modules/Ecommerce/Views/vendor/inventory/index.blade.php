@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Inventory Management</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by SKU or product">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="low" @selected(request('status') === 'low')>Low Stock</option>
                        <option value="out" @selected(request('status') === 'out')>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-light" type="submit">Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Available</th>
                            <th>Low Stock At</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventory as $item)
                            <tr class="{{ $item->isLowStock() ? 'table-warning' : '' }} {{ $item->isOutOfStock() ? 'table-danger' : '' }}">
                                <td><code>{{ $item->sku }}</code></td>
                                <td>{{ $item->product?->name }}</td>
                                <td>{{ $item->variant?->label ?? '-' }}</td>
                                <td>{{ $item->available_quantity }}</td>
                                <td>{{ $item->low_stock_threshold }}</td>
                                <td>
                                    @if($item->isOutOfStock())
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item->isLowStock())
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('vendor.inventory.show', $item) }}" class="btn btn-soft-primary btn-sm">
                                        <iconify-icon icon="solar:eye-linear" width="16" height="16"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No inventory records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $inventory->links('admin.vendor.pagination') }}</div>
    </div>
</div>
@endsection