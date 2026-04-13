@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="testimonials-order-panel" data-reorder-url="{{ route('admin.testimonials.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Testimonials</h4>
                    <p class="text-muted mb-0">Manage customer reviews, highlight featured feedback, and drag items into the right order.</p>
                    <div id="testimonials-sort-status" class="small mt-1"></div>
                </div>
                @if($adminUser?->can('testimonials.create'))
                    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">Add Testimonial</a>
                @endif
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search client, company, designation, project">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Any status</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="featured" class="form-select">
                            <option value="">All testimonials</option>
                            <option value="featured" @selected(request('featured') === 'featured')>Featured only</option>
                            <option value="regular" @selected(request('featured') === 'regular')>Regular only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-light" type="submit">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 56px;">Order</th>
                                <th>Client</th>
                                <th>Rating</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="testimonials-sort-list">
                            @forelse($testimonials as $testimonial)
                                <tr class="sortable-testimonial" draggable="true" data-id="{{ $testimonial->id }}">
                                    <td><button type="button" class="btn btn-light btn-sm" title="Drag to reorder">&#8942;&#8942;</button></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($testimonial->image_url)
                                                <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="rounded-circle object-fit-cover" width="54" height="54">
                                            @else
                                                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width:54px;height:54px;">
                                                    {{ $testimonial->initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $testimonial->name }}</div>
                                                <div class="small text-muted">{{ collect([$testimonial->designation, $testimonial->company])->filter()->implode(' at ') ?: 'Client testimonial' }}</div>
                                                <div class="small text-muted">/{{ $testimonial->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-warning" aria-label="{{ $testimonial->rating }} star rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= $testimonial->rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $testimonial->project_name ?: 'General' }}</div>
                                        <div class="small text-muted">{{ $testimonial->location ?: 'No location' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <span class="badge {{ $testimonial->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ $testimonial->status ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($testimonial->is_featured)
                                                <span class="badge bg-warning-subtle text-warning">Featured</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('testimonials.update'))
                                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('testimonials.delete'))
                                                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" data-confirm="Delete this testimonial?">
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
                                    <td colspan="6" class="text-center text-muted py-5">No testimonials found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $testimonials->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('testimonials-sort-list');
    const panel = document.getElementById('testimonials-order-panel');
    const status = document.getElementById('testimonials-sort-status');
    if (!list || !panel) return;

    let draggedRow = null;
    list.querySelectorAll('.sortable-testimonial').forEach((row) => {
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
                    order: Array.from(list.querySelectorAll('.sortable-testimonial')).map((row) => Number(row.dataset.id)),
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
