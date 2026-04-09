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
            --career-bg: #f3efe9;
            --career-surface: #fffaf4;
            --career-card: #ffffff;
            --career-ink: #1f2937;
            --career-soft: #6b7280;
            --career-line: #e5ddd1;
            --career-accent: #c85e2f;
            --career-accent-deep: #9f4320;
            --career-accent-soft: rgba(200, 94, 47, 0.12);
            --career-success: #1d8f5a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Play", sans-serif;
            color: var(--career-ink);
            background:
                radial-gradient(circle at top left, rgba(200, 94, 47, 0.12), transparent 30%),
                linear-gradient(180deg, #fbf7f2 0%, var(--career-bg) 100%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .career-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .career-topbar {
            padding: 22px 0;
        }

        .career-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .career-brand__mark {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--career-accent), #f5b28d);
            color: #fff;
            font-size: 1.1rem;
        }

        .career-hero {
            padding: 20px 0 44px;
        }

        .career-hero__panel {
            background: linear-gradient(135deg, #fff7ef 0%, #fff 45%, #f7ece2 100%);
            border: 1px solid var(--career-line);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 18px 45px rgba(58, 42, 28, 0.08);
        }

        .career-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--career-accent-soft);
            color: var(--career-accent-deep);
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        h1, h2, h3, h4 {
            color: var(--career-ink);
            margin: 0 0 12px;
            line-height: 1.1;
        }

        h1 {
            font-size: clamp(2.2rem, 5vw, 4.3rem);
            max-width: 11ch;
        }

        .career-hero__copy {
            max-width: 760px;
        }

        .career-subtitle,
        .career-copy,
        .career-meta,
        .career-card p,
        .career-form-note {
            color: var(--career-soft);
            line-height: 1.65;
        }

        .career-grid {
            display: grid;
            gap: 24px;
        }

        .career-grid--sidebar {
            grid-template-columns: minmax(240px, 290px) minmax(0, 1fr);
        }

        .career-card,
        .career-sidebar,
        .career-form-card {
            background: var(--career-card);
            border: 1px solid var(--career-line);
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(58, 42, 28, 0.06);
        }

        .career-sidebar,
        .career-form-card,
        .career-card {
            padding: 24px;
        }

        .career-list {
            display: grid;
            gap: 18px;
        }

        .career-job-card {
            padding: 24px;
        }

        .career-kicker,
        .career-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .career-kicker {
            background: #eef6f1;
            color: var(--career-success);
        }

        .career-pill {
            background: #f6f2ec;
            color: var(--career-ink);
        }

        .career-meta-row,
        .career-pills,
        .career-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .career-actions {
            margin-top: 18px;
        }

        .career-btn,
        .career-btn--ghost,
        .career-btn--light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
            border: 1px solid transparent;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .career-btn {
            background: var(--career-accent);
            color: #fff;
            box-shadow: 0 12px 28px rgba(200, 94, 47, 0.22);
        }

        .career-btn--ghost {
            background: transparent;
            border-color: var(--career-line);
            color: var(--career-ink);
        }

        .career-btn--light {
            background: #f8f4ee;
            color: var(--career-ink);
        }

        .career-btn:hover,
        .career-btn--ghost:hover,
        .career-btn--light:hover {
            transform: translateY(-1px);
        }

        .career-field,
        .career-field textarea,
        .career-field select,
        .career-field input {
            width: 100%;
        }

        .career-field {
            display: grid;
            gap: 8px;
        }

        .career-field label {
            font-size: 0.92rem;
            font-weight: 700;
        }

        .career-input,
        .career-select,
        .career-textarea {
            border-radius: 14px;
            border: 1px solid var(--career-line);
            background: #fff;
            padding: 14px 15px;
            font: inherit;
            color: var(--career-ink);
        }

        .career-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .career-flash {
            margin-bottom: 18px;
            border-radius: 16px;
            padding: 14px 16px;
            background: #eaf8ef;
            color: #15603d;
            border: 1px solid #c5ecd4;
        }

        .career-errors {
            margin: 0 0 18px;
            padding: 14px 18px;
            border-radius: 16px;
            background: #fff0ef;
            border: 1px solid #f0c6c1;
            color: #992d20;
        }

        .career-section {
            margin-top: 24px;
        }

        .career-richtext {
            line-height: 1.8;
            color: var(--career-ink);
        }

        .career-richtext ul,
        .career-richtext ol {
            padding-left: 18px;
        }

        .career-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .career-stat {
            padding: 18px;
            border-radius: 18px;
            border: 1px solid var(--career-line);
            background: rgba(255, 255, 255, 0.72);
        }

        .career-stat strong {
            display: block;
            font-size: 1.15rem;
            margin-top: 8px;
        }

        .career-empty {
            text-align: center;
            padding: 50px 24px;
        }

        @media (max-width: 991px) {
            .career-grid--sidebar {
                grid-template-columns: 1fr;
            }

            .career-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .career-shell {
                width: min(100% - 20px, 1180px);
            }

            .career-hero__panel,
            .career-sidebar,
            .career-form-card,
            .career-card {
                border-radius: 20px;
                padding: 20px;
            }

            .career-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="career-topbar">
        <div class="career-shell">
            <a href="{{ route('careers.index') }}" class="career-brand">
                <span class="career-brand__mark">C</span>
                <span>{{ $siteName ?? config('app.name') }} Careers</span>
            </a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
