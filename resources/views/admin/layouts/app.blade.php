<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $adminSettings = \Modules\Settings\Models\Setting::pairs();
        // dd($adminSettings);
        $adminPreferredTheme = (string) ($adminSettings['admin_dark_mode_enabled'] ?? '0') === '1' ? 'dark' : 'light';
        $hexToRgb = function (?string $hex): ?string {
            if (! is_string($hex) || ! preg_match('/^#([A-Fa-f0-9]{6})$/', $hex)) {
                return null;
            }

            return implode(', ', [
                hexdec(substr($hex, 1, 2)),
                hexdec(substr($hex, 3, 2)),
                hexdec(substr($hex, 5, 2)),
            ]);
        };
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @if(!empty($adminSettings['site_favicon']))
        <link rel="shortcut icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
        <link rel="icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
    @endif
    @include('seo::meta')

    <script>
        window.adminPreferredTheme = @json($adminPreferredTheme);
    </script>
    @include('admin.layouts.partials.head-css')
    @if(
        filled($adminSettings['admin_primary_color'] ?? null) ||
        filled($adminSettings['admin_topbar_bg'] ?? null) ||
        filled($adminSettings['admin_topbar_text_color'] ?? null) ||
        filled($adminSettings['admin_sidebar_bg'] ?? null) ||
        filled($adminSettings['admin_sidebar_text_color'] ?? null) ||
        filled($adminSettings['admin_sidebar_hover_color'] ?? null) ||
        filled($adminSettings['admin_page_bg'] ?? null)
    )
        <style>
            @if(filled($adminSettings['admin_primary_color'] ?? null))
                :root {
                        --bs-primary: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-primary-rgb: {!! $hexToRgb($adminSettings['admin_primary_color']) !!} !important;
                        --bs-btn-bg: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-link-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-link-hover-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }
            @endif

            html[data-bs-theme="light"] {
                @if(filled($adminSettings['admin_topbar_bg'] ?? null))
                    --bs-topbar-bg: {!! $adminSettings['admin_topbar_bg'] !!} !important;
                    --bs-topbar-search-bg: {!! $adminSettings['admin_topbar_bg'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_topbar_text_color'] ?? null))
                    --bs-topbar-item-color: {!! $adminSettings['admin_topbar_text_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_bg'] ?? null))
                    --bs-main-nav-bg: {!! $adminSettings['admin_sidebar_bg'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_text_color'] ?? null))
                    --bs-main-nav-item-color: {!! $adminSettings['admin_sidebar_text_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_hover_color'] ?? null))
                    --bs-main-nav-item-hover-color: {!! $adminSettings['admin_sidebar_hover_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_page_bg'] ?? null))
                    --bs-body-bg: {!! $adminSettings['admin_page_bg'] !!} !important;
                @endif
            }

            @if(filled($adminSettings['admin_primary_color'] ?? null))
                .btn-primary,
                .bg-primary {
                    background-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }

                .text-primary,
                a {
                    color: {!! $adminSettings['admin_primary_color'] !!};
                }
                .form-check-input:checked{
                    background-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }
            @endif
        </style>
    @endif
    <style>
        .auto-contrast-logo {
            --auto-logo-bg: transparent;
            background: var(--auto-logo-bg);
            border-radius: 6px;
            box-decoration-break: clone;
            transition: background 0.2s ease;
        }

        .auto-contrast-logo.logo-contrast-ready {
            padding: 4px 6px;
        }

        .auto-contrast-logo-preview.logo-contrast-ready {
            padding: 10px;
        }
    </style>
    @stack('styles')
    </head>
<body>
    <div class="wrapper">
         @include('admin.layouts.partials.topbar')
         @include('admin.layouts.partials.main-nav')
         <div class="page-content pt-3">
             @yield('content')
         </div>
    </div>
    @include('admin.layouts.partials.right-sidebar')
    @include('admin.layouts.partials.toast')
    @include('admin.layouts.partials.vendor-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const darkBackground = '#1f2937';
            const lightBackground = '#f8fafc';

            function applyContrastBackground(image, luminance) {
                image.style.setProperty('--auto-logo-bg', luminance > 170 ? darkBackground : lightBackground);
                image.classList.add('logo-contrast-ready');
            }

            function detectWithCanvas(image) {
                if (!image.naturalWidth || !image.naturalHeight) {
                    return false;
                }

                const maxSize = 64;
                const ratio = Math.min(maxSize / image.naturalWidth, maxSize / image.naturalHeight, 1);
                const width = Math.max(1, Math.round(image.naturalWidth * ratio));
                const height = Math.max(1, Math.round(image.naturalHeight * ratio));
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d', { willReadFrequently: true });

                canvas.width = width;
                canvas.height = height;
                context.drawImage(image, 0, 0, width, height);

                const pixels = context.getImageData(0, 0, width, height).data;
                let red = 0;
                let green = 0;
                let blue = 0;
                let count = 0;

                for (let index = 0; index < pixels.length; index += 4) {
                    if (pixels[index + 3] < 32) {
                        continue;
                    }

                    red += pixels[index];
                    green += pixels[index + 1];
                    blue += pixels[index + 2];
                    count++;
                }

                if (!count) {
                    return false;
                }

                applyContrastBackground(image, (0.2126 * (red / count)) + (0.7152 * (green / count)) + (0.0722 * (blue / count)));

                return true;
            }

            function detectLogoContrast(image) {
                try {
                    if (detectWithCanvas(image)) {
                        return;
                    }
                } catch (error) {
                    image.style.setProperty('--auto-logo-bg', 'linear-gradient(135deg, #1f2937 0 50%, #f8fafc 50% 100%)');
                    image.classList.add('logo-contrast-ready');
                }
            }

            document.querySelectorAll('img.auto-contrast-logo').forEach(function (image) {
                if (image.complete) {
                    detectLogoContrast(image);
                    return;
                }

                image.addEventListener('load', function () {
                    detectLogoContrast(image);
                }, { once: true });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
