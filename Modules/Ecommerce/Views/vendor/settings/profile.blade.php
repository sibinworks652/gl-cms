@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Profile Settings</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('vendor.settings.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $vendor->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            @if($vendor->logo)
                                <img src="{{ $vendor->logo_url }}" alt="Logo" class="mt-2" style="max-width: 150px;">
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Store Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $vendor->status === 'approved' ? 'success' : ($vendor->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($vendor->status) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Commission Rate:</span>
                        <span>{{ $vendor->commission_rate ?? 0 }}%</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Store URL:</span>
                        <code>{{ $vendor->slug }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection