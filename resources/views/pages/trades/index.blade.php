<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-16 md:px-8">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Player ↔ Player
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                <span class="prism-text">Trades</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">Propose, accept, decline. Settle without ever opening your wallet.</p>
        </div>
        <x-prism-button :href="route('trades.create')" size="md">+ Propose new trade</x-prism-button>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- INCOMING --}}
        <div>
            <h2 class="mb-4 font-display text-lg font-black">Incoming proposals</h2>
            @if($received->isEmpty())
                <x-empty-state icon="←" title="No incoming trades" message="Once another trainer proposes a trade, it'll show up here." />
            @else
                <div class="space-y-3">
                    @foreach($received as $t)
                        <a href="{{ route('trades.show', $t) }}" class="group gleam relative block rounded-2xl border border-ink-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-prism-violet hover:shadow-lg duration-300 ease-[cubic-bezier(.22,1,.36,1)]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold">{{ $t->sender?->name }} wants to trade</p>
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-amber-700">{{ $t->status }}</span>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">{{ $t->items_count ?? $t->items->count() }} cards · {{ $t->created_at->diffForHumans() }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- OUTGOING --}}
        <div>
            <h2 class="mb-4 font-display text-lg font-black">Sent proposals</h2>
            @if($sent->isEmpty())
                <x-empty-state icon="→" title="No sent trades" message="Propose a trade to another trainer to start." />
            @else
                <div class="space-y-3">
                    @foreach($sent as $t)
                        <a href="{{ route('trades.show', $t) }}" class="group gleam relative block rounded-2xl border border-ink-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-prism-violet hover:shadow-lg duration-300 ease-[cubic-bezier(.22,1,.36,1)]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold">To {{ $t->receiver?->name }}</p>
                                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-ink-700">{{ $t->status }}</span>
                            </div>
                            <p class="mt-1 text-xs text-ink-500">{{ $t->items->count() }} cards · {{ $t->created_at->diffForHumans() }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

</x-app-layout>
