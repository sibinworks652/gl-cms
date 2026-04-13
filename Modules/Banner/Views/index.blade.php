@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card" id="banner-order-panel" data-reorder-url="{{ route('admin.banners.reorder') }}" data-csrf-token="{{ csrf_token() }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Banner Slides</h4>
                            <p class="text-muted mb-0">Manage homepage sliders, hero banners, schedule, and drag ordering.</p>
                            <div id="banner-sort-status" class="small mt-1"></div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($adminUser?->can('banners.create'))
                                <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">Create Slide</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="banner-sort-list" class="row g-3">
                            @forelse ($slides as $slide)
                                <div class="col-xl-4 sortable-slide" draggable="true" data-id="{{ $slide->id }}">
                                    <div class="card border h-100 mb-0">
                                        <div class="card-body">
                                            <div class="d-flex gap-3">
                                                <button type="button" class="btn btn-light btn-sm align-self-start" title="Drag to reorder">&#8942;&#8942;</button>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                        <div>
                                                            <h5 class="mb-1">{{ $slide->title }}</h5>
                                                            <div class="text-muted small">{{ ucfirst($slide->media_type) }} | {{ ucfirst($slide->button_link_type) }}</div>
                                                        </div>
                                                        <span class="badge {{ $slide->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                            {{ $slide->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </div>
                                                    @if($slide->subtitle)
                                                        <p class="mb-2 fw-medium">{{ $slide->subtitle }}</p>
                                                    @endif
                                                    <p class="text-muted mb-2">{{ \Illuminate\Support\Str::limit($slide->description, 110) }}</p>
                                                    <div class="small text-muted mb-3">
                                                        Schedule:
                                                        {{ $slide->starts_at?->format('d M Y H:i') ?? 'Immediate' }}
                                                        to
                                                        {{ $slide->ends_at?->format('d M Y H:i') ?? 'No end date' }}
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        @if($adminUser?->can('banners.view'))
                                                            <a href="{{ route('admin.banners.show', $slide) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-outline" width="16" height="16" /></a>
                                                        @endif
                                                        @if($adminUser?->can('banners.update'))
                                                            <a href="{{ route('admin.banners.edit', $slide) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="20" height="20" /></a>
                                                        @endif
                                                        @if($adminUser?->can('banners.delete'))
                                                            <form method="POST" action="{{ route('admin.banners.destroy', $slide) }}" data-confirm="Delete this banner slide?">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 text-muted">No banner slides found.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $slides->links('admin.vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('banner-sort-list');
    const panel = document.getElementById('banner-order-panel');
    const status = document.getElementById('banner-sort-status');
    if (!list || !panel) return;

    let draggedCard = null;
    list.querySelectorAll('.sortable-slide').forEach((card) => {
        card.addEventListener('dragstart', () => {
            draggedCard = card;
            card.classList.add('opacity-50');
        });

        card.addEventListener('dragend', () => {
            if (draggedCard) {
                saveOrder();
            }

            draggedCard = null;
            card.classList.remove('opacity-50');
        });

        card.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (!draggedCard || draggedCard === card) return;

            const rect = card.getBoundingClientRect();
            const insertBefore = (event.clientY - rect.top) < (rect.height / 2);
            list.insertBefore(draggedCard, insertBefore ? card : card.nextSibling);
        });
    });

    function currentOrder() {
        return Array.from(list.querySelectorAll('.sortable-slide')).map((card) => Number(card.dataset.id));
    }

    async function saveOrder() {
        status.textContent = 'Saving order...';
        const csrfToken = panel.dataset.csrfToken || '';
        const payload = JSON.stringify({
            order: currentOrder(),
        });

        try {
            const response = await fetch(panel.dataset.reorderUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: payload,
            });

            if (!response.ok) {
                throw new Error('Unable to save order.');
            }

            const contentType = response.headers.get('content-type') || '';
            const result = contentType.includes('application/json')
                ? await response.json()
                : null;

            status.textContent = result?.message || 'Banner order saved.';
            status.classList.remove('text-danger');
            status.classList.add('text-success');
        } catch (error) {
            status.textContent = 'Saving failed. Please refresh and try again.';
            status.classList.remove('text-success');
            status.classList.add('text-danger');
            
        }
    }
})();
</script>
@endpush
