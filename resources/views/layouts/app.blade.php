<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">

    <title>{{ $title ?? config('app.name', 'PokeTrade') }} — Prismatic Evolutions Marketplace</title>
    <meta name="description" content="Buy, sell, auction, and trade Pokémon TCG: Scarlet & Violet — Prismatic Evolutions cards. Eevee evolutions, illustration rares, pack openings, and more.">

    {{-- Google Fonts: Outfit (display) + Inter (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
