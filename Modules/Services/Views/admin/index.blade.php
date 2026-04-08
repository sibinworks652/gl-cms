@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="services-order-panel" data-reorder-url="{{ route('admin.services.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Services</h4>
                    <p class="text-muted mb-0">Manage service pages, categories, SEO, featured flags, and drag ordering.</p>
                    <div id="services-sort-status" class="small mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    @if($adminUser?->can('service-categories.view'))
                        <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-primary btn-sm">Categories</a>
                    @endif
                    @if($adminUser?->can('services.create'))
                        <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">Create Service</a>
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

                <div id="services-sort-list" class="row g-3">
                    @forelse($services as $service)
                        <div class="col-xl-3 col-md-6 col-lg-4 col-sm-6 sortable-service" draggable="true" data-id="{{ $service->id }}">
                            <div class="card border h-100 mb-0 overflow-hidden" style="width: 100%;">
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}" class="card-img-top" style="object-fit:cover;">
                                    @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 100%;">
                                        <iconify-icon icon="solar:album-bold" width="48" height="48" style="color: #ced4da;"></iconify-icon>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <div class="d-flex gap-3">
                                        <button type="button" class="btn btn-light btn-sm align-self-start" title="Drag to reorder">&#8942;&#8942;</button>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <h5 class="mb-1">{{ $service->title }}</h5>
                                                    <div class="text-muted small">/{{ $service->slug }}</div>
                                                </div>
                                                <span class="badge {{ $service->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="small text-muted mb-2">
                                                {{ $service->category?->name ?? '' }}
                                                @if($service->is_featured)
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Featured</span>
                                                @endif
                                            </div>
                                            {{-- <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit($service->short_description, 130) }}</p> --}}

                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                                                {{-- <a href="{{ route('services.show', $service->slug) }}" target="_blank" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-outline" width="16" height="16"></iconify-icon></a> --}}
                                                @if($adminUser?->can('services.update'))
                                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                                @endif
                                                @if($adminUser?->can('services.delete'))
                                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16"></iconify-icon></button>
                                                    </form>
                                                @endif
                                            </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5 text-muted">No services found.</div>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer">
                {{ $services->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('services-sort-list');
    const panel = document.getElementById('services-order-panel');
    const status = document.getElementById('services-sort-status');
    if (!list || !panel) return;

    let draggedCard = null;
    list.querySelectorAll('.sortable-service').forEach((card) => {
        card.addEventListener('dragstart', () => {
            draggedCard = card;
            card.classList.add('opacity-50');
        });

        card.addEventListener('dragend', () => {
            if (draggedCard) saveOrder();
            draggedCard = null;
            card.classList.remove('opacity-50');
        });

        card.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!draggedCard || draggedCard === card) return;
            const rect = card.getBoundingClientRect();
            list.insertBefore(draggedCard, (event.clientY - rect.top) < (rect.height / 2) ? card : card.nextSibling);
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
                    order: Array.from(list.querySelectorAll('.sortable-service')).map((card) => Number(card.dataset.id)),
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
