<x-app-layout>

    <section class="mx-auto max-w-[1200px] px-4 py-16 md:px-8">
        <div class="mb-10">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Receipts &amp; tracking
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                My <span class="prism-text">orders</span>.
            </h1>
        </div>

        {{-- Filter Section --}}
        <div class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}"
                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition {{ $selectedStatus === '' ? 'bg-ink-900 text-white' : 'border border-ink-200 text-ink-700 hover:border-prism-violet' }}">
                All Orders
            </a>
            @foreach ($statuses as $status)
                <a href="{{ route('orders.index', ['status' => $status]) }}"
                    class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold capitalize transition {{ $selectedStatus === $status ? 'prism-bg text-white' : 'border border-ink-200 text-ink-700 hover:border-prism-violet' }}">
                    {{ $status }}
                </a>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <x-empty-state icon="◆"
                title="{{ $selectedStatus ? 'No ' . $selectedStatus . ' orders' : 'No orders yet' }}"
                message="{{ $selectedStatus ? 'No orders match this status. Try another filter.' : 'When you place an order, it\'ll show up here with tracking and a digital invoice.' }}">
                <x-prism-button :href="route('cards.index')" size="md">Browse Items</x-prism-button>
            </x-empty-state>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <div
                        class="group relative rounded-2xl border border-ink-200 bg-white overflow-hidden transition hover:-translate-y-0.5 hover:border-prism-violet hover:shadow-lg duration-300 ease-[cubic-bezier(.22,1,.36,1)]">
                        {{-- Order Summary (Clickable) --}}
                        <a href="{{ route('orders.show', $order) }}"
                            class="gleam relative flex flex-col gap-3 p-5 transition md:flex-row md:items-center hover:bg-ink-50">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest
                                    bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                        {{ $order->status }}
                                    </span>
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest border
                                    {{ $order->payment_status === 'paid' ? 'border-emerald-200 text-emerald-700' : 'border-amber-200 text-amber-700' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </div>
                                <p class="mt-1.5 font-display text-base font-black text-ink-900">
                                    {{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}
                                    <span class="font-normal text-ink-500"> · placed
                                        {{ $order->created_at->diffForHumans() }}</span>
                                </p>
                                <p class="text-xs font-bold text-prism-violet mt-1">
                                    @php
                                        $checkstatus = $order->status;
                                    @endphp
                                    @if ($checkstatus === 'paid' || $checkstatus === 'shipped' || $checkstatus === 'delivered')
                                        {{ ucfirst($checkstatus) }} at
                                        {{ $order->{$checkstatus . '_at'}?->format('M d, Y') ?? 'Unknown Date' }}
                                    @elseif ($checkstatus === 'cancelled')
                                        Cancelled at
                                        {{ $order->updated_at->format('M d, Y') ?? 'Unknown Date' }}
                                    @else
                                        Order created at {{ $order->created_at->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-baseline gap-4">
                                <div class="text-right">
                                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Total</p>
                                    <p class="font-display text-2xl font-black prism-text">@idr($order->total)</p>
                                </div>
                                <svg class="h-5 w-5 text-ink-300 transition group-hover:translate-x-1 group-hover:text-prism-violet"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6" />
                                </svg>
                            </div>
                        </a>

                        {{-- Items Dropdown (Appears on Hover) --}}
                        <div
                            class="max-h-0 overflow-hidden bg-ink-50 border-t border-ink-100 transition-all duration-1000 group-hover:max-h-96">
                            <div class="px-5 py-4 space-y-3">
                                <p class="text-xs font-bold uppercase tracking-widest text-ink-600 mb-3">Items in order:
                                </p>
                                @foreach ($order->items as $item)
                                    <div class="flex gap-3 pb-3 border-b border-ink-200 last:border-b-0 last:pb-0">
                                        {{-- Item Image --}}
                                        <div
                                            class="h-14 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-white border border-ink-200">
                                            @if ($item->image_snapshot)
                                                <img src="{{ str_starts_with($item->image_snapshot, 'http') ? $item->image_snapshot : asset('storage/' . $item->image_snapshot) }}"
                                                    alt="{{ $item->name_snapshot }}"
                                                    class="h-full w-full object-cover">
                                            @else
                                                <div
                                                    class="flex items-center justify-center h-full w-full bg-gradient-to-br from-ink-100 to-ink-200">
                                                    <span class="text-[10px] font-bold text-ink-400">N/A</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Item Details --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-ink-900 line-clamp-1">
                                                {{ $item->name_snapshot }}</p>
                                            <p class="text-xs text-ink-600 mt-1">
                                                <span class="font-semibold">Qty {{ $item->quantity }}</span> @
                                                <span class="font-mono font-semibold">@idr($item->price_snapshot)</span>
                                            </p>
                                            <p class="text-xs font-bold text-prism-violet mt-1">
                                                @idr($item->subtotal)
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </section>

</x-app-layout>
