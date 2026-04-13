@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Role List</h4>
                            <p class="text-muted mb-0">Manage admin roles and role permissions.</p>
                        </div>
                        @if($adminUser?->can('roles.create'))
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">Create Role</a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>

                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Permissions</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @forelse ($role->permissions->sortBy('name')->groupBy(fn ($permission) => \Illuminate\Support\Str::before($permission->name, '.')) as $groupKey => $groupPermissions)
                                                {{-- <div class="mb-1"> --}}
                                                    <span class="fw-normal">{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::singular($groupKey)) }}:</span>
                                                    @foreach ($groupPermissions as $permission)
                                                        <span class="badge bg-primary-subtle text-primary me-1">{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::after($permission->name, '.')) }}</span>
                                                    @endforeach
                                                {{-- </div> --}}
                                            @empty
                                                <span class="text-muted">No permissions</span>
                                            @endforelse
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                @if($adminUser?->can('roles.update'))
                                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                                                @endif
                                                @if($adminUser?->can('roles.delete'))
                                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" data-confirm="Delete this role?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No roles found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        {{ $roles->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
