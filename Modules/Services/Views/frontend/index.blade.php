@extends('services::frontend.layout')

@php
    $pageTitle = config('app.name') . ' Services';
    $pageDescription = 'Explore our services, featured solutions, and category-based offerings.';
@endphp

@section('content')
    <section class="svc-hero">
        <div class="svc-shell">
            <div class="svc-panel">
                <span class="svc-kicker">What We Offer</span>
                <h1>Services built around real outcomes.</h1>
                <p class="svc-copy">Create service landing pages and individual service detail URLs like <code>/services/web-dev</code> and <code>/services/app-dev</code>.</p>
                @if($categories->isNotEmpty())
                    <div class="svc-filter">
                        <a href="{{ route('services.index') }}" class="{{ !$activeCategory ? 'is-active' : '' }}">All</a>
                        @foreach($categories as $category)
                            <a href="{{ route('services.index', ['category' => $category->slug]) }}" class="{{ $activeCategory === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if($featuredServices->isNotEmpty() && !$activeCategory)
        <section class="svc-section">
            <div class="svc-shell">
                <h2>Featured Services</h2>
                <div class="svc-grid" style="margin-top: 18px;">
                    @foreach($featuredServices as $service)
                        <article class="svc-card">
                            <div class="svc-card__media">
                                @if($service->image_url)
                                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}">
                                @else
                                    <div class="svc-pill">{{ $service->title }}</div>
                                @endif
                            </div>
                            <div class="svc-card__body">
                                @if($service->category?->name)
                                    <div class="svc-pill" style="margin-bottom: 12px;">{{ $service->category->name }}</div>
                                @endif
                                <h3>{{ $service->title }}</h3>
                                <p class="svc-copy">{{ \Illuminate\Support\Str::limit($service->short_description, 120) }}</p>
                                <a href="{{ route('services.show', $service->slug) }}" class="svc-btn--ghost">View Service</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="svc-section">
        <div class="svc-shell">
            <h2>All Services</h2>
            <div class="svc-grid" style="margin-top: 18px;">
                @forelse($services as $service)
                    <article class="svc-card">
                        <div class="svc-card__media">
                            @if($service->image_url)
                                <img src="{{ $service->image_url }}" alt="{{ $service->title }}">
                            @else
                                <div class="svc-pill">{{ $service->title }}</div>
                            @endif
                        </div>
                        <div class="svc-card__body">
                            @if($service->category?->name)
                                <div class="svc-pill" style="margin-bottom: 12px;">{{ $service->category->name }}</div>
                            @endif
                            <h3>{{ $service->title }}</h3>
                            <p class="svc-copy">{{ \Illuminate\Support\Str::limit($service->short_description, 140) }}</p>
                            <a href="{{ route('services.show', $service->slug) }}" class="svc-btn--ghost">View Service</a>
                        </div>
                    </article>
                @empty
                    <div class="svc-card svc-empty" style="grid-column: 1 / -1;">
                        <h3>No services found.</h3>
                        <p class="svc-copy">Create services in admin and they will appear here automatically.</p>
                    </div>
                @endforelse
            </div>
            <div style="margin-top: 28px;">{{ $services->links() }}</div>
        </div>
    </section>
@endsection
