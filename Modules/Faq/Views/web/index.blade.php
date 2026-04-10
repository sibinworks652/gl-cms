<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($siteName ?? config('app.name')) . ' FAQ' }}</title>
    <meta name="description" content="Find answers to common questions, grouped by topic and searchable from one place.">
    <style>
        :root {
            --faq-bg: #f4f2ee;
            --faq-card: #ffffff;
            --faq-ink: #21303d;
            --faq-soft: #697786;
            --faq-line: #dde3e8;
            --faq-accent: #1f6d8d;
            --faq-shadow: 0 16px 34px rgba(33, 48, 61, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            color: var(--faq-ink);
            background: radial-gradient(circle at top right, rgba(31, 109, 141, 0.12), transparent 22%), linear-gradient(180deg, #faf9f7 0%, var(--faq-bg) 100%);
        }
        a { color: inherit; text-decoration: none; }
        .faq-shell { width: min(1080px, calc(100% - 32px)); margin: 0 auto; }
        .faq-hero { padding: 42px 0 24px; }
        .faq-panel, .faq-item { background: var(--faq-card); border: 1px solid var(--faq-line); border-radius: 24px; box-shadow: var(--faq-shadow); }
        .faq-panel { padding: 32px; }
        .faq-kicker { display: inline-flex; padding: 8px 14px; border-radius: 999px; background: rgba(31, 109, 141, 0.12); color: var(--faq-accent); font-size: 0.82rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
        h1, h2, h3 { margin: 0 0 12px; line-height: 1.1; }
        h1 { font-size: clamp(2.2rem, 5vw, 4rem); max-width: 10ch; }
        .faq-copy, .faq-meta { color: var(--faq-soft); line-height: 1.7; }
        .faq-filters { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .faq-input, .faq-select {
            border-radius: 14px; border: 1px solid var(--faq-line); background: #fff; padding: 13px 14px; font: inherit; color: var(--faq-ink);
        }
        .faq-btn {
            display: inline-flex; align-items: center; justify-content: center; padding: 13px 18px; border-radius: 14px;
            background: var(--faq-accent); color: #fff; font-weight: 700; border: 0;
        }
        .faq-section { padding-bottom: 44px; }
        .faq-group { margin-bottom: 22px; }
        .faq-item { overflow: hidden; }
        .faq-item + .faq-item { margin-top: 14px; }
        .faq-question {
            width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 22px;
            background: transparent; border: 0; text-align: left; font: inherit; font-weight: 700; color: var(--faq-ink); cursor: pointer;
        }
        .faq-answer { padding: 0 22px 22px; color: var(--faq-soft); line-height: 1.75; display: none; }
        .faq-item.is-open .faq-answer { display: block; }
        .faq-icon { width: 32px; height: 32px; border-radius: 999px; background: #eef5f8; color: var(--faq-accent); display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .faq-empty { text-align: center; padding: 56px 24px; }
        @media (max-width: 700px) {
            .faq-shell { width: min(100% - 20px, 1080px); }
            .faq-panel { padding: 24px; border-radius: 20px; }
        }
    </style>
</head>
<body>
    <section class="faq-hero">
        <div class="faq-shell">
            <div class="faq-panel">
                <span class="faq-kicker">Help Center</span>
                <h1>Answers, without the wait.</h1>
                <p class="faq-copy">A reusable FAQ page with optional categories, search, and expandable answers for onboarding, support, and sales questions.</p>
                <form method="GET" class="faq-filters">
                    <input type="text" name="search" class="faq-input" value="{{ request('search') }}" placeholder="Search questions or answers">
                    <select name="category" class="faq-select">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button class="faq-btn" type="submit">Filter</button>
                </form>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="faq-shell">
            @php($groupedFaqs = $faqs->getCollection()->groupBy(fn ($faq) => $faq->category?->name ?? 'General'))
            @forelse($groupedFaqs as $group => $items)
                <div class="faq-group">
                    <h2>{{ $group }}</h2>
                    @foreach($items as $faq)
                        <article class="faq-item" data-faq-item>
                            <button type="button" class="faq-question" data-faq-toggle>
                                <span>{{ $faq->question }}</span>
                                <span class="faq-icon">+</span>
                            </button>
                            <div class="faq-answer">{!! $faq->answer !!}</div>
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="faq-panel faq-empty">
                    <h3>No FAQs found.</h3>
                    <p class="faq-copy">Try changing your search or category filter.</p>
                </div>
            @endforelse

            <div style="margin-top: 28px;">{{ $faqs->links() }}</div>
        </div>
    </section>

    <script>
    document.querySelectorAll('[data-faq-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const item = button.closest('[data-faq-item]');
            item.classList.toggle('is-open');
            const icon = button.querySelector('.faq-icon');
            if (icon) icon.textContent = item.classList.contains('is-open') ? '-' : '+';
        });
    });
    </script>
</body>
</html>
