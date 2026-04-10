@extends('services::frontend.layout')

@php
    $pageTitle = $service->meta_title ?: ($service->title . ' | ' . config('app.name') . ' Services');
    $pageDescription = $service->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($service->short_description ?: $service->full_description), 155);
@endphp

@section('content')
    <section class="svc-hero">
        <div class="svc-shell">
            <div class="svc-panel">
                @if($service->category?->name)
                    <span class="svc-kicker">{{ $service->category->name }}</span>
                @endif
                <h1>{{ $service->title }}</h1>
                @if($service->short_description)
                    <p class="svc-copy">{{ $service->short_description }}</p>
                @endif
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:20px;">
                    <a href="{{ route('services.index') }}" class="svc-btn">Back to Services</a>
                    @if($service->cta_label && $service->cta_url)
                        <a href="{{ $service->cta_url }}" class="svc-btn--ghost">{{ $service->cta_label }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="svc-section">
        <div class="svc-shell">
            <div class="svc-card svc-detail">
                <div class="svc-detail-grid">
                    <aside class="svc-aside">
                        @if($service->image_url)
                            <img src="{{ $service->image_url }}" alt="{{ $service->title }}" style="width:100%;aspect-ratio:1 / 1;object-fit:cover;border-radius:22px;margin-bottom:20px;">
                        @endif
                        <div class="svc-copy">
                            <strong>Slug</strong>
                            <div>{{ $service->slug }}</div>
                        </div>
                        @if($service->category?->name)
                            <div class="svc-copy" style="margin-top:16px;">
                                <strong>Category</strong>
                                <div>{{ $service->category->name }}</div>
                            </div>
                        @endif
                    </aside>
                    <article>
                        <h2>Service Overview</h2>
                        @if($service->full_description)
                            <div class="svc-copy">{!! $service->full_description !!}</div>
                        @elseif($service->short_description)
                            <p class="svc-copy">{{ $service->short_description }}</p>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </section>

    @if($relatedServices->count())
        <section class="svc-section">
            <div class="svc-shell">
                <h2>Related Services</h2>
                <div class="svc-grid" style="margin-top:18px;">
                    @foreach($relatedServices as $related)
                        <article class="svc-card">
                            <div class="svc-card__media">
                                @if($related->image_url)
                                    <img src="{{ $related->image_url }}" alt="{{ $related->title }}">
                                @else
                                    <div class="svc-pill">{{ $related->title }}</div>
                                @endif
                            </div>
                            <div class="svc-card__body">
                                <h3>{{ $related->title }}</h3>
                                <p class="svc-copy">{{ \Illuminate\Support\Str::limit($related->short_description, 120) }}</p>
                                <a href="{{ route('services.show', $related->slug) }}" class="svc-btn--ghost">View Service</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
