<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Menu Preview</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .topbar {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            padding: 1.25rem 2rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        }

        .topbar__inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .brand {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .menu-list,
        .submenu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu-list {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .menu-list > li {
            position: relative;
        }

        .menu-list a,
        .submenu a {
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 0.65rem 0.85rem;
            border-radius: 999px;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .menu-list > li > a {
            color: rgba(255, 255, 255, 0.92);
        }

        .menu-list > li:hover > a,
        .submenu a:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .submenu {
            position: absolute;
            top: calc(100% + 0.4rem);
            left: 0;
            min-width: 220px;
            background: #fff;
            color: #0f172a;
            border-radius: 1rem;
            padding: 0.5rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
            display: none;
            z-index: 20;
        }

        .submenu .submenu {
            top: 0;
            left: calc(100% + 0.4rem);
        }

        .menu-list li:hover > .submenu,
        .submenu li:hover > .submenu {
            display: block;
        }

        .content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .card {
            background: #fff;
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
        }

        .empty {
            color: #64748b;
        }

        code {
            background: #e2e8f0;
            padding: 0.15rem 0.4rem;
            border-radius: 0.35rem;
        }
    </style>
</head>
<body>
    @php($headerMenu = $dynamicMenus->get('header'))
    @php($settings = \Modules\Settings\Models\Setting::pairs())
    @php($siteName = $settings['site_name'] ?? 'CMS Demo')
    @php($footerCopyright = $settings['footer_copyright'] ?? ('© ' . now()->year . ' ' . $siteName . '. All rights reserved.'))

    <header class="topbar">
        <div class="topbar__inner">
            <div class="brand">{{ $siteName }}</div>

            <nav aria-label="Header menu">
                @if($headerMenu && $headerMenu->rootItems->isNotEmpty())
                    @include('menu::partials.render', ['items' => $headerMenu->rootItems, 'class' => 'menu-list'])
                @else
                    <div class="empty">No active header menu found.</div>
                @endif
            </nav>
        </div>
    </header>

    <main class="content">
        <div class="card">
            <h1>Header Menu Preview</h1>
            <p>This page shows a sample frontend header using the dynamic menu system.</p>
            <p>Create a menu in the admin area, assign its location to <code>header</code>, and add nested items to see the dropdown structure here.</p>
            <p>Preview URL: <code>/menu-preview</code></p>
        </div>
    </main>

    <footer style="border-top: 1px solid #e2e8f0; padding: 1.5rem 2rem; color: #64748b; background: #fff;">
        <div style="max-width: 1100px; margin: 0 auto;">
            {{ $footerCopyright }}
        </div>
    </footer>
</body>
</html>
