<footer class="relative mt-24 overflow-hidden border-t border-ink-200 bg-white">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px prism-bg"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/2 h-96 w-[1200px] -translate-x-1/2 rounded-full bg-gradient-to-r from-prism-pink/15 via-prism-mint/15 to-prism-sky/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-[1400px] px-4 py-14 md:px-8">
        <div class="grid gap-10 md:grid-cols-12">
            <div class="md:col-span-4">
                <x-brand-mark size="lg" :tagline="true" />
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-500">
                    The card tracker for the Pokémon TCG <em>Scarlet &amp; Violet — Prismatic Evolutions</em> expansion. Track market prices, pull digital gacha, bid on real cards, and hang out in the community.
                </p>
            </div>

            <div class="md:col-span-2">
                <h4 class="font-display text-sm font-bold uppercase tracking-widest text-ink-900">Explore</h4>
                <ul class="mt-3 space-y-2 text-sm text-ink-500">
                    <li><a href="{{ route('cards.index') }}" class="hover:text-ink-900">Cards</a></li>
                    <li><a href="{{ route('auctions.index') }}" class="hover:text-ink-900">Auctions</a></li>
                    <li><a href="{{ route('gacha.index') }}" class="hover:text-ink-900">Gacha</a></li>
                    <li><a href="{{ route('forums.index') }}" class="hover:text-ink-900">Forums</a></li>
                    <li><a href="{{ route('shop.index') }}" class="hover:text-ink-900">Merch</a></li>
                </ul>
            </div>

            <div class="md:col-span-2">
                <h4 class="font-display text-sm font-bold uppercase tracking-widest text-ink-900">Account</h4>
                <ul class="mt-3 space-y-2 text-sm text-ink-500">
                    @auth
                        <li><a href="{{ route('profiles.show', auth()->user()) }}" class="hover:text-ink-900">My Profile</a></li>
                        <li><a href="{{ route('collection.index') }}" class="hover:text-ink-900">My Collection</a></li>
                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-ink-900">Chase Cards</a></li>
                        <li><a href="{{ route('orders.index') }}"  class="hover:text-ink-900">Orders</a></li>
                        <li><a href="{{ route('settings.edit') }}" class="hover:text-ink-900">Settings</a></li>
                    @else
                        <li><a href="{{ route('login') }}"    class="hover:text-ink-900">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-ink-900">Sign up</a></li>
                    @endauth
                </ul>
            </div>

            <div class="md:col-span-4">
                <h4 class="font-display text-sm font-bold uppercase tracking-widest text-ink-900">Stay updated</h4>
                <p class="mt-3 text-sm text-ink-500">Drop your email for restocks, new auctions, and limited drops.</p>
                <form class="mt-4 relative" onsubmit="event.preventDefault();">
                    <input type="email" placeholder="trainer@kanto.com"
                           class="w-full rounded-full border-ink-200 py-2.5 pl-4 pr-32 text-sm focus:border-prism-violet focus:ring-prism-violet" />
                    <button type="submit"
                            class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex items-center rounded-full bg-ink-900 px-4 py-1.5 text-xs font-display font-bold text-white hover:bg-prism-violet">
                        Notify me
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-12 border-t border-ink-100 pt-6 text-center text-xs text-ink-500">
            <p>© {{ now()->year }} PokeTrade — A student project. Not affiliated with The Pokémon Company.</p>
        </div>
    </div>
</footer>
