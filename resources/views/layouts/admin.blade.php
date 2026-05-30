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
        $iconAttrs = 'xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-5 w-5"';
        $items = [
            [
                'route' => 'admin.dashboard',
                'label' => 'Dashboard',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
            ],
            [
                'route' => 'admin.cards.index',
                'label' => 'Cards',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>',
            ],
            [
                'route' => 'admin.shop.index',
                'label' => 'Shop items',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.434 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>',
            ],
            [
                'route' => 'admin.auctions.index',
                'label' => 'Auctions',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>',
            ],
            [
                'route' => 'admin.orders.index',
                'label' => 'Orders',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>',
            ],
            [
                'route' => 'admin.users.index',
                'label' => 'Users',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
            ],
            [
                'route' => 'admin.reports.index',
                'label' => 'Reports',
                'icon'  => '<svg '.$iconAttrs.'><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" /></svg>',
            ],
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
                        <span class="inline-flex h-5 w-5 items-center justify-center">{!! $i['icon'] !!}</span>
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
