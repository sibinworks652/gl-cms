@forelse($logs as $log)
    <article class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div class="d-flex gap-3">
                    <div class="rounded-circle bg-{{ $log->badgeClass() }}-subtle text-{{ $log->badgeClass() }} d-inline-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px;">
                        {{ strtoupper(substr($log->admin?->name ?? 'System', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $log->description }}</div>
                        <div class="small text-muted mt-1">
                            {{-- {{ $log->admin?->name ?? 'System' }} --}}
                            {{ $log->admin?->getRoleNames()->first() ?? 'No Role' }}
                            @if($log->module)
                                <span class="mx-1">•</span>{{ str($log->module)->replace('-', ' ')->headline() }}
                            @endif
                            @if($log->record_id)
                                <span class="mx-1">•</span>#{{ $log->record_id }}
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            {{ $log->created_at?->diffForHumans() }}
                            @if($log->ip_address)
                                <span class="mx-1">•</span>{{ $log->ip_address }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $log->badgeClass() }}-subtle text-{{ $log->badgeClass() }}">{{ $log->actionLabel() }}</span>
                </div>
            </div>
        </div>
    </article>
@empty
    <div class="text-center text-muted py-5">No activity logged yet.</div>
@endforelse
