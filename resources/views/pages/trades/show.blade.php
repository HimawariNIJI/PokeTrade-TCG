<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-12 md:px-8 md:py-16">
    <a href="{{ route('trades.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back to trades</a>

    <div class="mt-6 rounded-3xl border border-ink-200 bg-white p-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="rounded-full bg-{{ $trade->status === 'pending' ? 'amber' : ($trade->status === 'accepted' ? 'emerald' : 'rose') }}-100 px-3 py-1 text-xs font-bold uppercase tracking-widest text-{{ $trade->status === 'pending' ? 'amber' : ($trade->status === 'accepted' ? 'emerald' : 'rose') }}-700">
                    {{ $trade->status }}
                </span>
                <p class="mt-2 text-sm text-ink-500">Proposed {{ $trade->created_at->diffForHumans() }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-ink-500">From: {{ $trade->sender?->name }}</p>
                <p class="text-xs text-ink-500">To: {{ $trade->receiver?->name }}</p>
            </div>
        </div>

        @if($trade->message)
            <blockquote class="mt-6 rounded-2xl border-l-4 border-prism-violet bg-ink-50 p-5 italic text-ink-700">
                "{{ $trade->message }}"
            </blockquote>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <div>
                <h3 class="mb-3 font-display text-base font-black">Offered</h3>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    @forelse($trade->items->where('side', 'offer') as $ti)
                        <a href="{{ route('cards.show', $ti->card) }}" class="block">
                            <div class="aspect-[245/342] overflow-hidden rounded-xl bg-ink-100">
                                @if($ti->card?->image_small)
                                    <img src="{{ $ti->card->image_small }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <p class="mt-1 line-clamp-1 text-xs font-bold">{{ $ti->card?->name }}</p>
                        </a>
                    @empty
                        <p class="col-span-full text-xs text-ink-500">No offer cards.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h3 class="mb-3 font-display text-base font-black">Requested</h3>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                    @forelse($trade->items->where('side', 'request') as $ti)
                        <a href="{{ route('cards.show', $ti->card) }}" class="block">
                            <div class="aspect-[245/342] overflow-hidden rounded-xl bg-ink-100">
                                @if($ti->card?->image_small)
                                    <img src="{{ $ti->card->image_small }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <p class="mt-1 line-clamp-1 text-xs font-bold">{{ $ti->card?->name }}</p>
                        </a>
                    @empty
                        <p class="col-span-full text-xs text-ink-500">No request cards.</p>
                    @endforelse
                </div>
            </div>
        </div>

        @if($trade->status === 'pending' && $trade->receiver_id === auth()->id())
            <form method="POST" action="{{ route('trades.respond', $trade) }}" class="mt-8 flex flex-wrap gap-3">
                @csrf
                <button type="submit" name="action" value="accept" class="rounded-full bg-emerald-600 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-700">
                    Accept trade
                </button>
                <button type="submit" name="action" value="reject" class="rounded-full border border-rose-300 px-6 py-3 text-sm font-bold text-rose-700 hover:bg-rose-50">
                    Decline
                </button>
            </form>
        @endif
    </div>
</section>

</x-app-layout>
