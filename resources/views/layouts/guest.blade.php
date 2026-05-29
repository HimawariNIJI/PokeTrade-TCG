<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PokeTrade') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white antialiased text-ink-900">

    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- LEFT: brand panel --}}
        <aside class="relative hidden overflow-hidden lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-prism-pink via-prism-violet to-prism-sky"></div>
            <div class="absolute inset-0 halftone opacity-20"></div>

            {{-- Floating accent shapes --}}
            <div class="absolute -left-20 top-1/3 h-72 w-72 rounded-full bg-prism-gold/30 blur-3xl"></div>
            <div class="absolute -right-20 bottom-1/3 h-72 w-72 rounded-full bg-prism-mint/30 blur-3xl"></div>

            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5">
                    <span class="relative inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-full shadow-lg ring-2 ring-white/80 transition-transform duration-300 ease-[var(--ease-spring)] group-hover:-rotate-12">
                        <span class="absolute inset-x-0 bottom-0 top-1/2 bg-white"></span>
                        <span class="absolute inset-x-0 top-0 h-1/2 bg-poke-red"></span>
                        <span class="absolute inset-x-0 top-1/2 h-[2px] -translate-y-1/2 bg-ink-900"></span>
                        <span class="relative h-1/3 w-1/3 rounded-full bg-white ring-2 ring-ink-900"></span>
                    </span>
                    <span class="font-display text-2xl font-bold tracking-tight">
                        Poke<span class="text-prism-gold">Trade</span>
                    </span>
                </a>

                <div>
                    <h2 class="font-display text-5xl font-bold leading-[0.95] xl:text-6xl">
                        Build your<br/>prismatic<br/>binder.
                    </h2>
                    <p class="mt-5 max-w-md text-white/80">
                        Track market prices, bid in live auctions, pull digital gacha packs, and show off your collection. The Scarlet &amp; Violet: Prismatic Evolutions home base, fully holographic.
                    </p>
                </div>

                <p class="text-xs text-white/60">© {{ now()->year }} PokeTrade · Student project · Card data via pokemontcg.io</p>
            </div>
        </aside>

        {{-- RIGHT: form --}}
        <main class="flex items-center justify-center px-4 py-12 sm:px-12">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <x-brand-mark size="md" />
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>

    <x-flash />
</body>
</html>
