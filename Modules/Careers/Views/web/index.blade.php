@extends('careers::web.layout')

@php
    $pageTitle = 'Careers at ' . $siteName;
    $pageDescription = 'Explore open roles, filter by team and location, and apply online.';
@endphp

@section('content')
    <section class="career-hero">
        <div class="career-shell">
            <div class="career-hero__panel">
                <span class="career-eyebrow">We are hiring</span>
                <div class="career-hero__copy">
                    <h1>Build meaningful work with a team that ships.</h1>
                    <p class="career-subtitle">Explore open roles across engineering, design, marketing, and operations. Search, filter, and apply in minutes.</p>
                </div>
                <div class="career-stats">
                    <div class="career-stat">
                        Open Roles
                        <strong>{{ $jobs->total() }}</strong>
                    </div>
                    <div class="career-stat">
                        Categories
                        <strong>{{ count($categories) }}</strong>
                    </div>
                    <div class="career-stat">
                        Locations
                        <strong>{{ count($locations) }}</strong>
                    </div>
                    <div class="career-stat">
                        Featured
                        <strong>{{ $featuredJobs->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="career-shell career-grid career-grid--sidebar">
        <aside class="career-sidebar">
            <h3>Find Your Fit</h3>
            <p class="career-copy">Filter opportunities by team, job type, and location.</p>
            <form method="GET" class="career-list">
                <div class="career-field">
                    <label for="career-search">Search</label>
                    <input id="career-search" class="career-input" type="text" name="search" value="{{ request('search') }}" placeholder="Developer, designer, HR">
                </div>
                <div class="career-field">
                    <label for="career-category">Category</label>
                    <select id="career-category" class="career-select" name="category">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="career-field">
                    <label for="career-location">Location</label>
                    <select id="career-location" class="career-select" name="location">
                        <option value="">All locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="career-field">
                    <label for="career-job-type">Job Type</label>
                    <select id="career-job-type" class="career-select" name="job_type">
                        <option value="">All types</option>
                        @foreach($jobTypes as $value => $label)
                            <option value="{{ $value }}" @selected(request('job_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="career-actions">
                    <button class="career-btn" type="submit">Apply Filters</button>
                    <a href="{{ route('careers.index') }}" class="career-btn--ghost">Reset</a>
                </div>
            </form>
        </aside>

        <div class="career-list">
            @forelse($jobs as $job)
                <article class="career-card career-job-card">
                    <div class="career-actions" style="justify-content: space-between; margin-top: 0;">
                        <div class="career-pills">
                            @if($job->is_featured)
                                <span class="career-kicker">Featured</span>
                            @endif
                            <span class="career-pill">{{ $jobTypes[$job->job_type] ?? $job->job_type }}</span>
                        </div>
                        <span class="career-meta">{{ $job->expiry_date?->format('d M Y') ? 'Apply by ' . $job->expiry_date->format('d M Y') : 'Open now' }}</span>
                    </div>
                    <h2 style="margin-top: 18px;">{{ $job->title }}</h2>
                    <div class="career-meta-row career-meta">
                        <span>{{ $job->category?->name ?? 'General' }}</span>
                        <span>{{ $job->location }}</span>
                        <span>{{ $job->experience }}</span>
                        <span>{{ $job->vacancies }} openings</span>
                    </div>
                    <p>{{ $job->short_description }}</p>
                    <div class="career-actions">
                        <a href="{{ route('careers.show', $job->slug) }}" class="career-btn">View Details</a>
                        <a href="{{ route('careers.apply.show', $job->slug) }}" class="career-btn--light">Apply Now</a>
                    </div>
                </article>
            @empty
                <div class="career-card career-empty">
                    <h3>No matching roles right now</h3>
                    <p class="career-copy">Try adjusting the filters or check back later for fresh openings.</p>
                    <a href="{{ route('careers.index') }}" class="career-btn--ghost">Clear Filters</a>
                </div>
            @endforelse

            @if($jobs->hasPages())
                <div class="career-card">
                    {{ $jobs->links('admin.vendor.pagination') }}
                </div>
            @endif
        </div>
    </section>
@endsection
