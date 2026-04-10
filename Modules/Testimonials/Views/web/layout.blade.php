<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? ($siteName ?? config('app.name')) }}</title>
    @if(!empty($pageDescription))
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    <style>
        :root {
            --t-bg: #f5f2ea;
            --t-surface: #fffdfa;
            --t-card: #ffffff;
            --t-ink: #162130;
            --t-soft: #5c6777;
            --t-line: #e7e0d2;
            --t-accent: #cb6d35;
            --t-accent-deep: #8e441e;
            --t-star: #f2a531;
            --t-shadow: 0 18px 40px rgba(33, 28, 21, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--t-ink);
            background:
                radial-gradient(circle at top right, rgba(203, 109, 53, 0.14), transparent 22%),
                linear-gradient(180deg, #fcfaf6 0%, var(--t-bg) 100%);
        }
        a { color: inherit; text-decoration: none; }
        .t-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        .t-topbar { padding: 24px 0; }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .gap-3 { gap: 1rem; }
        .fw-semibold { font-weight: 600; }
        .fw-medium { font-weight: 500; }
        .t-brand {
            display: inline-flex; align-items: center; gap: 12px; font-size: 0.95rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
        }
        .t-brand__mark {
            width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 14px; color: #fff; background: linear-gradient(135deg, var(--t-accent), #efb27f);
            box-shadow: 0 14px 28px rgba(203, 109, 53, 0.26);
        }
        .t-hero { padding: 18px 0 44px; }
        .t-hero__panel {
            padding: 34px; border-radius: 30px; border: 1px solid var(--t-line);
            background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(255,250,242,0.98)), var(--t-surface);
            box-shadow: var(--t-shadow);
        }
        .t-kicker {
            display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 999px;
            background: rgba(203, 109, 53, 0.12); color: var(--t-accent-deep); font-size: 0.8rem;
            font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
        }
        h1, h2, h3 { margin: 0 0 12px; line-height: 1.08; }
        h1 { font-size: clamp(2.5rem, 5vw, 4.5rem); max-width: 10ch; }
        .t-subtitle, .t-copy, .t-meta, .t-filter-note { color: var(--t-soft); line-height: 1.7; }
        .t-actions, .t-stars, .t-badges { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .t-btn, .t-btn--ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 18px;
            border-radius: 14px; border: 1px solid transparent; font-weight: 700; transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .t-btn { background: var(--t-accent); color: #fff; box-shadow: 0 16px 32px rgba(203, 109, 53, 0.22); }
        .t-btn--ghost { border-color: var(--t-line); background: rgba(255,255,255,0.7); }
        .t-btn:hover, .t-btn--ghost:hover { transform: translateY(-1px); }
        .t-section { padding-bottom: 40px; }
        .t-featured-strip {
            display: grid; grid-auto-flow: column; grid-auto-columns: minmax(280px, 340px); gap: 18px;
            overflow-x: auto; padding-bottom: 6px; scroll-snap-type: x proximity;
        }
        .t-card, .t-quote, .t-detail {
            background: var(--t-card); border: 1px solid var(--t-line); border-radius: 26px; box-shadow: var(--t-shadow);
        }
        .t-quote { padding: 24px; scroll-snap-align: start; }
        .t-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .t-card { position: relative; overflow: hidden; padding: 24px; }
        .t-card::before {
            content: ""; position: absolute; inset: 0 0 auto auto; width: 140px; height: 140px;
            background: radial-gradient(circle, rgba(203, 109, 53, 0.12), transparent 65%); transform: translate(28%, -28%);
        }
        .t-card > * { position: relative; }
        .t-avatar {
            width: 68px; height: 68px; border-radius: 22px; object-fit: cover;
            background: linear-gradient(135deg, #f4d2bf, #ecd4a4); display: inline-flex; align-items: center;
            justify-content: center; color: var(--t-accent-deep); font-size: 1.2rem; font-weight: 700; margin-bottom: 18px;
        }
        .t-stars { color: var(--t-star); font-size: 1rem; margin-bottom: 14px; }
        .t-badge {
            display: inline-flex; align-items: center; padding: 7px 11px; border-radius: 999px;
            background: #f5efe6; color: var(--t-ink); font-size: 0.8rem; font-weight: 700;
        }
        .t-badge--featured { background: rgba(203, 109, 53, 0.12); color: var(--t-accent-deep); }
        .t-filter { display: flex; flex-wrap: wrap; gap: 12px; margin: 18px 0 30px; }
        .t-filter a {
            padding: 10px 14px; border-radius: 999px; border: 1px solid var(--t-line);
            background: rgba(255,255,255,0.75); font-weight: 700; font-size: 0.9rem;
        }
        .t-filter a.is-active { background: var(--t-accent); color: #fff; border-color: var(--t-accent); }
        .t-detail { padding: 32px; }
        .t-detail-grid { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 24px; }
        .t-aside {
            padding: 24px; border-radius: 24px; border: 1px solid var(--t-line);
            background: linear-gradient(180deg, #fffaf3 0%, #ffffff 100%);
        }
        .t-list { display: grid; gap: 14px; }
        .t-empty { text-align: center; padding: 56px 24px; }
        @media (max-width: 991px) { .t-grid, .t-detail-grid { grid-template-columns: 1fr; } }
        @media (max-width: 700px) {
            .t-shell { width: min(100% - 20px, 1180px); }
            .t-hero__panel, .t-card, .t-quote, .t-detail { padding: 22px; border-radius: 22px; }
            .t-featured-strip { grid-auto-columns: minmax(250px, 88vw); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="t-topbar">
        <div class="t-shell">
            <a href="{{ route('testimonials.index') }}" class="t-brand">
                <span class="t-brand__mark">T</span>
                <span>{{ $siteName ?? config('app.name') }} Testimonials</span>
            </a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
