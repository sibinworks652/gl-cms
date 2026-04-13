@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Permission' : 'Create Permission' }}</h4>
                        <p class="text-muted mb-0">Create permissions for the admin guard.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $isEdit ? route('admin.permissions.update', $permission) : route('admin.permissions.store') }}">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif

                            <div class="mb-4">
                                <label class="form-label">Permission Name</label>
                                <input type="text" name="name" class="form-control @error('name') error-input-bottom @enderror" value="{{ old('name', $permission->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Permission' : 'Create Permission' }}</button>
                                <a href="{{ route('admin.permissions.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
