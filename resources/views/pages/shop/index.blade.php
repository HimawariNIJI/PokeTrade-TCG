<x-app-layout
    title="Merch Shop"
    description="Official Pokemon TCG merch: sealed booster boxes, bundles, sleeves, playmats, and plushies. Curated drops, ready to ship.">


<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-50"></div>
    <div class="mx-auto max-w-[1400px] px-4 pb-10 pt-16 md:px-8 md:pt-20">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
                    Custom shop
                </span>
                <h1 class="mt-4 font-display text-5xl font-black tracking-tight md:text-6xl">
                    Boxes, plushies, <span class="prism-text">gear</span>.
                </h1>
                <p class="mt-3 max-w-2xl text-ink-700">Sealed product, sleeves, playmats and merch — admin-curated, image-uploaded, ready to ship.</p>
            </div>
        </div>
    </div>
</section>

<form method="GET" class="border-y border-ink-200 bg-white/90 backdrop-blur sticky top-[68px] z-20">
    <div class="mx-auto flex max-w-[1400px] flex-wrap items-center gap-3 px-4 py-3 md:px-8">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search merch…"
               class="flex-1 min-w-[200px] rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet" />
        <select name="category" aria-label="Filter by category" class="rounded-full border-ink-200 text-sm">
            <option value="">All categories</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" @selected(request('category') === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">Apply</button>
        <a href="{{ route('shop.index') }}" class="text-sm text-ink-500">Reset</a>
    </div>
</form>

<section class="mx-auto max-w-[1400px] px-4 py-10 md:px-8 md:py-14">
    @if($items->isNotEmpty())
        <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            @foreach($items as $item)
                <x-shop-tile :item="$item" />
            @endforeach
        </div>
        <div class="mt-10">{{ $items->links() }}</div>
    @else
        <x-empty-state title="No items yet" message="Admins can add merchandise from the admin panel." />
    @endif
</section>

</x-app-layout>
