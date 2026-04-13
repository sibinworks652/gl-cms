@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Role' : 'Create Role' }}</h4>
                        <p class="text-muted mb-0">Assign permissions to this admin role.</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ $isEdit ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
                            @csrf
                            @if ($isEdit)
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Role Name</label>
                                <input type="text" name="name" class="form-control @error('name') error-input-bottom @enderror" value="{{ old('name', $role->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label d-block">Permissions</label>
                                @php($selectedPermissionValues = (array) old('permissions', $selectedPermissions))

                                <div class="row g-3">
                                    @forelse ($permissionGroups as $groupKey => $group)
                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                                    <h6 class="mb-0">{{ $group['label'] }}</h6>
                                                    <span class="badge bg-light text-dark">{{ count($group['permissions']) }} permissions</span>
                                                </div>

                                                <div class="row g-2">
                                                    @foreach ($group['permissions'] as $permission)
                                                        <div class="col-md-3 col-sm-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission['name'] }}" id="permission-{{ $permission['id'] }}" {{ in_array($permission['name'], $selectedPermissionValues, true) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="permission-{{ $permission['id'] }}">{{ $permission['action'] }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted">No permissions available. Create permissions first.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Role' : 'Create Role' }}</button>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
