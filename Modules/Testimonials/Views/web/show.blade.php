@extends('testimonials::web.layout')

@php
    $pageTitle = $testimonial->meta_title ?: ($testimonial->name . ' Testimonial | ' . ($siteName ?? config('app.name')));
    $pageDescription = $testimonial->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($testimonial->content), 155);
@endphp

@section('content')
    <section class="t-hero">
        <div class="t-shell">
            <div class="t-hero__panel">
                <span class="t-kicker">Client Story</span>
                <h1>{{ $testimonial->name }}</h1>
                <p class="t-subtitle">{{ collect([$testimonial->designation, $testimonial->company, $testimonial->location])->filter()->implode(' • ') ?: 'Verified client testimonial' }}</p>
                <div class="t-stars" aria-label="{{ $testimonial->rating }} star rating" style="margin-top: 18px;">
                    @for($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= $testimonial->rating ? '★' : '☆' }}</span>
                    @endfor
                </div>
                <div class="t-actions" style="margin-top: 20px;">
                    <a href="{{ route('testimonials.index') }}" class="t-btn">View All Testimonials</a>
                    @if($testimonial->project_name)
                        <span class="t-btn--ghost">{{ $testimonial->project_name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="t-section">
        <div class="t-shell">
            <div class="t-detail">
                <div class="t-detail-grid">
                    <aside class="t-aside">
                        @if($testimonial->image_url)
                            <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="t-avatar" style="width: 100%; height: auto; aspect-ratio: 1 / 1; margin-bottom: 20px; border-radius: 28px;">
                        @else
                            <div class="t-avatar" style="width: 100%; height: 240px; margin-bottom: 20px; border-radius: 28px; font-size: 3rem;">{{ $testimonial->initials }}</div>
                        @endif

                        <div class="t-list">
                            <div>
                                <strong>Client</strong>
                                <div class="t-meta">{{ $testimonial->name }}</div>
                            </div>
                            @if($testimonial->company)
                                <div>
                                    <strong>Company</strong>
                                    <div class="t-meta">{{ $testimonial->company }}</div>
                                </div>
                            @endif
                            @if($testimonial->designation)
                                <div>
                                    <strong>Designation</strong>
                                    <div class="t-meta">{{ $testimonial->designation }}</div>
                                </div>
                            @endif
                            @if($testimonial->location)
                                <div>
                                    <strong>Location</strong>
                                    <div class="t-meta">{{ $testimonial->location }}</div>
                                </div>
                            @endif
                            @if($testimonial->project_name)
                                <div>
                                    <strong>Project / Service</strong>
                                    <div class="t-meta">{{ $testimonial->project_name }}</div>
                                </div>
                            @endif
                        </div>
                    </aside>

                    <article>
                        <div class="t-badges" style="margin-bottom: 14px;">
                            @if($testimonial->is_featured)
                                <span class="t-badge t-badge--featured">Featured Testimonial</span>
                            @endif
                            <span class="t-badge">{{ $testimonial->rating }}/5 Rating</span>
                        </div>
                        <h2>What {{ $testimonial->name }} shared</h2>
                        <p class="t-copy" style="font-size: 1.12rem;">“{{ $testimonial->content }}”</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    @if($relatedTestimonials->isNotEmpty())
        <section class="t-section">
            <div class="t-shell">
                <h2>Related Testimonials</h2>
                <div class="t-grid" style="margin-top: 18px;">
                    @foreach($relatedTestimonials as $related)
                        <article class="t-card">
                            @if($related->image_url)
                                <img src="{{ $related->image_url }}" alt="{{ $related->name }}" class="t-avatar">
                            @else
                                <div class="t-avatar">{{ $related->initials }}</div>
                            @endif
                            <div class="t-stars" aria-label="{{ $related->rating }} star rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $related->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <h3>{{ $related->name }}</h3>
                            <div class="t-meta">{{ collect([$related->designation, $related->company])->filter()->implode(' • ') }}</div>
                            <p class="t-copy">“{{ \Illuminate\Support\Str::limit($related->content, 140) }}”</p>
                            <div style="margin-top: 16px;">
                                <a href="{{ route('testimonials.show', $related->slug) }}" class="t-btn--ghost">Open Testimonial</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
