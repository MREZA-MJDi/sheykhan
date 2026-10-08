@php
    $seo = $seoMeta ?? null;
    $pageTitle = trim((string) ($seo?->title ?: trim($__env->yieldContent('title'))));
    $pageDescription = trim((string) ($seo?->description ?: trim($__env->yieldContent('description'))));
    $canonical = $seo?->canonical_url ?: url()->current();
    $robots = $seo?->robots ?: 'index,follow';
    $ogTitle = $seo?->og_title ?: $pageTitle;
    $ogDescription = $seo?->og_description ?: $pageDescription;

    $ogImage = $seo?->og_image_url;

    if (!$ogImage && isset($product)) {
        $ogImage = $product->media?->first()?->url();
    }

    if (!$ogImage && isset($post)) {
        $ogImage = $post->media?->first()?->url();
    }

    if (!$ogImage && isset($course)) {
        $ogImage = $course->media?->first()?->url();
    }

    $schema = $seo?->schema_json;

    if (!$schema && request()->routeIs('home')) {
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => 'شیخان',
                    'url' => url('/'),
                ],
                [
                    '@type' => 'WebSite',
                    'name' => 'شیخان',
                    'url' => url('/'),
                    'inLanguage' => 'fa-IR',
                ],
            ],
        ];
    } elseif (!$schema && isset($product)) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $pageDescription,
            'url' => $canonical,
            'image' => $ogImage ? [$ogImage] : [],
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) ($product->sale_price ?? $product->price),
                'priceCurrency' => $product->currency ?: 'IRR',
                'availability' => 'https://schema.org/InStock',
                'url' => $canonical,
            ],
        ];
    } elseif (!$schema && isset($post)) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $pageTitle,
            'description' => $pageDescription,
            'url' => $canonical,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'image' => $ogImage ? [$ogImage] : [],
        ];
    } elseif (!$schema && isset($course)) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => $pageDescription,
            'url' => $canonical,
            'inLanguage' => 'fa-IR',
        ];
    } elseif (!$schema && isset($teacher)) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $teacher->name,
            'url' => $canonical,
            'jobTitle' => $teacher->teacherProfile?->specialization ?: 'مدرس',
        ];
    }
@endphp

<title>{{ $pageTitle ?: 'شیخان | آموزش، رشد، آینده' }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="{{ isset($post) ? 'article' : (isset($product) ? 'product' : 'website') }}">
<meta property="og:site_name" content="شیخان">
<meta property="og:locale" content="fa_IR">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $canonical }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

@if($schema)
    <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)</script>
@endif
