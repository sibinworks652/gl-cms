<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $service->meta_title ?: $service->title . ' | ' . config('app.name') }}</title>
    @if($service->meta_description)
        <meta name="description" content="{{ $service->meta_description }}">
    @endif
    <link rel="canonical" href="{{ route('services.show', $service->slug) }}">
    @if($service->image_url)
        <meta property="og:image" content="{{ $service->image_url }}">
    @endif
    <style>
        :root { color-scheme: light; --ink:#111827; --muted:#64748b; --line:#e5e7eb; --accent:#f15a24; --bg:#f7f8fb; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: var(--ink); background: var(--bg); line-height: 1.7; }
        a { color: inherit; text-decoration: none; }
        .service-detail { max-width: 1120px; margin: 0 auto; padding: 56px 20px; }
        .service-hero { background: #111827; color: #fff; border-radius: 8px; overflow: hidden; margin-bottom: 34px; }
        .service-hero-inner { display: grid; grid-template-columns: 1.1fr .9fr; gap: 28px; align-items: center; padding: 42px; }
        .service-kicker { color: #fdba74; font-weight: 700; margin-bottom: 10px; }
        .service-hero h1 { margin: 0 0 14px; font-size: 44px; line-height: 1.12; }
        .service-hero p { margin: 0 0 22px; color: #d1d5db; }
        .service-hero img { width: 100%; height: 340px; object-fit: cover; border-radius: 8px; }
        .service-cta { display: inline-flex; align-items: center; justify-content: center; background: var(--accent); color: #fff; border-radius: 8px; padding: 12px 18px; font-weight: 700; }
        .service-content { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 34px; }
        .service-content img { max-width: 100%; height: auto; }
        .related-services { margin-top: 36px; }
        .related-services h2 { margin: 0 0 18px; font-size: 26px; }
        .services-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .service-card { background: #fff; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; min-width: 0; }
        .service-card-media img { width: 100%; height: 180px; object-fit: cover; display: block; }
        .service-card-body { padding: 22px; }
        .service-card-icon { color: var(--accent); font-size: 34px; margin-bottom: 12px; }
        .service-card-icon svg { width: 34px; height: 34px; }
        .service-card-category { color: var(--muted); font-size: 13px; margin-bottom: 6px; }
        .service-card h3 { margin: 0 0 10px; font-size: 22px; line-height: 1.25; }
        .service-card p { margin: 0 0 16px; color: var(--muted); }
        .service-card-link { color: var(--accent); font-weight: 700; }
        @media (max-width: 900px) { .service-hero-inner { grid-template-columns: 1fr; } .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .service-detail { padding: 32px 16px; } .service-hero-inner { padding: 28px 20px; } .service-hero h1 { font-size: 32px; } .services-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="service-detail">
        <section class="service-hero">
            <div class="service-hero-inner">
                <div>
                    <div class="service-kicker">{{ $service->category?->name ?? 'Service' }}</div>
                    <h1>{{ $service->title }}</h1>
                    <p>{{ $service->short_description }}</p>
                    @if($service->cta_label && $service->cta_url)
                        <a href="{{ $service->cta_url }}" class="service-cta">{{ $service->cta_label }}</a>
                    @endif
                </div>
                @if($service->image_url)
                    <img src="{{ $service->image_url }}" alt="{{ $service->title }}">
                @endif
            </div>
        </section>

        <article class="service-content">
            {!! $service->full_description !!}
        </article>

        @php($related = $relatedServices->getCollection()->reject(fn ($relatedService) => $relatedService->id === $service->id)->take(3))
        @if($related->isNotEmpty())
            <section class="related-services">
                <h2>Related Services</h2>
                <div class="services-grid">
                    @foreach($related as $service)
                        @include('services::partials.card', ['service' => $service])
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</body>
</html>
