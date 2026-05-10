<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PokeTrade') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
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
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    <span class="relative inline-flex h-9 w-9 items-center justify-center">
                        <span class="absolute inset-0 rotate-45 rounded-md bg-white"></span>
                        <span class="absolute inset-[3px] rotate-45 rounded-[5px] prism-bg"></span>
                        <span class="absolute inset-[7px] rotate-45 rounded-[3px] bg-white"></span>
                    </span>
                    <span class="font-display text-2xl font-black tracking-tight">
                        Poke<span class="prism-text">Trade</span>
                    </span>
                </a>

                <div>
                    <h2 class="font-display text-5xl font-black leading-[0.95] xl:text-6xl">
                        Build your<br/>prismatic<br/>binder.
                    </h2>
                    <p class="mt-5 max-w-md text-white/80">
                        Buy, sell, auction, trade, and pull packs from the Scarlet &amp; Violet — Prismatic Evolutions expansion. 180 cards, every Eevee evolution, fully holographic.
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
