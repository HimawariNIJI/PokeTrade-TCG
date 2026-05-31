<x-admin-layout heading="Cards" eyebrow="Catalog management">
    <x-slot:actions>
        <div class="group relative inline-block">
            {{-- Submitted via fetch() instead of a native form POST so the page
                 doesn't enter a "loading" state — that's what was pausing the
                 Arceus GIF and making it look frozen during the wait. --}}
            <form id="refresh-api-form" method="POST" action="{{ route('admin.cards.refresh') }}" class="inline-block">
                @csrf
                <button type="submit"
                        class="rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-bold text-ink-900 hover:border-prism-violet hover:text-prism-violet disabled:cursor-not-allowed disabled:opacity-60">
                    ↻ Refresh from API
                </button>
            </form>
            <div role="tooltip"
                 class="pointer-events-none absolute right-0 top-full z-30 mt-2 w-64 origin-top-right scale-95 rounded-2xl border border-ink-200 bg-white p-3 text-left text-xs leading-relaxed text-ink-700 opacity-0 shadow-xl transition duration-150 group-hover:scale-100 group-hover:opacity-100">
                Re-pulls the Standard catalogue from <span class="font-mono text-ink-900">pokemontcg.io</span> and refreshes every card's market price.
                <span class="mt-1 block text-ink-500">Takes about 5–10 seconds.</span>
            </div>
        </div>
    </x-slot:actions>

    <div id="refresh-api-overlay"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-ink-900/60 px-4 backdrop-blur-sm"
         aria-live="polite" aria-busy="true">
        <div class="relative w-full max-w-sm rounded-3xl border border-ink-200 bg-white p-8 text-center shadow-2xl">
            {{-- Close button — visible always; only really matters in the error state --}}
            <button type="button" id="refresh-api-close"
                    class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-ink-500 hover:bg-ink-100 hover:text-ink-900"
                    aria-label="Close">
                ✕
            </button>
            <img id="refresh-api-image" src="{{ asset('images/arceus-loading.gif') }}" alt=""
                 class="mx-auto mb-5 h-20 w-20" style="image-rendering: pixelated;">
            <h2 id="refresh-api-title" class="font-display text-xl font-bold text-ink-900">Refreshing from pokemontcg.io…</h2>
            <p id="refresh-api-message" class="mt-2 text-sm text-ink-600">
                Re-pulling the Standard catalogue and updating every card's market price.
                This takes a few seconds — please don't close this tab.
            </p>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('refresh-api-form');
            const overlay = document.getElementById('refresh-api-overlay');
            const title = document.getElementById('refresh-api-title');
            const message = document.getElementById('refresh-api-message');
            const image = document.getElementById('refresh-api-image');
            const closeBtn = document.getElementById('refresh-api-close');
            const button = form.querySelector('button');

            const defaultTitle = title.textContent;
            const defaultMessage = message.textContent;

            function showOverlay() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
            function hideOverlay() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                title.textContent = defaultTitle;
                message.textContent = defaultMessage;
                message.classList.remove('text-rose-600');
                image.style.display = '';
                button.disabled = false;
            }
            function showError(text) {
                title.textContent = 'Refresh failed';
                message.textContent = text;
                message.classList.add('text-rose-600');
                image.style.display = 'none';
                button.disabled = false;
            }

            closeBtn.addEventListener('click', hideOverlay);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                showOverlay();
                button.disabled = true;

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                        signal: AbortSignal.timeout(90000),
                    });
                    if (! res.ok) throw new Error(`HTTP ${res.status}`);
                    window.location.reload();
                } catch (err) {
                    const detail = err.name === 'TimeoutError'
                        ? 'the request took longer than 90 seconds (pokemontcg.io may be slow right now)'
                        : err.message;
                    showError('Refresh failed: ' + detail + '. Close this and try again.');
                }
            });
        })();
    </script>

    @if (session('status'))
        <div class="mb-4 rounded-2xl border border-ink-200 bg-white p-4 text-sm text-ink-900">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" class="mb-5 flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name…" class="flex-1 rounded-full border-ink-200 text-sm">
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">Search</button>
    </form>

    <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Card</th>
                    <th class="px-4 py-3">Rarity</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Stock</th>
                    <th class="px-4 py-3 text-center">Featured</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach($cards as $c)
                    <tr class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-mono text-xs text-ink-500">{{ $c->number }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-12 w-9 overflow-hidden rounded-md bg-ink-100">
                                    @if($c->image_small)<img src="{{ $c->image_small }}" class="h-full w-full object-cover">@endif
                                </span>
                                <div>
                                    <p class="font-bold">{{ $c->name }}</p>
                                    <p class="font-mono text-[10px] text-ink-500">{{ $c->api_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $c->rarity ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @foreach(($c->types ?? []) as $t) <x-type-chip :type="$t" size="sm" /> @endforeach
                        </td>
                        <td class="px-4 py-3 text-right font-mono">@idr($c->price)</td>
                        <td class="px-4 py-3 text-right font-mono {{ $c->stock <= 0 ? 'text-rose-600' : '' }}">{{ $c->stock }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($c->featured) <span class="inline-flex h-6 w-6 items-center justify-center rounded-full prism-bg text-[10px] text-white">★</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.cards.edit', $c) }}" class="text-xs font-semibold text-ink-700 hover:text-prism-violet">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $cards->links() }}</div>
</x-admin-layout>
