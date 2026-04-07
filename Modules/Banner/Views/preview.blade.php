<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Slider Preview</title>
    <style>
        :root {
            --bg: #07111f;
            --panel: #0f1c2f;
            --text: #f8fafc;
            --muted: rgba(248, 250, 252, 0.72);
            --accent: #f97316;
            --accent-2: #38bdf8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.22), transparent 30%),
                radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.18), transparent 28%),
                var(--bg);
            color: var(--text);
        }

        .hero-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .slider {
            position: relative;
            width: min(1200px, 100%);
            overflow: hidden;
            border-radius: 28px;
            background: rgba(15, 28, 47, 0.88);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .slides {
            display: flex;
            transition: transform 0.55s ease;
            will-change: transform;
        }

        .slide {
            min-width: 100%;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            align-items: stretch;
        }

        .slide__content {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: fit-content;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.12);
            color: #bae6fd;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .slide h1 {
            font-size: clamp(2rem, 4vw, 4rem);
            line-height: 1.02;
            margin: 0 0 1rem;
        }

        .slide h2 {
            margin: 0 0 1rem;
            color: var(--accent);
            font-size: clamp(1rem, 2vw, 1.4rem);
            font-weight: 600;
        }

        .slide p {
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.8;
            margin: 0 0 1.5rem;
            max-width: 56ch;
        }

        .slide__actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .slide__actions a {
            text-decoration: none;
            padding: 0.9rem 1.2rem;
            border-radius: 999px;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #fb923c);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .slide__media {
            min-height: 520px;
            position: relative;
            background: linear-gradient(160deg, rgba(56, 189, 248, 0.14), rgba(249, 115, 22, 0.14));
        }

        .slide__media img,
        .slide__media iframe {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border: 0;
        }

        .slide__media-placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 1rem;
        }

        .slider__controls {
            position: absolute;
            left: 1.5rem;
            right: 1.5rem;
            bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
        }

        .slider__nav,
        .slider__dots {
            pointer-events: auto;
        }

        .slider__nav button {
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            cursor: pointer;
            margin-right: 0.5rem;
        }

        .slider__dots {
            display: flex;
            gap: 0.5rem;
        }

        .slider__dots button {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            border: 0;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.28);
        }

        .slider__dots button.is-active {
            width: 34px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
        }

        .preview-note {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: var(--muted);
            font-size: 0.85rem;
        }

        .empty {
            width: min(900px, 100%);
            padding: 2rem;
            border-radius: 24px;
            background: rgba(15, 28, 47, 0.88);
            text-align: center;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .slide {
                grid-template-columns: 1fr;
            }

            .slide__content {
                padding: 2rem;
            }

            .slide__media {
                min-height: 300px;
            }
        }
    </style>
</head>
<body>
    <main class="hero-shell">
        @if ($slides->isEmpty())
            <div class="empty">
                <h1>No Live Banner Slides</h1>
                <p>Create active banners with a valid schedule to preview the frontend slider here.</p>
            </div>
        @else
            <section class="slider" id="banner-slider">
                <div class="preview-note">Frontend Banner Preview</div>
                <div class="slides">
                    @foreach ($slides as $slide)
                        <article class="slide">
                            <div class="slide__content">
                                <div class="eyebrow">Slide {{ $loop->iteration }}</div>
                                <h1>{{ $slide->title }}</h1>
                                @if ($slide->subtitle)
                                    <h2>{{ $slide->subtitle }}</h2>
                                @endif
                                @if ($slide->description)
                                    <p>{{ $slide->description }}</p>
                                @endif
                                <div class="slide__actions">
                                    @if ($slide->resolved_button_link && $slide->button_label)
                                        <a href="{{ $slide->resolved_button_link }}" class="btn-primary" @if($slide->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif>
                                            {{ $slide->button_label }}
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.banners.index') }}" class="btn-secondary">Manage Slides</a>
                                </div>
                            </div>
                            <div class="slide__media">
                                @if ($slide->media_type === 'image' && $slide->image_path)
                                    <img src="{{ asset('storage/' . $slide->image_path) }}" alt="{{ $slide->title }}">
                                @elseif ($slide->media_type === 'video' && $slide->video_url)
                                    <iframe src="{{ $slide->video_url }}" title="{{ $slide->title }}" allowfullscreen></iframe>
                                @else
                                    <div class="slide__media-placeholder">No media available</div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="slider__controls">
                    <div class="slider__nav">
                        <button type="button" id="banner-prev" aria-label="Previous slide">&#8592;</button>
                        <button type="button" id="banner-next" aria-label="Next slide">&#8594;</button>
                    </div>
                    <div class="slider__dots" id="banner-dots"></div>
                </div>
            </section>
        @endif
    </main>

    @if ($slides->isNotEmpty())
        <script>
        (() => {
            const slider = document.getElementById('banner-slider');
            const track = slider.querySelector('.slides');
            const slides = Array.from(slider.querySelectorAll('.slide'));
            const dotsWrap = document.getElementById('banner-dots');
            const prevBtn = document.getElementById('banner-prev');
            const nextBtn = document.getElementById('banner-next');
            let current = 0;
            let timer = null;

            slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.addEventListener('click', () => goTo(index, true));
                dotsWrap.appendChild(dot);
            });

            const dots = Array.from(dotsWrap.querySelectorAll('button'));

            function render() {
                track.style.transform = `translateX(-${current * 100}%)`;
                dots.forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === current);
                });
            }

            function goTo(index, resetTimer = false) {
                current = (index + slides.length) % slides.length;
                render();
                if (resetTimer) startAuto();
            }

            function startAuto() {
                window.clearInterval(timer);
                timer = window.setInterval(() => goTo(current + 1), 4500);
            }

            prevBtn.addEventListener('click', () => goTo(current - 1, true));
            nextBtn.addEventListener('click', () => goTo(current + 1, true));
            slider.addEventListener('mouseenter', () => window.clearInterval(timer));
            slider.addEventListener('mouseleave', startAuto);

            render();
            startAuto();
        })();
        </script>
    @endif
</body>
</html>
