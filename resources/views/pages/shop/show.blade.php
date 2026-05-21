<x-app-layout>

    <section class="mx-auto max-w-[1200px] px-4 py-12 md:px-8 md:py-16">
        <nav class="mb-6 text-xs text-ink-500">
            <a href="{{ route('shop.index') }}" class="hover:text-ink-900">Merch</a>
            <span class="mx-2">/</span>
            <span class="text-ink-900">{{ $item->name }}</span>
        </nav>

        <div class="grid gap-12 lg:grid-cols-2">
            <div class="relative">
                <div class="absolute -inset-6 -z-10 rounded-[2.5rem] prism-bg opacity-30 blur-3xl"></div>
                <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
                    <div class="aspect-square bg-gradient-to-br from-ink-50 to-ink-100">
                        @if ($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                class="h-full w-full object-cover" />
                        @else
                            <div class="relative flex h-full items-center justify-center halftone">
                                <div class="absolute inset-0 prism-bg opacity-20"></div>
                                <span
                                    class="relative font-display text-6xl font-black text-ink-700/40">{{ strtoupper(substr($item->category, 0, 3)) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                @if (auth()->check())
                    @php
                    $stockinCart =
                        optional(auth()->user()->cart)
                            ->items()
                            ->where('itemable_id', $item->id)
                            ->first()?->quantity ?? 0;
                    @endphp
                @else
                    @php $stockinCart = 0; @endphp
                @endif
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-ink-900 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-white">
                    {{ $item->category }}
                </span>
                <h1 class="mt-3 font-display text-5xl font-black tracking-tight">{{ $item->name }}</h1>
                <p class="mt-4 text-ink-700">{{ $item->description }}</p>

                <div class="mt-8 rounded-3xl border border-ink-200 bg-white p-6">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Price</p>
                    <p class="mt-1 font-display text-4xl font-black">@idr($item->price)</p>
                    <span
                        class="prism-text block font-bold">{{ $item->stock > 0 ? "$item->stock in stock" : 'Sold out' }}</span>
                    @if ($stockinCart != 0)
                        <p class="mt-1 text-xs text-ink-500">
                            {{ $stockinCart }} in your cart
                        </p>
                    @endif

                    <form method="POST" action="{{ route('cart.add') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="item_type" value="shop_item">
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <div class="flex gap-3">
                            <input type="number" name="quantity" value="1" min="1"
                                max="{{ max(1, $item->stock - $stockinCart) }}"
                                class="w-20 rounded-full border-ink-200 text-center text-sm focus:border-prism-violet focus:ring-prism-violet">
                            <x-prism-button type="submit" size="lg" :disabled="$item->stock <= 0">
                                {{ $item->stock > 0 ? 'Add to cart' : 'Sold out' }}
                            </x-prism-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
