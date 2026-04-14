@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $isEdit ? 'Edit' : 'Create' }} Brand</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.brands.index') }}" class="btn btn-primary">
                        <iconify-icon icon="solar:arrow-left-outline" class="me-1"></iconify-icon> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.ecommerce.brands.update', $brand) : route('admin.ecommerce.brands.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Brand Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $brand->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description', $brand->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Logo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                   id="logo" name="logo" accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($brand?->logo_url)
                                <div class="mt-2">
                                    <img src="{{ $brand->logo_url }}" alt="Logo" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                   {{ old('status', $brand->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">{{ $isEdit ? 'Update' : 'Create' }} Brand</button>
            </div>
        </div>
    </form>
</div>
@endsection
