@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="team-departments-order-panel" data-reorder-url="{{ route('admin.team-departments.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Team Departments</h4>
                    <p class="text-muted mb-0">Group team members for frontend filtering and internal organization.</p>
                    <div id="team-departments-sort-status" class="small mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.team-members.index') }}" class="btn btn-light btn-sm">Members</a>
                    @if($adminUser?->can('team-departments.create'))
                        <a href="{{ route('admin.team-departments.create') }}" class="btn btn-primary btn-sm">Create Department</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div id="team-departments-sort-list" class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">Order</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Members</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                                <tr class="sortable-team-department" draggable="true" data-id="{{ $department->id }}">
                                    <td><button type="button" class="btn btn-light btn-sm" title="Drag to reorder">&#8942;&#8942;</button></td>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ $department->slug }}</td>
                                    <td>{{ $department->members_count }}</td>
                                    <td>
                                        <span class="badge {{ $department->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $department->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('team-departments.update'))
                                                <a href="{{ route('admin.team-departments.edit', $department) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('team-departments.delete'))
                                                <form method="POST" action="{{ route('admin.team-departments.destroy', $department) }}" onsubmit="return confirm('Delete this department? Members will remain but become uncategorized.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16"></iconify-icon></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">No departments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $departments->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.querySelector('#team-departments-sort-list tbody');
    const panel = document.getElementById('team-departments-order-panel');
    const status = document.getElementById('team-departments-sort-status');
    if (!list || !panel) return;
    let draggedRow = null;
    list.querySelectorAll('.sortable-team-department').forEach((row) => {
        row.addEventListener('dragstart', () => { draggedRow = row; row.classList.add('opacity-50'); });
        row.addEventListener('dragend', () => { if (draggedRow) saveOrder(); draggedRow = null; row.classList.remove('opacity-50'); });
        row.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!draggedRow || draggedRow === row) return;
            const rect = row.getBoundingClientRect();
            list.insertBefore(draggedRow, (event.clientY - rect.top) < (rect.height / 2) ? row : row.nextSibling);
        });
    });
    async function saveOrder() {
        status.textContent = 'Saving order...';
        try {
            const response = await fetch(panel.dataset.reorderUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': panel.dataset.csrfToken || '',
                },
                body: JSON.stringify({ order: Array.from(list.querySelectorAll('.sortable-team-department')).map((row) => Number(row.dataset.id)) }),
            });
            if (!response.ok) throw new Error('Unable to save order.');
            const result = await response.json();
            status.textContent = result.message || 'Order saved.';
            status.className = 'small mt-1 text-success';
        } catch (error) {
            status.textContent = 'Saving failed. Please refresh and try again.';
            status.className = 'small mt-1 text-danger';
        }
    }
})();
</script>
@endpush
