<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? config('app.name') }}</title>
    @if(!empty($pageDescription))
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    <style>
        :root {
            --svc-bg: #f3f0ea;
            --svc-card: #ffffff;
            --svc-ink: #1e293b;
            --svc-soft: #64748b;
            --svc-line: #e2ddd4;
            --svc-accent: #b86132;
            --svc-shadow: 0 18px 38px rgba(30, 41, 59, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--svc-ink);
            background:
                radial-gradient(circle at top left, rgba(184, 97, 50, 0.12), transparent 24%),
                linear-gradient(180deg, #faf8f4 0%, var(--svc-bg) 100%);
        }
        a { color: inherit; text-decoration: none; }
        .svc-shell { width: min(1160px, calc(100% - 32px)); margin: 0 auto; }
        .svc-topbar { padding: 24px 0; }
        .svc-brand { display: inline-flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .svc-brand__mark {
            width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--svc-accent), #efb28f); color: #fff;
        }
        .svc-hero { padding: 18px 0 42px; }
        .svc-panel, .svc-card {
            background: var(--svc-card); border: 1px solid var(--svc-line); border-radius: 26px; box-shadow: var(--svc-shadow);
        }
        .svc-panel { padding: 34px; }
        .svc-kicker {
            display: inline-flex; padding: 8px 14px; border-radius: 999px; background: rgba(184, 97, 50, 0.12);
            color: var(--svc-accent); font-size: 0.82rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
        }
        h1, h2, h3 { margin: 0 0 12px; line-height: 1.08; }
        h1 { font-size: clamp(2.4rem, 5vw, 4.2rem); max-width: 11ch; }
        .svc-copy, .svc-meta { color: var(--svc-soft); line-height: 1.7; }
        .svc-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .svc-card { overflow: hidden; }
        .svc-card__media { aspect-ratio: 4 / 2.7; background: linear-gradient(135deg, #f4e3d9, #f8efe8); display: flex; align-items: center; justify-content: center; }
        .svc-card__media img { width: 100%; height: 100%; object-fit: cover; }
        .svc-card__body { padding: 22px; }
        .svc-pill {
            display: inline-flex; padding: 7px 11px; border-radius: 999px; background: #f6efe9; font-size: 0.82rem; font-weight: 700;
        }
        .svc-btn, .svc-btn--ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 18px; border-radius: 14px; font-weight: 700;
        }
        .svc-btn { background: var(--svc-accent); color: #fff; }
        .svc-btn--ghost { background: rgba(255,255,255,0.75); border: 1px solid var(--svc-line); }
        .svc-filter { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .svc-filter a {
            padding: 10px 14px; border-radius: 999px; border: 1px solid var(--svc-line); background: rgba(255,255,255,0.75); font-weight: 700;
        }
        .svc-filter a.is-active { background: var(--svc-accent); border-color: var(--svc-accent); color: #fff; }
        .svc-section { padding-bottom: 42px; }
        .svc-detail { padding: 32px; }
        .svc-detail-grid { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 24px; }
        .svc-aside { padding: 22px; border-radius: 22px; border: 1px solid var(--svc-line); background: linear-gradient(180deg, #fffaf4 0%, #ffffff 100%); }
        .svc-empty { text-align: center; padding: 54px 24px; }
        @media (max-width: 991px) { .svc-grid, .svc-detail-grid { grid-template-columns: 1fr; } }
        @media (max-width: 700px) {
            .svc-shell { width: min(100% - 20px, 1160px); }
            .svc-panel, .svc-detail { padding: 22px; border-radius: 22px; }
        }
    </style>
</head>
<body>
    <header class="svc-topbar">
        <div class="svc-shell">
            <a href="{{ route('services.index') }}" class="svc-brand">
                <span class="svc-brand__mark">S</span>
                <span>{{ config('app.name') }} Services</span>
            </a>
        </div>
    </header>
    <main>@yield('content')</main>
</body>
</html>
