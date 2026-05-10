@php
    $navLinks = [
        ['route' => 'home',           'label' => 'Home'],
        ['route' => 'cards.index',    'label' => 'Card Shop'],
        ['route' => 'shop.index',     'label' => 'Merch'],
        ['route' => 'auctions.index', 'label' => 'Auctions'],
        ['route' => 'packs.index',    'label' => 'Open Pack'],
        ['route' => 'about',          'label' => 'About'],
    ];
@endphp

<header x-data="{ open: false, scrolled: window.scrollY > 8 }"
        @scroll.window="scrolled = window.scrollY > 8"
        class="sticky top-0 z-40 transition"
        :class="scrolled ? 'backdrop-blur-md bg-white/85 border-b border-ink-200 shadow-sm' : 'bg-white/95 border-b border-transparent'">

    {{-- Top hairline rainbow --}}
    <div class="h-[3px] w-full prism-bg"></div>

    <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-6 px-4 py-3 md:px-8 md:py-4">
        <x-brand-mark size="md" />

        <nav class="hidden items-center gap-1 lg:flex">
            @foreach($navLinks as $link)
                @php $active = request()->routeIs($link['route']); @endphp
                <a href="{{ route($link['route']) }}"
                   class="relative rounded-full px-4 py-2 text-sm font-semibold transition
                          {{ $active ? 'text-ink-900' : 'text-ink-700 hover:text-ink-900' }}">
                    @if($active)
                        <span class="absolute inset-0 rounded-full bg-ink-900/[0.06]"></span>
                        <span class="absolute -bottom-0.5 left-1/2 h-[3px] w-8 -translate-x-1/2 rounded-full prism-bg"></span>
                    @endif
                    <span class="relative">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @auth
                {{-- Wishlist --}}
                <a href="{{ route('wishlist.index') }}"
                   class="hidden h-10 w-10 items-center justify-center rounded-full border border-ink-200 text-ink-700 transition hover:border-prism-violet hover:text-prism-violet md:inline-flex"
                   title="Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5A4.69 4.69 0 0 0 12 6.073a4.69 4.69 0 0 0-4.313-2.323C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                    </svg>
                </a>

                {{-- Cart --}}
                <a href="{{ route('cart.index') }}"
                   class="relative inline-flex h-10 items-center gap-2 rounded-full border border-ink-200 px-4 text-sm font-semibold text-ink-900 transition hover:border-prism-violet"
                   title="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    @php
                        $cartCount = optional(auth()->user()->cart)->item_count ?? 0;
                    @endphp
                    @if($cartCount > 0)
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full prism-bg px-1 text-[10px] font-bold text-white shadow">
                            {{ $cartCount }}
                        </span>
                    @endif
                    <span class="hidden md:inline">Cart</span>
                </a>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white py-1 pl-1 pr-3 text-sm font-semibold text-ink-900 transition hover:border-prism-violet">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden md:inline">{{ Str::limit(auth()->user()->name, 14) }}</span>
                        <svg class="h-4 w-4 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition.origin.top.right
                         class="absolute right-0 top-12 w-56 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-xl"
                         style="display: none;">
                        <div class="border-b border-ink-100 px-4 py-3">
                            <p class="text-xs text-ink-500">Signed in as</p>
                            <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('orders.index') }}"  class="block px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50">My Orders</a>
                        <a href="{{ route('wishlist.index') }}" class="block px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50">Wishlist</a>
                        <a href="{{ route('trades.index') }}"   class="block px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50">My Trades</a>
                        <a href="{{ route('profile.edit') }}"   class="block px-4 py-2.5 text-sm text-ink-700 hover:bg-ink-50">Profile</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="block bg-ink-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-700">
                                Admin Console →
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                   class="hidden rounded-full px-4 py-2 text-sm font-semibold text-ink-900 hover:text-prism-violet sm:inline-flex">
                    Log in
                </a>
                <x-prism-button :href="route('register')" size="sm">
                    Sign up
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                    </svg>
                </x-prism-button>
            @endauth

            {{-- Mobile menu toggle --}}
            <button @click="open = !open"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-200 lg:hidden">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-transition x-cloak class="border-t border-ink-100 bg-white lg:hidden">
        <nav class="flex flex-col gap-1 px-4 py-4">
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs($link['route']) ? 'bg-ink-900 text-white' : 'text-ink-900 hover:bg-ink-100' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
