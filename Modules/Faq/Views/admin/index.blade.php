@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="faq-order-panel" data-reorder-url="{{ route('admin.faqs.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">FAQs</h4>
                    <p class="text-muted mb-0">Manage common questions, publish answers, and fine-tune the frontend order.</p>
                    <div id="faq-sort-status" class="small mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    @if($adminUser?->can('faq-categories.view'))
                        <a href="{{ route('admin.faq-categories.index') }}" class="btn btn-outline-primary btn-sm">Categories</a>
                    @endif
                    @if($adminUser?->can('faqs.create'))
                        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">Add FAQ</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Any status</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-light" type="submit">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Order</th>
                                <th>Question</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="faq-sort-list">
                            @forelse($faqs as $faq)
                                <tr class="sortable-faq" draggable="true" data-id="{{ $faq->id }}">
                                    <td><button type="button" class="btn btn-light btn-sm" title="Drag to reorder">&#8942;&#8942;</button></td>
                                    <td>
                                        <div class="fw-semibold">{{ $faq->question }}</div>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($faq->answer), 100) }}</div>
                                    </td>
                                    <td>{{ $faq->category?->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        <span class="badge {{ $faq->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $faq->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('faqs.update'))
                                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                            @endif
                                            @if($adminUser?->can('faqs.delete'))
                                                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" data-confirm="Delete this FAQ?">
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
                                    <td colspan="5" class="text-center text-muted py-5">No FAQs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $faqs->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('faq-sort-list');
    const panel = document.getElementById('faq-order-panel');
    const status = document.getElementById('faq-sort-status');
    if (!list || !panel) return;
    let draggedRow = null;
    list.querySelectorAll('.sortable-faq').forEach((row) => {
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
                body: JSON.stringify({ order: Array.from(list.querySelectorAll('.sortable-faq')).map((row) => Number(row.dataset.id)) }),
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
