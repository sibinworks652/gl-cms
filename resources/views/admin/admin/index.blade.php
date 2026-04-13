@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Admin List</h4>
                            <p class="text-muted mb-0">Manage admin users and assign roles.</p>
                        </div>
                        @if($adminUser?->can('admins.create'))
                            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary btn-sm">Create Admin</a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admins as $admin)
                                    @if(Auth::user()->hasRole('Super Admin') || !$admin->hasRole('Super Admin'))
                                    <tr>
                                        <td>{{ $admin->id }}</td>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->username }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            @forelse ($admin->roles as $role)
                                                <span class="badge bg-info-subtle text-info me-1">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">No role</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if ($admin->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                @if($adminUser?->can('admins.update'))
                                                    <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                                                @endif

                                                @if($adminUser?->can('admins.delete') && $admin->role !== 'Super Admin')
                                                    <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" data-confirm="Delete this admin?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No admins found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        {{ $admins->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
