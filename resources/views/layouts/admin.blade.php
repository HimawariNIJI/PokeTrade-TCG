<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin · PokeTrade</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink-50 antialiased text-ink-900">

    @php
        $items = [
            ['route' => 'admin.dashboard',  'label' => 'Dashboard',     'icon' => '◇'],
            ['route' => 'admin.cards.index','label' => 'Cards',         'icon' => '◆'],
            ['route' => 'admin.shop.index',     'label' => 'Shop items',    'icon' => '▣'],
            ['route' => 'admin.auctions.index','label' => 'Auctions',      'icon' => '⚡'],
            ['route' => 'admin.orders.index',  'label' => 'Orders',         'icon' => '⬢'],
            ['route' => 'admin.users.index','label' => 'Users',         'icon' => '◉'],
            ['route' => 'admin.reports.index','label' => 'Reports',     'icon' => '⚑'],
        ];
        $openReports = \App\Models\Report::where('status', 'open')->count();
    @endphp

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside class="sticky top-0 hidden h-screen w-72 flex-shrink-0 border-r border-ink-200 bg-white lg:flex lg:flex-col">
            <div class="border-b border-ink-200 px-6 py-5">
                <x-brand-mark size="md" />
                <p class="mt-2 inline-flex items-center gap-2 rounded-full bg-ink-900 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest text-white">
                    Admin Console
                </p>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                @foreach($items as $i)
                    @php $active = request()->routeIs($i['route']) || request()->routeIs(str_replace('.index', '.*', $i['route'])); @endphp
                    <a href="{{ route($i['route']) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition
                              {{ $active ? 'bg-ink-900 text-white' : 'text-ink-700 hover:bg-ink-100' }}">
                        <span class="font-mono text-base">{{ $i['icon'] }}</span>
                        <span class="flex-1">{{ $i['label'] }}</span>
                        @if($i['route'] === 'admin.reports.index' && $openReports > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-[10px] font-bold text-white">{{ $openReports }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-ink-200 px-3 py-4">
                <a href="{{ route('home') }}" class="block rounded-xl px-3 py-2 text-xs font-semibold text-ink-500 hover:bg-ink-100 hover:text-ink-900">
                    ← Back to public site
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-xs font-semibold text-rose-600 hover:bg-rose-50">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN --}}
        <main class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-ink-200 bg-white/85 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-4 py-3 md:px-8">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">{{ $eyebrow ?? 'Admin' }}</p>
                        <h1 class="font-display text-2xl font-black tracking-tight">{{ $heading ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(isset($actions))
                            {{ $actions }}
                        @endif
                        <span class="hidden md:inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-xs font-semibold">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full prism-bg text-[10px] font-bold text-white">
                                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                </div>
            </header>

            <div class="px-4 py-6 md:px-8 md:py-10">
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>

    <x-flash />
</body>
</html>
