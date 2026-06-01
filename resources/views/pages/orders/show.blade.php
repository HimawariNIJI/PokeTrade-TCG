<x-app-layout>

    <section class="mx-auto max-w-[1100px] px-4 py-12 md:px-8 md:py-16">
        <a href="{{ route('orders.index') }}"
            class="inline-flex items-center gap-2 text-xs font-semibold text-ink-500 hover:text-ink-900">
            ← Back to orders
        </a>

        <div class="mt-6 grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <div class="rounded-3xl border border-ink-200 bg-white p-7">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs font-bold tracking-widest text-ink-500">{{ $order->code }}</p>
                            <h1 class="mt-1 font-display text-3xl font-black tracking-tight">Order details</h1>
                            <p class="mt-1 text-sm text-ink-500">Placed
                                {{ $order->created_at->format('M j, Y · g:i A') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-widest
                            bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                {{ $order->status }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-widest border
                            {{ $order->payment_status === 'paid' ? 'border-emerald-200 text-emerald-700' : 'border-amber-200 text-amber-700' }}">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                    </div>

                    {{-- Status timeline --}}
                    <ol class="mt-8 grid gap-2 md:grid-cols-4">
                        @foreach (['pending' => 'Order placed', 'paid' => 'Payment confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $key => $label)
                            @php
                                $current = array_search($order->status, ['pending', 'paid', 'shipped', 'delivered']);
                                $i = array_search($key, ['pending', 'paid', 'shipped', 'delivered']);
                                $reached = $current !== false && $i <= $current;
                            @endphp
                            <li
                                class="relative flex items-center gap-3 rounded-2xl border {{ $reached ? 'border-prism-violet bg-white' : 'border-ink-200 bg-ink-50' }} px-4 py-3">
                                <span
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $reached ? 'prism-bg text-white' : 'bg-ink-200 text-ink-500' }}">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="text-xs font-bold {{ $reached ? 'text-ink-900' : 'text-ink-500' }}">
                                    {{ $label }}
                                </span>
                            </li>
                        @endforeach
                    </ol>

                    {{-- Hardcoded physical-delivery callout. Auction wins and merch
                         orders both flow through this page, and both are real items being
                         mailed — make sure the trainer can never confuse this with a
                         digital gacha pull. --}}
                    <div data-test="order-physical-callout"
                         class="mt-8 flex items-start gap-3 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 px-4 py-3 text-sm text-ink-900">
                        <span class="text-lg leading-none">📦</span>
                        <p>
                            <span class="font-display font-black text-prism-violet">Physical delivery.</span>
                            The items below are real, physical products. We'll ship them to the
                            shipping address shown on this order — no digital codes,
                            no in-app collectibles, just the actual cards and merch arriving at your door.
                        </p>
                    </div>

                    {{-- Items --}}
                    <h2 class="mt-10 font-display text-lg font-black">Items</h2>
                    <ul class="mt-3 divide-y divide-ink-100">
                        @foreach ($order->items as $item)
                            <li class="flex items-center gap-4 py-4">
                                <span class="inline-flex h-16 w-12 shrink-0 overflow-hidden rounded-md bg-ink-100">
                                    @if ($item->image_snapshot)
                                        <img src="{{ str_starts_with($item->image_snapshot, 'http') ? $item->image_snapshot : asset('storage/' . $item->image_snapshot) }}"
                                            alt="" class="h-full w-full object-cover">
                                    @endif
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-1 text-sm font-bold">{{ $item->name_snapshot }}</p>
                                    <p class="text-xs text-ink-500">Qty {{ $item->quantity }} × @idr($item->price_snapshot)</p>
                                </div>
                                <span class="font-mono text-sm font-bold">@idr($item->subtotal)</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    @if ($order->status === 'pending' && $order->payment_status === 'unpaid' && $order->user_id == auth()->id())
                        <div class="mt-4 flex flex-wrap gap-3" x-data="{ cancelModalOpen: false }">

                            <a href="{{ route('payment_show', $order->code) }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-black px-6 py-3 text-sm font-bold text-white transition-all duration-300 hover:bg-gradient-to-r hover:from-prism-violet hover:to-prism-pink hover:shadow-xl">
                                💳 Pay Now
                            </a>

                            <button type="button"
                                @click="cancelModalOpen = true"
                                class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-6 py-3 text-sm font-bold text-red-600 transition-all duration-300 hover:bg-red-600 hover:text-white hover:shadow-xl">
                                ✕ Cancel Order
                            </button>

                            <!-- Confirmation Modal -->
                            <div x-show="cancelModalOpen" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 @click.self="cancelModalOpen = false"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                                 style="display: none;">
                                
                                <div x-show="cancelModalOpen"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="w-full max-w-md rounded-3xl border border-ink-200 bg-white p-6 shadow-2xl">
                                    
                                    <div class="text-center mb-6">
                                        <h3 class="font-display text-xl font-black text-ink-900">Cancel Order?</h3>
                                        <p class="mt-2 text-sm text-ink-600">
                                            This action cannot be undone. Your order will be cancelled and stock will be returned.
                                        </p>
                                    </div>

                                    <dl class="mb-6 space-y-2 rounded-2xl bg-red-50 p-4 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="text-ink-700">Order Code:</dt>
                                            <dd class="font-mono font-semibold text-ink-900">{{ $order->code }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-red-200 pt-2">
                                            <dt class="text-ink-700">Total Amount:</dt>
                                            <dd class="font-display font-black text-red-600">@idr($order->total)</dd>
                                        </div>
                                    </dl>

                                    <div class="flex gap-3">
                                        <button type="button"
                                            @click="cancelModalOpen = false"
                                            class="flex-1 rounded-2xl border border-ink-200 px-4 py-3 text-sm font-bold text-ink-900 transition-all duration-300 hover:bg-ink-50">
                                            Keep Order
                                        </button>

                                        <form action="{{ route('orders_cancel', $order->code) }}" method="POST" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            
                                            <button type="submit"
                                                class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition-all duration-300 hover:bg-red-700 hover:shadow-lg">
                                                Cancel Order
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>
            </div>

            <aside class="lg:col-span-4 space-y-4">
                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h3 class="font-display text-base font-black">Total</h3>
                    <dl class="mt-3 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Subtotal</dt>
                            <dd class="font-mono">@idr($order->subtotal)</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Shipping</dt>
                            <dd class="font-mono">@idr($order->shipping_fee)</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Tax</dt>
                            <dd class="font-mono">@idr($order->tax)</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex items-baseline justify-between border-t border-ink-100 pt-3">
                        <span class="font-display text-base font-bold">Grand total</span>
                        <span class="font-display text-2xl font-black prism-text">@idr($order->total)</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h3 class="font-display text-base font-black">Shipping to</h3>
                    <p class="mt-2 text-sm">
                        {{ $order->shipping_name }}<br />
                        {{ $order->shipping_address }}<br />
                        {{ $order->shipping_city }} {{ $order->shipping_postal_code }}<br />
                        <span class="text-ink-500">{{ $order->shipping_phone }}</span>
                    </p>
                </div>

                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h3 class="font-display text-base font-black">Payment</h3>
                    <p class="mt-2 text-sm capitalize">
                        {{ $order->payment_method ?? 'Unknown' }}
                    </p>
                </div>
            </aside>
        </div>
    </section>

</x-app-layout>
