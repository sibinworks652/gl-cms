@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Product Categories</h4>
                <p class="text-muted mb-0">Organize your catalog with reusable category groups.</p>
            </div>
            <a href="{{ route('admin.ecommerce.categories.create') }}" class="btn btn-primary">Add Category</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->parent?->name ?? 'Root' }}</td>
                                <td><span class="badge {{ $category->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.ecommerce.categories.edit', $category) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon></a>
                                    <form method="POST" action="{{ route('admin.ecommerce.categories.destroy', $category) }}" class="d-inline" data-confirm="Delete this category?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-soft-danger btn-sm" type="submit"><iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon></button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">No categories found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $categories->links('admin.vendor.pagination') }}</div>
        </div>
    </div>
@endsection
