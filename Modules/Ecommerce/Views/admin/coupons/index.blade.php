@extends('admin.layouts.app')

@section('content')
<div class="container-xxl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Coupons</h4>
            <p class="text-muted mb-0">Manage checkout coupon codes and usage limits.</p>
        </div>
        <a href="{{ route('admin.ecommerce.coupons.create') }}" class="btn btn-primary">Add Coupon</a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Window</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->name }}</td>
                            <td><code>{{ $coupon->code }}</code></td>
                            <td class="text-capitalize">{{ $coupon->type }}</td>
                            <td>{{ $coupon->type === 'percentage' ? rtrim(rtrim((string) $coupon->value, '0'), '.') . '%' : '₹' . number_format((float) $coupon->value, 2) }}</td>
                            <td>{{ $coupon->start_date?->format('d M Y') ?? 'Now' }} - {{ $coupon->end_date?->format('d M Y') ?? 'Open' }}</td>
                            <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? 'Unlimited' }}</td>
                            <td><span class="badge {{ $coupon->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ $coupon->status ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.ecommerce.coupons.edit', $coupon) }}" class="btn btn-soft-warning btn-sm">
                                     <iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon></a>
                                <form method="POST" action="{{ route('admin.ecommerce.coupons.destroy', $coupon) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-soft-danger btn-sm" type="submit"> <iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon></button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">No coupons found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $coupons->links('admin.vendor.pagination') }}</div>
    </div>
</div>
@endsection
