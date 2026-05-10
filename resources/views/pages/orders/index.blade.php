<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-16 md:px-8">
    <div class="mb-10">
        <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
            Receipts &amp; tracking
        </span>
        <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
            My <span class="prism-text">orders</span>.
        </h1>
    </div>

    @if($orders->isEmpty())
        <x-empty-state
            icon="◆"
            title="No orders yet"
            message="When you place an order, it'll show up here with tracking and a digital invoice.">
            <x-prism-button :href="route('cards.index')" size="md">Browse cards</x-prism-button>
        </x-empty-state>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="group flex flex-col gap-3 rounded-2xl border border-ink-200 bg-white p-5 transition hover:border-prism-violet hover:shadow-lg md:flex-row md:items-center">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold tracking-widest text-ink-700">{{ $order->code }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest
                                bg-{{ $order->status_color }}-100 text-{{ $order->status_color }}-700">
                                {{ $order->status }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest border
                                {{ $order->payment_status === 'paid' ? 'border-emerald-200 text-emerald-700' : 'border-amber-200 text-amber-700' }}">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                        <p class="mt-1.5 font-display text-base font-black text-ink-900">
                            {{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}
                            <span class="font-normal text-ink-500"> · placed {{ $order->created_at->diffForHumans() }}</span>
                        </p>
                    </div>
                    <div class="flex items-baseline gap-4">
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-widest text-ink-500">Total</p>
                            <p class="font-display text-2xl font-black prism-text">${{ number_format((float) $order->total, 2) }}</p>
                        </div>
                        <svg class="h-5 w-5 text-ink-300 transition group-hover:translate-x-1 group-hover:text-prism-violet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>
    @endif
</section>

</x-app-layout>
