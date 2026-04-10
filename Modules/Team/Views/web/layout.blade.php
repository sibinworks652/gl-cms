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
            --team-bg: #eef2ec;
            --team-surface: #f9fcf8;
            --team-card: #ffffff;
            --team-ink: #173129;
            --team-soft: #5d6d68;
            --team-line: #d7e1db;
            --team-accent: #2d7a61;
            --team-accent-soft: rgba(45, 122, 97, 0.12);
            --team-shadow: 0 18px 38px rgba(22, 49, 41, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--team-ink);
            background:
                radial-gradient(circle at top left, rgba(45, 122, 97, 0.12), transparent 24%),
                linear-gradient(180deg, #f8fcf8 0%, var(--team-bg) 100%);
        }
        a { color: inherit; text-decoration: none; }
        .team-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        .team-topbar { padding: 24px 0; }
        .team-brand { display: inline-flex; align-items: center; gap: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .team-brand__mark {
            width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--team-accent), #7cc4a4); color: #fff;
        }
        .team-hero { padding: 16px 0 42px; }
        .team-hero__panel {
            padding: 34px; border-radius: 28px; border: 1px solid var(--team-line);
            background: linear-gradient(135deg, rgba(255,255,255,0.94), rgba(245,251,247,0.98)), var(--team-surface);
            box-shadow: var(--team-shadow);
        }
        .team-kicker {
            display: inline-flex; padding: 8px 14px; border-radius: 999px; background: var(--team-accent-soft);
            color: var(--team-accent); font-size: 0.82rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
        }
        h1, h2, h3 { margin: 0 0 12px; line-height: 1.08; }
        h1 { font-size: clamp(2.4rem, 5vw, 4.4rem); max-width: 10ch; }
        .team-copy, .team-meta, .team-subtitle { color: var(--team-soft); line-height: 1.7; }
        .team-actions, .team-filter, .team-socials, .team-badges { display: flex; flex-wrap: wrap; gap: 10px; }
        .team-btn, .team-btn--ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 18px; border-radius: 14px;
            border: 1px solid transparent; font-weight: 700; transition: transform 0.2s ease;
        }
        .team-btn { background: var(--team-accent); color: #fff; box-shadow: 0 15px 30px rgba(45, 122, 97, 0.22); }
        .team-btn--ghost { background: rgba(255,255,255,0.78); border-color: var(--team-line); }
        .team-btn:hover, .team-btn--ghost:hover { transform: translateY(-1px); }
        .team-section { padding-bottom: 40px; }
        .team-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .team-card, .team-detail {
            background: var(--team-card); border: 1px solid var(--team-line); border-radius: 26px; box-shadow: var(--team-shadow);
        }
        .team-card { overflow: hidden; }
        .team-card__media {
            position: relative; aspect-ratio: 4 / 4.5; background: linear-gradient(135deg, #dcebe2, #edf6f1);
            display: flex; align-items: center; justify-content: center;
        }
        .team-card__media img { width: 100%; height: 100%; object-fit: cover; }
        .team-avatar-fallback {
            width: 92px; height: 92px; border-radius: 28px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--team-accent), #94d7b9); color: #fff; font-size: 1.4rem; font-weight: 700;
        }
        .team-card__body { padding: 22px; }
        .team-badge { padding: 7px 11px; border-radius: 999px; background: #eef5f1; font-size: 0.82rem; font-weight: 700; }
        .team-badge--featured { background: var(--team-accent-soft); color: var(--team-accent); }
        .team-filter a {
            padding: 10px 14px; border-radius: 999px; border: 1px solid var(--team-line); background: rgba(255,255,255,0.8); font-weight: 700;
        }
        .team-filter a.is-active { background: var(--team-accent); border-color: var(--team-accent); color: #fff; }
        .team-social {
            width: 38px; height: 38px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
            background: #eff6f2; color: var(--team-accent); font-weight: 700;
        }
        .team-detail { padding: 32px; }
        .team-detail-grid { display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 24px; }
        .team-aside { padding: 24px; border-radius: 24px; border: 1px solid var(--team-line); background: linear-gradient(180deg, #f8fcf8 0%, #fff 100%); }
        .team-list { display: grid; gap: 14px; }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .gap-3 { gap: 1rem; }
        .fw-semibold { font-weight: 600; }
        .team-empty { text-align: center; padding: 56px 24px; }
        @media (max-width: 991px) { .team-grid, .team-detail-grid { grid-template-columns: 1fr; } }
        @media (max-width: 700px) {
            .team-shell { width: min(100% - 20px, 1180px); }
            .team-hero__panel, .team-detail { padding: 22px; border-radius: 22px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="team-topbar">
        <div class="team-shell">
            <a href="{{ route('team.index') }}" class="team-brand">
                <span class="team-brand__mark">T</span>
                <span>{{ $siteName ?? config('app.name') }} Team</span>
            </a>
        </div>
    </header>
    <main>@yield('content')</main>
    @stack('scripts')
</body>
</html>
