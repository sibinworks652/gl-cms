@extends('careers::web.layout')

@php
    $pageTitle = $job->meta_title ?: ($job->title . ' | ' . $siteName . ' Careers');
    $pageDescription = $job->meta_description ?: $job->short_description;
@endphp

@section('content')
    <section class="career-hero">
        <div class="career-shell">
            <div class="career-hero__panel">
                <div class="career-pills" style="margin-bottom: 16px;">
                    @if($job->is_featured)
                        <span class="career-kicker">Featured</span>
                    @endif
                    <span class="career-pill">{{ $job->category?->name ?? 'General' }}</span>
                    <span class="career-pill">{{ \Modules\Careers\Models\Job::jobTypes()[$job->job_type] ?? $job->job_type }}</span>
                </div>
                <h1 style="max-width: 13ch;">{{ $job->title }}</h1>
                <p class="career-subtitle">{{ $job->short_description }}</p>
                <div class="career-stats">
                    <div class="career-stat">Location<strong>{{ $job->location }}</strong></div>
                    <div class="career-stat">Experience<strong>{{ $job->experience }}</strong></div>
                    <div class="career-stat">Vacancies<strong>{{ $job->vacancies }}</strong></div>
                    <div class="career-stat">Salary<strong>{{ $job->salary ?: 'Open' }}</strong></div>
                </div>
                <div class="career-actions">
                    <a href="{{ route('careers.apply.show', $job->slug) }}" class="career-btn">Apply for This Role</a>
                    <a href="{{ route('careers.index') }}" class="career-btn--ghost">View All Jobs</a>
                </div>
            </div>
        </div>
    </section>

    <section class="career-shell career-grid career-grid--sidebar">
        <aside class="career-sidebar">
            <h3>Role Snapshot</h3>
            <div class="career-list">
                <div><strong>Category</strong><div class="career-meta">{{ $job->category?->name ?? 'General' }}</div></div>
                <div><strong>Job Type</strong><div class="career-meta">{{ \Modules\Careers\Models\Job::jobTypes()[$job->job_type] ?? $job->job_type }}</div></div>
                <div><strong>Location</strong><div class="career-meta">{{ $job->location }}</div></div>
                <div><strong>Apply Before</strong><div class="career-meta">{{ $job->expiry_date?->format('d M Y') ?? 'No expiry date' }}</div></div>
            </div>
        </aside>

        <div class="career-list">
            <article class="career-card">
                <h2>About This Role</h2>
                <div class="career-richtext">{!! $job->description !!}</div>
            </article>

            @if($job->skills)
                <article class="career-card">
                    <h3>Skills</h3>
                    <div class="career-pills">
                        @foreach(collect(preg_split('/[\r\n,]+/', $job->skills))->map(fn ($item) => trim((string) $item))->filter() as $skill)
                            <span class="career-pill">{{ $skill }}</span>
                        @endforeach
                    </div>
                </article>
            @endif

            @if($job->requirements)
                <article class="career-card">
                    <h3>Requirements</h3>
                    <div class="career-copy" style="white-space: pre-line;">{{ $job->requirements }}</div>
                </article>
            @endif

            @if($job->responsibilities)
                <article class="career-card">
                    <h3>Responsibilities</h3>
                    <div class="career-copy" style="white-space: pre-line;">{{ $job->responsibilities }}</div>
                </article>
            @endif

            @if($job->benefits)
                <article class="career-card">
                    <h3>Benefits</h3>
                    <div class="career-copy" style="white-space: pre-line;">{{ $job->benefits }}</div>
                </article>
            @endif

            <article class="career-card">
                <h3>Ready to Apply?</h3>
                <p class="career-copy">Submit your details, resume, and optional cover letter. We will email you once your application is received.</p>
                <a href="{{ route('careers.apply.show', $job->slug) }}" class="career-btn">Apply Now</a>
            </article>
        </div>
    </section>
@endsection
