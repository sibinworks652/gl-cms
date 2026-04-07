@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="service-categories-order-panel" data-reorder-url="{{ route('admin.service-categories.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Service Categories</h4>
                    <p class="text-muted mb-0">Group services and control frontend category filters.</p>
                    <div id="service-categories-sort-status" class="small mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-light btn-sm">Services</a>
                    @if($adminUser?->can('service-categories.create'))
                        <a href="{{ route('admin.service-categories.create') }}" class="btn btn-primary btn-sm">Create Category</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div id="service-categories-sort-list" class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">Order</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Services</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="sortable-service-category" draggable="true" data-id="{{ $category->id }}">
                                    <td><button type="button" class="btn btn-light btn-sm" title="Drag to reorder">&#8942;&#8942;</button></td>
                                    <td>
                                        <div class="fw-semibold">{{ $category->name }}</div>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($category->description, 80) }}</div>
                                    </td>
                                    <td>{{ $category->slug }}</td>
                                    <td>{{ $category->services_count }}</td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('service-categories.update'))
                                                <a href="{{ route('admin.service-categories.edit', $category) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('service-categories.delete'))
                                                <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Services will stay but become uncategorized.');">
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
                                    <td colspan="6" class="text-center text-muted py-5">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $categories->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.querySelector('#service-categories-sort-list tbody');
    const panel = document.getElementById('service-categories-order-panel');
    const status = document.getElementById('service-categories-sort-status');
    if (!list || !panel) return;

    let draggedRow = null;
    list.querySelectorAll('.sortable-service-category').forEach((row) => {
        row.addEventListener('dragstart', () => {
            draggedRow = row;
            row.classList.add('opacity-50');
        });

        row.addEventListener('dragend', () => {
            if (draggedRow) saveOrder();
            draggedRow = null;
            row.classList.remove('opacity-50');
        });

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
                body: JSON.stringify({
                    order: Array.from(list.querySelectorAll('.sortable-service-category')).map((row) => Number(row.dataset.id)),
                }),
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
