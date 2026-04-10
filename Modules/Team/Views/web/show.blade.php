@extends('team::web.layout')

@php
    $pageTitle = $member->meta_title ?: ($member->name . ' | ' . ($siteName ?? config('app.name')) . ' Team');
    $pageDescription = $member->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($member->short_bio ?: $member->description), 155);
@endphp

@section('content')
    <section class="team-hero">
        <div class="team-shell">
            <div class="team-hero__panel">
                <span class="team-kicker">{{ $member->department?->name ?? 'Team Member' }}</span>
                <h1>{{ $member->name }}</h1>
                <p class="team-subtitle">{{ $member->designation }}</p>
                @if($member->short_bio)
                    <p class="team-copy">{{ $member->short_bio }}</p>
                @endif
                <div class="team-actions" style="margin-top: 20px;">
                    <a href="{{ route('team.index') }}" class="team-btn">Back to Team</a>
                    @if($member->email)
                        <a href="mailto:{{ $member->email }}" class="team-btn--ghost">Email</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="team-shell">
            <div class="team-detail">
                <div class="team-detail-grid">
                    <aside class="team-aside">
                        @if($member->image_url)
                            <img src="{{ $member->image_url }}" alt="{{ $member->name }}" style="width:100%;aspect-ratio:1 / 1;object-fit:cover;border-radius:24px;margin-bottom:20px;">
                        @else
                            <div class="team-card__media" style="aspect-ratio:1 / 1;border-radius:24px;margin-bottom:20px;">
                                <div class="team-avatar-fallback">{{ $member->initials }}</div>
                            </div>
                        @endif
                        <div class="team-list">
                            <div><strong>Name</strong><div class="team-meta">{{ $member->name }}</div></div>
                            <div><strong>Designation</strong><div class="team-meta">{{ $member->designation }}</div></div>
                            @if($member->department?->name)
                                <div><strong>Department</strong><div class="team-meta">{{ $member->department->name }}</div></div>
                            @endif
                            @if($member->email)
                                <div><strong>Email</strong><div class="team-meta">{{ $member->email }}</div></div>
                            @endif
                            @if($member->phone)
                                <div><strong>Phone</strong><div class="team-meta">{{ $member->phone }}</div></div>
                            @endif
                        </div>
                        @php($socials = collect(['facebook' => 'Facebook', 'twitter' => 'Twitter', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'website' => 'Website'])->filter(fn ($label, $key) => $member->socialLink($key)))
                        @if($socials->isNotEmpty())
                            <div class="team-socials" style="margin-top: 20px;">
                                @foreach($socials as $key => $label)
                                    <a href="{{ $member->socialLink($key) }}" class="team-btn--ghost" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                                @endforeach
                            </div>
                        @endif
                    </aside>

                    <article>
                        <div class="team-badges" style="margin-bottom: 14px;">
                            @if($member->is_featured)
                                <span class="team-badge team-badge--featured">Featured Member</span>
                            @endif
                            @if($member->department?->name)
                                <span class="team-badge">{{ $member->department->name }}</span>
                            @endif
                        </div>
                        <h2>About {{ $member->name }}</h2>
                        @if($member->description)
                            <div class="team-copy">{!! nl2br(e($member->description)) !!}</div>
                        @elseif($member->short_bio)
                            <p class="team-copy">{{ $member->short_bio }}</p>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </section>

    @if($relatedMembers->isNotEmpty())
        <section class="team-section">
            <div class="team-shell">
                <h2>More Team Members</h2>
                <div class="team-grid" style="margin-top: 18px;">
                    @foreach($relatedMembers as $related)
                        <article class="team-card">
                            <div class="team-card__media">
                                @if($related->image_url)
                                    <img src="{{ $related->image_url }}" alt="{{ $related->name }}">
                                @else
                                    <div class="team-avatar-fallback">{{ $related->initials }}</div>
                                @endif
                            </div>
                            <div class="team-card__body">
                                <h3>{{ $related->name }}</h3>
                                <div class="team-meta">{{ $related->designation }}</div>
                                <p class="team-copy">{{ \Illuminate\Support\Str::limit($related->short_bio, 110) }}</p>
                                <a href="{{ route('team.show', $related->slug) }}" class="team-btn--ghost">View Profile</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
