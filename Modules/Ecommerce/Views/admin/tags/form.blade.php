@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $isEdit ? 'Edit' : 'Create' }} Tag</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.tags.index') }}" class="btn btn-primary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-1"></iconify-icon> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.ecommerce.tags.update', $tag) : route('admin.ecommerce.tags.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tag Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $tag->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">e.g., New Arrival, Best Seller, Sale</small>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="general" {{ old('type', $tag->type ?? '') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="trending" {{ old('type', $tag->type ?? '') == 'trending' ? 'selected' : '' }}>Trending</option>
                                <option value="sale" {{ old('type', $tag->type ?? '') == 'sale' ? 'selected' : '' }}>Sale</option>
                                <option value="new" {{ old('type', $tag->type ?? '') == 'new' ? 'selected' : '' }}>New</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                   {{ old('status', $tag->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">{{ $isEdit ? 'Update' : 'Create' }} Tag</button>
            </div>
        </div>
    </form>
</div>
@endsection
