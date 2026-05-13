<x-app-layout>

<section class="mx-auto max-w-[1400px] px-4 py-16 md:px-8">
    <div class="mb-10 flex items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Your binder bag
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                Shopping <span class="prism-text">cart</span>.
            </h1>
        </div>
    </div>

    @php $items = optional($cart)->items ?? collect(); @endphp

    @if($items->isEmpty())
        <x-empty-state
            icon="◇"
            title="Your cart is empty"
            message="Browse the card shop or pick up some boosters from the merch shop to get started.">
            <div class="flex flex-wrap justify-center gap-3">
                <x-prism-button :href="route('shop.index')" size="md">Shop Here!</x-prism-button>
            </div>
        </x-empty-state>
    @else
        <div class="grid gap-8 lg:grid-cols-12">
            {{-- LEFT: Items --}}
            <div class="space-y-3 lg:col-span-8">
                @foreach($items as $line)
                    @php
                        $it = $line->itemable;
                        $isCard = $it instanceof \App\Models\Card;
                        $name = $it?->name ?? 'Unknown item';
                        $img = $isCard ? $it?->image_small : ($it?->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($it->image) : null);
                        $href = $isCard ? route('cards.show', $it) : route('shop.show', $it);
                    @endphp
                    <article class="flex items-center gap-4 rounded-2xl border border-ink-200 bg-white p-4">
                        <a href="{{ $href }}" class="shrink-0">
                            <div class="h-24 w-20 overflow-hidden rounded-xl bg-ink-100">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $name }}" class="h-full w-full object-cover" />
                                @endif
                            </div>
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ $href }}" class="line-clamp-1 font-display text-base font-black text-ink-900 hover:text-prism-violet">
                                {{ $name }}
                            </a>
                            <p class="mt-0.5 text-xs text-ink-500">
                                {{ $isCard ? 'Pokémon TCG card' : 'Merch · ' . ucfirst($it?->category ?? '') }}
                            </p>
                            <p class="mt-2 font-display text-lg font-bold text-ink-900">
                                @idr($line->price_snapshot)
                            </p>
                        </div>

                        @if($line->itemable_type === 'App\\Models\\ShopItem')
                        <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="item_type" value="shop_item">
                            <input type="hidden" name="item_id" value="{{ $line->itemable_id }}">
                            <button type="button" class="h-8 w-8 rounded-full border border-ink-200 text-ink-700 hover:border-ink-900" onclick="const input = this.nextElementSibling; if(input.value >= 1) { input.value--; this.form.submit(); }">ー</button>
                            <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" max="99"
                                   class="w-16 rounded-full border-ink-200 text-center text-sm focus:border-prism-violet focus:ring-prism-violet" />
                            <button type="button" class="h-8 w-8 rounded-full border border-ink-200 text-ink-700 hover:border-ink-900" onclick=" const input = this.previousElementSibling; input.value++; this.form.submit(); " > ＋ </button>
                        </form>

                        <form method="POST" action="{{ route('cart.remove') }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="item_type" value="shop_item">
                            <input type="hidden" name="item_id" value="{{ $line->itemable_id }}">
                            <button type="submit" class="text-ink-300 hover:text-rose-600" title="Remove">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </form>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- RIGHT: Summary --}}
            <aside class="lg:col-span-4">
                <div class="sticky top-24 space-y-4 rounded-3xl border border-ink-200 bg-white p-6">
                    <h2 class="font-display text-xl font-black text-ink-900">Order summary</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-mono font-semibold">@idr(($cart->subtotal ?? 0))</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-500">Shipping</dt><dd class="font-mono text-ink-500">calculated at checkout</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-500">Tax</dt><dd class="font-mono text-ink-500">—</dd></div>
                    </dl>
                    <div class="border-t border-ink-100 pt-4">
                        <div class="flex items-baseline justify-between">
                            <span class="font-display text-base font-bold">Total</span>
                            <span class="font-display text-2xl font-black prism-text">@idr(($cart->subtotal ?? 0))</span>
                        </div>
                    </div>
                    <x-prism-button :href="route('checkout.show')" size="lg" class="w-full">
                        Proceed to checkout →
                    </x-prism-button>
                    <a href="{{ route('cards.index') }}" class="block text-center text-xs text-ink-500 hover:text-ink-900">Continue shopping</a>
                </div>
            </aside>
        </div>
    @endif
</section>

</x-app-layout>
