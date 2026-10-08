@php
    $seo = $seoMeta ?? null;
    $pageTitle = trim((string) ($seo?->title ?: trim($__env->yieldContent('title'))));
    $pageDescription = trim((string) ($seo?->description ?: trim($__env->yieldContent('description'))));
    $canonical = $seo?->canonical_url ?: url()->current();
    $robots = $seo?->robots ?: 'index,follow';
    $ogTitle = $seo?->og_title ?: $pageTitle;
    $ogDescription = $seo?->og_description ?: $pageDescription;
@endphp

<title>{{ $pageTitle ?: 'شیخان | آموزش، رشد، آینده' }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $canonical }}">
@if($seo?->og_image_url)
    <meta property="og:image" content="{{ $seo->og_image_url }}">
@endif

@if($seo?->schema_json)
    <script type="application/ld+json">@json($seo->schema_json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)</script>
@endif
