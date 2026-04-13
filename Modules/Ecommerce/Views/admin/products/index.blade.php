@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Products</h4>
                <p class="text-muted mb-0">Manage product catalog, variants, stock, and vendors.</p>
            </div>
            <a href="{{ route('admin.ecommerce.products.create') }}" class="btn btn-primary">Add Product</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by product or SKU">
                    </div>
                    <div class="col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-light" type="submit">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <div class="small text-muted">{{ $product->sku }}</div>
                                </td>
                                <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                <td>{{ $product->vendor?->name ?? 'Marketplace' }}</td>
                                <td>{{ number_format((float) $product->sale_price ?: (float) $product->base_price, 2) }}</td>
                                <td>{{ $product->variants->sum('stock') ?: $product->stock }}</td>
                                <td><span class="badge {{ $product->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.ecommerce.products.edit', $product) }}" class="btn btn-soft-warning btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.ecommerce.products.destroy', $product) }}" class="d-inline" data-confirm="Delete this product?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-soft-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No products found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $products->links('admin.vendor.pagination') }}</div>
        </div>
    </div>
@endsection
