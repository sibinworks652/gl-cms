<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(\App\Support\ModuleRegistry::enabled('seo') && \Illuminate\Support\Facades\View::exists('seo::meta'))
        @include('seo::meta')
    @endif
    <title>{{ $page->title }}</title>
    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        .page-content-shell {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0;
        }
    </style>
</head>
<body>
    <main class="page-content-shell">
        {!! $page->content !!}
    </main>
</body>
</html>
