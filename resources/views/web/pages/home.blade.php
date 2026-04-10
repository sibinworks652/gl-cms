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
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background: #f8fafc;
            color: #111827;
        }

        .page-shell {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 32px;
        }
    </style>
</head>
<body>
    <main class="page-shell">
        <h1>{{ $page->title }}</h1>
        <p>Start editing this Blade file:</p>
        <p><code>{{ $page->view_path }}</code></p>
    </main>
</body>
</html>
