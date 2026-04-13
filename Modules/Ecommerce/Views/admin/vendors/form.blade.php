@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.ecommerce.vendors.update', $vendor) : route('admin.ecommerce.vendors.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Vendor' : 'Create Vendor' }}</h4>
                    <p class="text-muted mb-0">Assign vendors to users and configure seller details.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.ecommerce.vendors.index') }}" class="btn btn-light">Back</a>
                    <button class="btn btn-primary" type="submit">Save Vendor</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vendor Name</label>
                        <input name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input name="slug" class="form-control" value="{{ old('slug', $vendor->slug) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Linked User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('user_id', $vendor->user_id) === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input name="email" class="form-control" value="{{ old('email', $vendor->email) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Commission %</label>
                        <input name="commission_rate" type="number" step="0.01" class="form-control" value="{{ old('commission_rate', $vendor->commission_rate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Logo</label>
                        <input name="logo" type="file" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description', $vendor->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" value="1" id="vendor-status" @checked(old('status', $vendor->status))>
                            <label class="form-check-label" for="vendor-status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
