<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1220">

    @php
        $siteName = 'PokeTrade';
        $pageTitle = ($title ?? null)
            ? $title . ' · ' . $siteName . ' — Prismatic Evolutions'
            : $siteName . ' — Prismatic Evolutions Price Tracker, Auctions & Gacha';
        $pageDescription = $description
            ?? 'Track Pokemon TCG Scarlet & Violet: Prismatic Evolutions card prices with live market values, plus official merch, real-card auctions, a digital gacha, and a trainer community.';
        $canonicalUrl = $canonical ?? url()->current();
        $ogImageUrl = $ogImage ?? asset('images/og-default.png');
        $ogType = $ogType ?? 'website';
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index, follow, max-image-preview:large">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta property="og:locale" content="en_US">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    {{-- Favicons (prism pokeball) --}}
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    {{-- Google Fonts: Fredoka (display) + Nunito (body) + JetBrains Mono (numbers) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Site-wide structured data --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#org',
                'name' => $siteName,
                'url' => url('/'),
                'logo' => asset('favicon.svg'),
                'description' => 'A Pokemon TCG Prismatic Evolutions price tracker with merch, auctions, a digital gacha, and community forums.',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'name' => $siteName,
                'url' => url('/'),
                'publisher' => ['@id' => url('/') . '#org'],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/cards') . '?q={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @stack('jsonld')
</head>
<body class="min-h-screen bg-white antialiased text-ink-900">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-ink-900 focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    @include('layouts.partials.nav')

    <main id="main" class="relative">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    <x-flash />

    {{-- Single shared overlay: any card on the page can spin to centre --}}
    <x-card-flip-overlay />
</body>
</html>
