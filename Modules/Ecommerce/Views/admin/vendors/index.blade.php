@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Vendors</h4>
                <p class="text-muted mb-0">Manage sellers for your multi-vendor marketplace.</p>
            </div>
            <a href="{{ route('admin.ecommerce.vendors.create') }}" class="btn btn-primary">Add Vendor</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Vendor</th>
                            <th>Contact</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>
                                    <div>{{ $vendor->name }}</div>
                                    @if($vendor->user)
                                        <div class="small text-muted">User: {{ $vendor->user->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $vendor->email ?: 'No email' }}</div>
                                    <div class="small text-muted">{{ $vendor->phone ?: 'No phone' }}</div>
                                </td>
                                <td>{{ $vendor->products_count }}</td>
                                <td>
                                    @if($vendor->status === 'pending')
                                        <span class="badge bg-warning text-white">Pending</span>
                                    @elseif($vendor->status === 'approved')
                                        <span class="badge bg-success text-white">Approved</span>
                                    @elseif($vendor->status === 'rejected')
                                        <span class="badge bg-danger text-white">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $vendor->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                    @if($vendor->status === 'pending')
                                        <form method="POST" action="{{ route('admin.ecommerce.vendors.approve', $vendor) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-soft-success btn-sm"><iconify-icon icon="solar:check-circle-linear" width="16" height="16"></iconify-icon></button>
                                        </form>
                                        <button type="button" class="btn btn-soft-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $vendor->id }}"><iconify-icon icon="solar:close-circle-linear" width="16" height="16"></iconify-icon></button>
                                    @endif
                                    <a href="{{ route('admin.ecommerce.vendors.edit', $vendor) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon></a>
                                    <form method="POST" action="{{ route('admin.ecommerce.vendors.destroy', $vendor) }}" class="d-inline" data-confirm="Delete this vendor?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-soft-danger btn-sm" type="submit"><iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon></button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @if($vendor->status === 'pending')
                                <div class="modal fade" id="rejectModal{{ $vendor->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.ecommerce.vendors.reject', $vendor) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Vendor</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="reason" class="form-label">Reason for rejection</label>
                                                        <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No vendors found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $vendors->links('admin.vendor.pagination') }}</div>
        </div>
    </div>
@endsection
