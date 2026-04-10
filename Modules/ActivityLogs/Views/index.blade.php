@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Activity Timeline</h4>
                <p class="text-muted mb-0">Track create, update, delete, login, and logout events across the CMS.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search description or record">
                    </div>
                    <div class="col-md-3">
                        <select name="action" class="form-select">
                            <option value="">All actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="module" class="form-select">
                            <option value="">All modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected(request('module') === $module)>{{ str($module)->replace('-', ' ')->headline() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-light" type="submit">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="activity-log-feed" data-feed-url="{{ route('admin.activity-logs.feed', request()->query()) }}">
            @include('activity-logs::partials.timeline-items', ['groupedLogs' => $groupedLogs])
        </div>

        <div class="mt-3">
            {{ $logs->links('admin.vendor.pagination') }}
        </div>
    </div>
@endsection

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
