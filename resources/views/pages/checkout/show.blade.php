<x-app-layout>

    <section class="mx-auto max-w-[1400px] px-4 py-12 md:px-8 md:py-16">
        <div class="mb-10">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Step 2 of 2
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                <span class="prism-text">Checkout</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">Complete your shipping details to place the order.</p>

            {{-- Hardcoded physical-delivery banner. Reinforces what the shipping form
                 is actually for: a real package coming to the trainer's house. --}}
            <div data-test="checkout-physical-banner"
                 class="mt-5 flex items-start gap-3 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 px-4 py-3 text-sm text-ink-900">
                <span class="text-lg leading-none">📦</span>
                <p>
                    <span class="font-display font-black text-prism-violet">Real items, real address.</span>
                    Every product in your cart is physical — sealed merch and actual Pokémon TCG cards.
                    We'll pack and ship them to the address you enter below, straight to your home.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('checkout.place') }}" class="grid gap-8 lg:grid-cols-12">
            @csrf

            {{-- LEFT: Address + Payment --}}
            <div class="space-y-6 lg:col-span-7">
                <fieldset class="rounded-3xl border border-ink-200 bg-white p-6">
                    <legend class="px-2 font-display text-lg font-black text-ink-900">Shipping details</legend>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Full name</span>
                            <input type="text" name="shipping_name" required
                                value="{{ old('shipping_name', $defaults['shipping_name']) }}"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet" />
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Phone</span>
                            <input type="tel" name="shipping_phone" required
                                value="{{ old('shipping_phone', $defaults['shipping_phone']) }}"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet" />
                            @error('shipping_phone')
                                <p class="text-sm text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Address</span>
                            <input type="text" name="shipping_address" required
                                value="{{ old('shipping_address', $defaults['shipping_address']) }}"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet" />
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">City</span>
                            <input type="text" name="shipping_city" required
                                value="{{ old('shipping_city', $defaults['shipping_city']) }}"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet" />
                        </label>
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Postal code</span>
                            <input type="text" name="shipping_postal_code" required
                                value="{{ old('shipping_postal_code', $defaults['shipping_postal_code']) }}"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet" />
                        </label>
                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Notes
                                (optional)</span>
                            <textarea name="notes" rows="3"
                                class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="rounded-3xl border border-ink-200 bg-white p-6">
                    <legend class="px-2 font-display text-lg font-black text-ink-900">Payment method</legend>
                    <p class="mb-3 text-xs text-ink-500">Sandbox-only. No real charges.</p>
                    <div class="grid gap-3">
                        @foreach ([['value' => 'midtrans', 'label' => 'Midtrans (sandbox)', 'sub' => 'GoPay, OVO, BCA VA']] as $i => $pm)
                            <label
                                class="cursor-pointer rounded-2xl border-2 border-ink-200 p-4 hover:border-prism-violet [&:has(input:checked)]:border-ink-900 [&:has(input:checked)]:bg-ink-900 [&:has(input:checked)]:text-white">
                                <input type="radio" name="payment_method" value="{{ $pm['value'] }}" class="sr-only"
                                    {{ $i === 0 ? 'checked' : '' }}>
                                <p class="font-display text-sm font-black">{{ $pm['label'] }}</p>
                                <p class="mt-0.5 text-[11px] opacity-70">{{ $pm['sub'] }}</p>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            {{-- RIGHT: Order summary --}}
            <aside class="lg:col-span-5">
                <div class="sticky top-24 space-y-3 rounded-3xl border border-ink-200 bg-white p-6">
                    <h2 class="font-display text-lg font-black text-ink-900">Your order</h2>
                    @php $items = optional($cart)->items ?? collect(); @endphp
                    @if ($items->isEmpty())
                        <p class="text-sm text-ink-500">Your cart is empty.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($items as $line)
                                @php $it = $line->itemable; @endphp
                                <li class="flex items-center gap-3 py-3">
                                    <span class="inline-flex h-12 w-10 shrink-0 overflow-hidden rounded-md bg-ink-100">
                                        @if ($it instanceof \App\Models\Card && $it->image)
                                            <img src="{{ $it->image }}" alt=""
                                                class="h-full w-full object-cover">
                                        @elseif($it instanceof \App\Models\ShopItem && $it->image)
                                            <img src="{{ $it->image }}" alt=""
                                                class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-1 text-sm font-semibold">{{ $it->name ?? '—' }}</p>
                                        <p class="text-xs text-ink-500">Qty {{ $line->quantity }}</p>
                                    </div>
                                    <span class="font-mono text-sm font-bold">@idr($line->price_snapshot * $line->quantity)</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="space-y-1 border-t border-ink-100 pt-4 text-sm">
                        <div class="flex justify-between"><span class="text-ink-500">Subtotal</span><span
                                class="font-mono">@idr($cart->subtotal ?? 0)</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Shipping</span><span
                                class="font-mono">@idr(25000)</span></div>
                        <div class="flex justify-between"><span class="text-ink-500">Tax (est.)</span><span
                                class="font-mono">@idr(($cart->subtotal ?? 0) * 0.1)</span></div>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-ink-100 pt-4">
                        <span class="font-display text-base font-bold">Total</span>
                        <span class="font-display text-2xl font-black prism-text">
                            @idr(($cart->subtotal ?? 0) * 1.1 + 25000)
                        </span>
                    </div>

                    <x-prism-button type="submit" size="lg" class="w-full">
                        Place order →
                    </x-prism-button>
                    <a href="{{ route('cart.index') }}"
                        class="block text-center text-xs text-ink-500 hover:text-ink-900">Back to cart</a>
                </div>
            </aside>
        </form>
    </section>

</x-app-layout>
