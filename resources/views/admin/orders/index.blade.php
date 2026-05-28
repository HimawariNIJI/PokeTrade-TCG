<x-admin-layout heading="Orders" eyebrow="Transactions">

    <form method="GET" class="mb-5 flex gap-2">
        <select name="status" class="rounded-full border-ink-200 text-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\Order::STATUSES as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($orders as $o)
                    <tr class="hover:bg-ink-50">
                        <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $o) }}"
                                class="font-mono text-xs hover:text-prism-violet">{{ $o->code }}</a></td>
                        <td class="px-4 py-3">{{ $o->user?->name }}</td>
                        <td class="px-4 py-3"><span
                                class="rounded-full bg-{{ $o->status_color }}-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-{{ $o->status_color }}-700">{{ $o->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $o->payment_status }}</td>
                        <td class="px-4 py-3 text-right font-mono">@idr($o->total)</td>
                        <td class="px-4 py-3 text-xs text-ink-500">
                            @if ($o->status === 'delivered' && $o->delivered_at)
                                {{ $o->delivered_at->format('M d, Y H:i') }}
                            @elseif($o->status === 'shipped' && $o->shipped_at)
                                {{ $o->shipped_at->format('M d, Y H:i') }}
                            @elseif($o->payment_status === 'paid' && $o->paid_at)
                                {{ $o->paid_at->format('M d, Y H:i') }}
                            @else
                                {{ $o->created_at->format('M d, Y H:i') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-ink-500">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>

</x-admin-layout>
