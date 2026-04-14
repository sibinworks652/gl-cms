@extends('admin.layouts.app')

@section('content')
<div class="container-xxl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Discounts</h4>
            <p class="text-muted mb-0">Manage product-specific and category-based offers.</p>
        </div>
        <a href="{{ route('admin.ecommerce.discounts.create') }}" class="btn btn-primary">Add Discount</a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Window</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $discount->name }}</div>
                                <div class="small text-muted">
                                    {{ $discount->products->count() }} products, {{ $discount->categories->count() }} categories
                                </div>
                            </td>
                            <td class="text-capitalize">{{ $discount->type }}</td>
                            <td>{{ $discount->badge_text }}</td>
                            <td>{{ $discount->start_date?->format('d M Y') ?? 'Now' }} - {{ $discount->end_date?->format('d M Y') ?? 'Open' }}</td>
                            <td>{{ $discount->used_count }} / {{ $discount->usage_limit ?? 'Unlimited' }}</td>
                            <td><span class="badge {{ $discount->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $discount->status ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.ecommerce.discounts.edit', $discount) }}" class="btn btn-soft-warning btn-sm">
                                     <iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon></a>
                                <form method="POST" action="{{ route('admin.ecommerce.discounts.destroy', $discount) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-soft-danger" type="submit">
                                            <iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon></button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No discounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $discounts->links('admin.vendor.pagination') }}</div>
    </div>
</div>
@endsection
