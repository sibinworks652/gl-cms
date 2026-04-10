@extends('testimonials::web.layout')

@php
    $pageTitle = ($siteName ?? config('app.name')) . ' Testimonials';
    $pageDescription = 'Read client reviews, success stories, and featured testimonials from our customers.';
@endphp

@section('content')
    <section class="t-hero">
        <div class="t-shell">
            <div class="t-hero__panel">
                <span class="t-kicker">Customer Voices</span>
                <h1>Proof that the work lands.</h1>
                <p class="t-subtitle">A curated wall of client feedback, project outcomes, and trusted partnerships. Use featured testimonials for your homepage and explore the full archive below.</p>
                <div class="t-actions" style="margin-top: 20px;">
                    <a href="#all-testimonials" class="t-btn">Browse Testimonials</a>
                    <a href="{{ route('testimonials.index', ['featured' => 1]) }}" class="t-btn--ghost">Featured Only</a>
                </div>
            </div>
        </div>
    </section>

    @if($featuredTestimonials->isNotEmpty())
        <section class="t-section">
            <div class="t-shell">
                <div style="margin-bottom: 18px;">
                    <h2>Featured Testimonials</h2>
                    <p class="t-filter-note">High-impact reviews ready for hero sections, trust strips, and marketing pages.</p>
                </div>
                <div class="t-featured-strip">
                    @foreach($featuredTestimonials as $featured)
                        <article class="t-quote">
                            <div class="t-stars" aria-label="{{ $featured->rating }} star rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $featured->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p class="t-copy" style="font-size: 1.05rem;">“{{ \Illuminate\Support\Str::limit($featured->content, 180) }}”</p>
                            <div class="d-flex align-items-center gap-3" style="margin-top: 20px;">
                                @if($featured->image_url)
                                    <img src="{{ $featured->image_url }}" alt="{{ $featured->name }}" class="t-avatar" style="margin-bottom: 0;">
                                @else
                                    <div class="t-avatar" style="margin-bottom: 0;">{{ $featured->initials }}</div>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $featured->name }}</div>
                                    <div class="t-meta">{{ collect([$featured->designation, $featured->company])->filter()->implode(', ') }}</div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="t-section" id="all-testimonials">
        <div class="t-shell">
            <div>
                <h2>All Testimonials</h2>
                <p class="t-filter-note">Reusable social proof cards for landing pages, service pages, and review archives.</p>
            </div>

            <div class="t-filter">
                <a href="{{ route('testimonials.index') }}" class="{{ request('featured') !== '1' ? 'is-active' : '' }}">All</a>
                <a href="{{ route('testimonials.index', ['featured' => 1]) }}" class="{{ request('featured') === '1' ? 'is-active' : '' }}">Featured</a>
            </div>

            <div class="t-grid">
                @forelse($testimonials as $testimonial)
                    <article class="t-card">
                        @if($testimonial->image_url)
                            <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="t-avatar">
                        @else
                            <div class="t-avatar">{{ $testimonial->initials }}</div>
                        @endif

                        <div class="t-stars" aria-label="{{ $testimonial->rating }} star rating">
                            @for($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= $testimonial->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>

                        <div class="t-badges" style="margin-bottom: 14px;">
                            @if($testimonial->is_featured)
                                <span class="t-badge t-badge--featured">Featured</span>
                            @endif
                            @if($testimonial->project_name)
                                <span class="t-badge">{{ $testimonial->project_name }}</span>
                            @endif
                        </div>

                        <h3>{{ $testimonial->name }}</h3>
                        <div class="t-meta" style="margin-bottom: 12px;">{{ collect([$testimonial->designation, $testimonial->company, $testimonial->location])->filter()->implode(' • ') }}</div>
                        <p class="t-copy">“{{ \Illuminate\Support\Str::limit($testimonial->content, 170) }}”</p>
                        <div style="margin-top: 18px;">
                            <a href="{{ route('testimonials.show', $testimonial->slug) }}" class="t-btn--ghost">Read Full Testimonial</a>
                        </div>
                    </article>
                @empty
                    <div class="t-card t-empty" style="grid-column: 1 / -1;">
                        <h3>No testimonials available yet.</h3>
                        <p class="t-copy">Once testimonials are published from the admin panel, they will appear here automatically.</p>
                    </div>
                @endforelse
            </div>

            <div style="margin-top: 28px;">
                {{ $testimonials->links() }}
            </div>
        </div>
    </section>
@endsection
