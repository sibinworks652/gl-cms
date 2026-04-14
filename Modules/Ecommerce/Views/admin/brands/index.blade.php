@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Brands</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.brands.create') }}" class="btn btn-primary">
                        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Add Brand
                    </a>
                </div>
            </div>
        </div>
    </div>

     <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td>
                                    @if($brand->logo_url)
                                        <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                    @else
                                        <span class="text-muted">No Logo</span>
                                    @endif
                                </td>
                                <td><strong>{{ $brand->name }}</strong></td>
                                <td>{{ $brand->slug }}</td>
                                <td>{{ $brand->products()->count() }}</td>
                                <td>
                                    <span class="badge bg-{{ $brand->status ? 'success' : 'secondary' }}">
                                        {{ $brand->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.ecommerce.brands.edit', $brand) }}" class="btn btn-soft-warning btn-sm">
                                     <iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon>
                                    </a>
                                    <form method="POST" action="{{ route('admin.ecommerce.brands.destroy', $brand) }}" class="d-inline" data-confirm="Delete this Brand?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" >
                                            <iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $brands->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
