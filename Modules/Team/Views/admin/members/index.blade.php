@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <div class="card" id="team-members-order-panel" data-reorder-url="{{ route('admin.team-members.reorder') }}" data-csrf-token="{{ csrf_token() }}">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Team Members</h4>
                    <p class="text-muted mb-0">Manage leadership, staff, and featured profiles with drag-and-drop ordering.</p>
                    <div id="team-members-sort-status" class="small mt-1"></div>
                </div>
                <div class="d-flex gap-2">
                    @if($adminUser?->can('team-departments.view'))
                        <a href="{{ route('admin.team-departments.index') }}" class="btn btn-outline-primary btn-sm">Departments</a>
                    @endif
                    @if($adminUser?->can('team-members.create'))
                        <a href="{{ route('admin.team-members.create') }}" class="btn btn-primary btn-sm">Add Member</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <select name="department" class="form-select">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) request('department') === (string) $department->id)>{{ $department->name }}</option>
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

                <div id="team-members-sort-list" class="row g-3">
                    @forelse($members as $member)
                        <div class="col-xl-3 col-md-6 col-lg-4 col-sm-6 sortable-team-member" draggable="true" data-id="{{ $member->id }}">
                            <div class="card border h-100 mb-0 overflow-hidden">
                                @if($member->image_url)
                                    <img src="{{ $member->image_url }}" alt="{{ $member->name }}" class="card-img-top" style="aspect-ratio: 1 / 1; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="aspect-ratio: 1 / 1;">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold" style="width:84px;height:84px;font-size:1.25rem;">
                                            {{ $member->initials }}
                                        </div>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <div class="d-flex gap-3">
                                        <button type="button" class="btn btn-light btn-sm align-self-start" title="Drag to reorder">&#8942;&#8942;</button>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <h5 class="mb-1">{{ $member->name }}</h5>
                                                    <div class="text-muted small">{{ $member->designation }}</div>
                                                </div>
                                                <span class="badge {{ $member->status ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $member->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="small text-muted mb-2">
                                                {{ $member->department?->name ?? 'No department' }}
                                                @if($member->is_featured)
                                                    <span class="badge bg-warning-subtle text-warning ms-1">Featured</span>
                                                @endif
                                            </div>
                                            <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit($member->short_bio, 90) }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                                        @if($adminUser?->can('team-members.update'))
                                            <a href="{{ route('admin.team-members.edit', $member) }}" class="btn btn-soft-warning btn-sm"><iconify-icon icon="solar:pen-new-square-line-duotone" width="18" height="18"></iconify-icon></a>
                                        @endif
                                        @if($adminUser?->can('team-members.delete'))
                                            <form method="POST" action="{{ route('admin.team-members.destroy', $member) }}" data-confirm="Delete this team member?">
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
                            <div class="text-center py-5 text-muted">No team members found.</div>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer">
                {{ $members->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const list = document.getElementById('team-members-sort-list');
    const panel = document.getElementById('team-members-order-panel');
    const status = document.getElementById('team-members-sort-status');
    if (!list || !panel) return;
    let draggedCard = null;
    list.querySelectorAll('.sortable-team-member').forEach((card) => {
        card.addEventListener('dragstart', () => { draggedCard = card; card.classList.add('opacity-50'); });
        card.addEventListener('dragend', () => { if (draggedCard) saveOrder(); draggedCard = null; card.classList.remove('opacity-50'); });
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
                body: JSON.stringify({ order: Array.from(list.querySelectorAll('.sortable-team-member')).map((card) => Number(card.dataset.id)) }),
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
