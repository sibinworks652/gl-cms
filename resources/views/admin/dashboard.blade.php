@extends('admin.layouts.app')

@section('content')
@use('App\Support\ModuleRegistry')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(($dashboardCards ?? collect())->isNotEmpty())
                <div class="row">
                    @foreach($dashboardCards as $card)
                        <div class="col-sm-6 col-xl-3 mb-3">
                            <div class="card overflow-hidden h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <div class="avatar-md {{ $card['iconWrapperClass'] ?? 'bg-soft-primary' }} rounded">
                                                <iconify-icon icon="{{ $card['icon'] }}" class="avatar-title fs-32 {{ $card['iconClass'] ?? 'text-primary' }}"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <p class="text-muted mb-0 text-truncate">{{ $card['title'] }}</p>
                                            <h3 class="text-dark mt-1 mb-0">{{ $card['value'] }}</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer py-2 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="text-truncate">
                                            <span class="{{ $card['metaClass'] ?? 'text-success' }}">{{ $card['metaValue'] }}</span>
                                            <span class="text-muted ms-1 fs-12">{{ $card['metaLabel'] }}</span>
                                        </div>
                                        <a href="{{ $card['link'] }}" class="text-reset fw-semibold fs-12 text-nowrap">{{ $card['linkLabel'] }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Dashboard Overview</h5>
                        <p class="text-muted mb-0">No functional dashboard cards are available yet for your current enabled modules and permissions.</p>
                    </div>
                </div>
            @endif

            @if(ModuleRegistry::enabled('activity_logs'))
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Recent Activity</h5>
                            <p class="text-muted mb-0">Latest admin actions across the CMS.</p>
                        </div>
                        @if(auth('admin')->user()?->can('activity-logs.view') || auth('admin')->user()?->can('activity-logs.view-own'))
                            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light btn-sm">View Timeline</a>
                        @endif
                    </div>
                    <div class="card-body custom-scrollbar" style="max-height:450px; overflow-y: auto; overscroll-behavior: contain;">
                        @include('activity-logs::partials.timeline-items', ['logs' => $recentActivities ?? collect()])
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
