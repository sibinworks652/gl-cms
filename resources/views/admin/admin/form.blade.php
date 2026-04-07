@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Admin' : 'Create Admin' }}</h4>
                        <p class="text-muted mb-0">Fill admin details and assign roles.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $isEdit ? route('admin.admins.update', $admin) : route('admin.admins.store') }}">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $admin->username) }}">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password {{ $isEdit ? '(Leave blank to keep current password)' : '' }}</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Roles</label>
                                <div class="row">
                                    @forelse ($roles as $role)
                                        <div class="col-md-2 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role-{{ $role->id }}" {{ in_array($role->name, old('roles', $selectedRoles), true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted">No roles available. Create a role first.</div>
                                    @endforelse
                                </div>
                                @error('roles')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Admin' : 'Create Admin' }}</button>
                                <a href="{{ route('admin.admins.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
