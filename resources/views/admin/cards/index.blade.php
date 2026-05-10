<x-admin-layout heading="Cards" eyebrow="Catalog management">
    <x-slot:actions>
        <x-prism-button :href="route('admin.cards.create')" size="sm">+ New card</x-prism-button>
    </x-slot:actions>

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
                        <td class="px-4 py-3 text-right font-mono">${{ number_format((float) $c->price, 2) }}</td>
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
