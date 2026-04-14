<div id="activity-log-feed" data-feed-url="{{ route('admin.activity-logs.feed', request()->query()) }}">
<div class="row">
     <div class="col-12">
         <h5 class="card-title mb-3">Activity Logs </h5>
         @forelse($groupedLogs as $date => $dayLogs)
         <div class="d-flex flex-row fs-18 align-items-center mb-3">
             <h5 class="mb-0">
                 @if($date == date('Y-m-d'))
                 Today
                 @elseif($date == date('Y-m-d', strtotime('-1 day')))
                 Yesterday
                 @else
                 {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}
                 @endif
             </h5>
         </div>

         <ul class="list-unstyled left-timeline">
             @foreach($dayLogs as $log)
             <li class="left-timeline-list">
                 <div class="card d-inline-block">
                     <div class="card-body">
                         <h5 class="mt-0 fs-16">
                             {{ $log->description }}
                             <span class="badge bg-{{ $log->badgeClass() }} ms-1">
                                 {{ $log->actionLabel() }}
                             </span>
                         </h5>
                         <p class="text-muted mb-0">
                             {{ $log->admin?->getRoleNames()->first() ?? 'System' }}
                             @if($log->module)
                             <span class="mx-1">•</span>{{ str($log->module)->headline() }}
                             @endif
                             @if($log->record_id)
                             <span class="mx-1">•</span>#{{ $log->record_id }}
                             @endif
                             <span class="mx-1">•</span>{{ $log->created_at?->diffForHumans() }}
                         </p>
                     </div>
                 </div>
             </li>
             @endforeach
         </ul>
         @empty
         <p class="text-muted text-center">No activity logs found.</p>
         @endforelse
     </div>
 </div>
</div>
@push('scripts')
<script>
(() => {
    const feed = document.getElementById('activity-log-feed');
    if (!feed) return;

    async function refreshFeed() {
        try {
            const response = await fetch(feed.dataset.feedUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;
            const result = await response.json();
            if (typeof result.html === 'string') {
                feed.innerHTML = result.html;
            }
        } catch (error) {
        }
    }

    window.setInterval(refreshFeed, 15000);
})();
</script>
@endpush
