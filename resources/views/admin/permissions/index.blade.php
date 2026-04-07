@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Permission List</h4>
                            <p class="text-muted mb-0">Manage admin permissions.</p>
                        </div>
                        @if($adminUser?->can('permissions.create'))
                            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">Create Permission</a>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Guard</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->id }}</td>

                                        <td>{{ $permission->name }}</td>
                                        <td>{{ $permission->guard_name }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                @if($adminUser?->can('permissions.update'))
                                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></a>
                                                @endif
                                                @if($adminUser?->can('permissions.delete'))
                                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Delete this permission?');">
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
                                        <td colspan="3" class="text-center py-4 text-muted">No permissions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        {{ $permissions->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
