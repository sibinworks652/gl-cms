<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services | {{ config('app.name') }}</title>
    <meta name="description" content="Explore our services and find the right solution for your business.">
    <link rel="canonical" href="{{ route('services.index') }}">
    <style>
        :root { color-scheme: light; --ink:#111827; --muted:#64748b; --line:#e5e7eb; --accent:#f15a24; --bg:#f7f8fb; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: var(--ink); background: var(--bg); line-height: 1.6; }
        a { color: inherit; text-decoration: none; }
        .services-page { max-width: 1180px; margin: 0 auto; padding: 56px 20px; }
        .services-hero { background: #111827; color: #fff; border-radius: 8px; padding: 48px 32px; margin-bottom: 28px; }
        .services-hero h1 { margin: 0 0 12px; font-size: 42px; line-height: 1.15; }
        .services-hero p { margin: 0; max-width: 720px; color: #d1d5db; }
        .service-filters { display: flex; gap: 10px; flex-wrap: wrap; margin: 0 0 28px; }
        .service-filter { border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; background: #fff; color: var(--muted); }
        .service-filter.active { border-color: var(--accent); color: var(--accent); }
        .services-featured { margin: 0 0 36px; }
        .services-section-title { margin: 0 0 18px; font-size: 24px; }
        .services-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .service-card { background: #fff; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; min-width: 0; }
        .service-card-media img { width: 100%; height: 190px; object-fit: cover; display: block; }
        .service-card-body { padding: 22px; }
        .service-card-icon { color: var(--accent); font-size: 34px; margin-bottom: 12px; }
        .service-card-icon svg { width: 34px; height: 34px; }
        .service-card-category { color: var(--muted); font-size: 13px; margin-bottom: 6px; }
        .service-card h3 { margin: 0 0 10px; font-size: 22px; line-height: 1.25; }
        .service-card p { margin: 0 0 16px; color: var(--muted); }
        .service-card-link { color: var(--accent); font-weight: 700; }
        .empty-services { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 32px; text-align: center; color: var(--muted); }
        @media (max-width: 900px) { .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .services-page { padding: 32px 16px; } .services-hero { padding: 32px 22px; } .services-hero h1 { font-size: 32px; } .services-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="services-page">
        <section class="services-hero">
            <h1>Services</h1>
            <p>Focused digital services for better websites, sharper products, and smoother business systems.</p>
        </section>

        @if($categories->isNotEmpty())
            <nav class="service-filters" aria-label="Service categories">
                <a href="{{ route('services.index') }}" class="service-filter {{ !$activeCategory ? 'active' : '' }}">All</a>
                @foreach($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}" class="service-filter {{ $activeCategory === $category->slug ? 'active' : '' }}">{{ $category->name }}</a>
                @endforeach
            </nav>
        @endif

        @if($featuredServices->isNotEmpty() && !$activeCategory)
            <section class="services-featured">
                <h2 class="services-section-title">Featured Services</h2>
                <div class="services-grid">
                    @foreach($featuredServices as $service)
                        @include('services::partials.card', ['service' => $service])
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <h2 class="services-section-title">All Services</h2>
            @if($services->isNotEmpty())
                <div class="services-grid">
                    @foreach($services as $service)
                        @include('services::partials.card', ['service' => $service])
                    @endforeach
                </div>
                <div style="margin-top: 28px;">
                    {{ $services->links() }}
                </div>
            @else
                <div class="empty-services">No active services found.</div>
            @endif
        </section>
    </main>
</body>
</html>
