@php
    $seo = $seo
        ?? (request()->route()?->getName()
            ? \Modules\Seo\Models\SeoSetting::findFor(request()->route()->getName(), 'route')
            : null)
        ?? \Modules\Seo\Models\SeoSetting::findFor(request()->path());
    $title = $seo?->seo_meta_title;
    $description = $seo?->seo_meta_description;
    $keywords = $seo?->seo_meta_keywords;
    $image = $seo?->ogImageUrl();
@endphp

@if($seo)
    @if($title)
        <title>{{ $title }}</title>
        <meta property="og:title" content="{{ $title }}">
        <meta name="twitter:title" content="{{ $title }}">
    @endif
    @if($description)
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    @if($keywords)
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <meta name="robots" content="{{ $seo->seo_indexing === 'noindex' ? 'noindex,nofollow' : 'index,follow' }}">
    @if($seo->seo_canonical_url)
        <link rel="canonical" href="{{ $seo->seo_canonical_url }}">
    @endif
    <meta property="og:type" content="website">
    @if($image)
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:image" content="{{ $image }}">
    @endif
    <meta name="twitter:card" content="{{ $seo->seo_twitter_card }}">
@endif
