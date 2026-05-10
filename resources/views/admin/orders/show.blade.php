<x-admin-layout heading="Order {{ $order->code }}" eyebrow="Order detail">

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8 space-y-6">
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h2 class="font-display text-base font-black">Items</h2>
            <ul class="mt-3 divide-y divide-ink-100">
                @foreach($order->items as $item)
                    <li class="flex items-center gap-4 py-3">
                        <span class="inline-flex h-14 w-11 overflow-hidden rounded-md bg-ink-100">
                            @if($item->image_snapshot)
                                <img src="{{ str_starts_with($item->image_snapshot, 'http') ? $item->image_snapshot : asset('storage/' . $item->image_snapshot) }}" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold">{{ $item->name_snapshot }}</p>
                            <p class="text-xs text-ink-500">Qty {{ $item->quantity }} × ${{ number_format((float) $item->price_snapshot, 2) }}</p>
                        </div>
                        <span class="font-mono font-bold">${{ number_format((float) $item->subtotal, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <aside class="lg:col-span-4 space-y-4">
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h3 class="font-display text-sm font-black">Customer</h3>
            <p class="mt-2 text-sm">{{ $order->user?->name }}</p>
            <p class="text-xs text-ink-500">{{ $order->user?->email }}</p>
        </div>

        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h3 class="font-display text-sm font-black">Update status</h3>
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="mt-3 space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="w-full rounded-xl border-ink-200 text-sm">
                    @foreach(\App\Models\Order::STATUSES as $st)
                        <option value="{{ $st }}" @selected($order->status === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="w-full rounded-xl border-ink-200 text-sm">
                    @foreach(\App\Models\Order::PAYMENT_STATUSES as $ps)
                        <option value="{{ $ps }}" @selected($order->payment_status === $ps)>Payment: {{ ucfirst($ps) }}</option>
                    @endforeach
                </select>
                <x-prism-button type="submit" size="sm" class="w-full">Save status</x-prism-button>
            </form>
        </div>

        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h3 class="font-display text-sm font-black">Total</h3>
            <p class="mt-2 font-display text-3xl font-black prism-text">${{ number_format((float) $order->total, 2) }}</p>
            <p class="text-xs text-ink-500">{{ $order->payment_method ?? '—' }}</p>
        </div>
    </aside>
</div>

</x-admin-layout>
