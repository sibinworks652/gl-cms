@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Dynamic Menus</h4>
                            <p class="text-muted mb-0">Manage header, footer, and sidebar navigation with nested items.</p>
                        </div>
                        @if($adminUser?->can('menus.create'))
                            <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">Create Menu</a>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($menus as $menu)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $menu->name }}</div>
                                                <div class="text-muted small">{{ $menu->slug }}</div>
                                            </td>
                                            <td>{{ $locations[$menu->location] ?? ucfirst($menu->location) }}</td>
                                            <td>{{ $menu->items_count }}</td>
                                            <td>
                                                <span class="badge {{ $menu->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    @if($adminUser?->can('menus.view'))
                                                        <a href="{{ route('admin.menus.view', $menu) }}" class="btn btn-soft-success btn-sm"><iconify-icon icon="solar:eye-broken" width="20" height="20" /></a>
                                                    @endif
                                                    @if($adminUser?->can('menus.update'))
                                                        <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="20" height="20" /></a>
                                                    @endif
                                                    @if($adminUser?->can('menus.delete'))
                                                        <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" data-confirm="Delete this menu and all menu items?">
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
                                            <td colspan="5" class="text-center text-muted py-5">No menus found yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        {{ $menus->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
