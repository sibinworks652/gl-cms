@extends('team::web.layout')

@php
    $pageTitle = ($siteName ?? config('app.name')) . ' Team';
    $pageDescription = 'Meet the people behind our company, from leadership to specialists across every department.';
@endphp

@section('content')
    <section class="team-hero">
        <div class="team-shell">
            <div class="team-hero__panel">
                <span class="team-kicker">Our People</span>
                <h1>The team behind the work.</h1>
                <p class="team-subtitle">A reusable team directory for company pages, about pages, and department-driven talent showcases.</p>
                <div class="team-actions" style="margin-top: 20px;">
                    <a href="#team-grid" class="team-btn">Meet the Team</a>
                    @if($featuredMembers->isNotEmpty())
                        <a href="#featured-team" class="team-btn--ghost">Featured Members</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if($departments->isNotEmpty())
        <section class="team-section">
            <div class="team-shell">
                <div class="team-filter">
                    <a href="{{ route('team.index') }}" class="{{ !request('department') ? 'is-active' : '' }}">All</a>
                    @foreach($departments as $department)
                        <a href="{{ route('team.index', ['department' => $department->slug]) }}" class="{{ request('department') === $department->slug ? 'is-active' : '' }}">{{ $department->name }}</a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($featuredMembers->isNotEmpty() && !request('department'))
        <section class="team-section" id="featured-team">
            <div class="team-shell">
                <h2>Featured Team</h2>
                <div class="team-grid" style="margin-top: 18px;">
                    @foreach($featuredMembers as $member)
                        <article class="team-card">
                            <div class="team-card__media">
                                @if($member->image_url)
                                    <img src="{{ $member->image_url }}" alt="{{ $member->name }}">
                                @else
                                    <div class="team-avatar-fallback">{{ $member->initials }}</div>
                                @endif
                            </div>
                            <div class="team-card__body">
                                <div class="team-badges" style="margin-bottom: 12px;">
                                    <span class="team-badge team-badge--featured">Featured</span>
                                    @if($member->department?->name)
                                        <span class="team-badge">{{ $member->department->name }}</span>
                                    @endif
                                </div>
                                <h3>{{ $member->name }}</h3>
                                <div class="team-meta">{{ $member->designation }}</div>
                                <p class="team-copy">{{ \Illuminate\Support\Str::limit($member->short_bio, 120) }}</p>
                                <div style="margin-top: 16px;">
                                    <a href="{{ route('team.show', $member->slug) }}" class="team-btn--ghost">View Profile</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="team-section" id="team-grid">
        <div class="team-shell">
            <h2>All Team Members</h2>
            <div class="team-grid" style="margin-top: 18px;">
                @forelse($members as $member)
                    <article class="team-card">
                        <div class="team-card__media">
                            @if($member->image_url)
                                <img src="{{ $member->image_url }}" alt="{{ $member->name }}">
                            @else
                                <div class="team-avatar-fallback">{{ $member->initials }}</div>
                            @endif
                        </div>
                        <div class="team-card__body">
                            <div class="team-badges" style="margin-bottom: 12px;">
                                @if($member->is_featured)
                                    <span class="team-badge team-badge--featured">Featured</span>
                                @endif
                                @if($member->department?->name)
                                    <span class="team-badge">{{ $member->department->name }}</span>
                                @endif
                            </div>
                            <h3>{{ $member->name }}</h3>
                            <div class="team-meta">{{ $member->designation }}</div>
                            <p class="team-copy">{{ \Illuminate\Support\Str::limit($member->short_bio, 125) }}</p>
                            @php($socials = collect(['facebook' => 'f', 'twitter' => 'x', 'linkedin' => 'in', 'instagram' => 'ig', 'website' => 'web'])->filter(fn ($label, $key) => $member->socialLink($key)))
                            @if($socials->isNotEmpty())
                                <div class="team-socials" style="margin: 16px 0;">
                                    @foreach($socials as $key => $label)
                                        <a href="{{ $member->socialLink($key) }}" class="team-social" target="_blank" rel="noopener noreferrer">{{ strtoupper($label) }}</a>
                                    @endforeach
                                </div>
                            @endif
                            <a href="{{ route('team.show', $member->slug) }}" class="team-btn--ghost">View Profile</a>
                        </div>
                    </article>
                @empty
                    <div class="team-card team-empty" style="grid-column: 1 / -1;">
                        <h3>No team members available yet.</h3>
                        <p class="team-copy">Publish members from the admin panel and they will appear here automatically.</p>
                    </div>
                @endforelse
            </div>
            <div style="margin-top: 28px;">{{ $members->links() }}</div>
        </div>
    </section>
@endsection
