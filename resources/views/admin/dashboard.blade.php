<x-admin-layout heading="Dashboard" eyebrow="Overview">

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ([['label' => 'Cards in catalog', 'value' => $stats['cards'], 'sub' => 'from API + manual'], ['label' => 'Shop items', 'value' => $stats['shop_items'], 'sub' => 'merch & bundles'], ['label' => 'Customers', 'value' => $stats['customers'], 'sub' => 'registered users'], ['label' => 'Total orders', 'value' => $stats['orders'], 'sub' => $stats['pending'] . ' pending']] as $s)
            <div class="rounded-3xl border border-ink-200 bg-white p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">{{ $s['label'] }}</p>
                <p class="mt-2 font-display text-3xl font-black">{{ $s['value'] }}</p>
                <p class="mt-1 text-xs text-ink-500">{{ $s['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- REVENUE --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-2">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Revenue (paid orders)</p>
                    <p class="mt-2 font-display text-4xl font-black prism-text">@idr($stats['revenue'])</p>
                </div>
                <p class="text-xs text-ink-500">Last 6 months</p>
            </div>

            {{-- Tiny bar chart placeholder --}}
            @php
                $maxAmount = 0;
                foreach ($monthlyRevenue as $item) {
                    $maxAmount = max($maxAmount, $item['amount'] ?? 0);
                }

                $maxAmount = $maxAmount > 0 ? $maxAmount : 1;

                $maxChartHeight = 100;
            @endphp

            <div class="mt-6 grid grid-cols-6 items-end gap-2">
                @foreach ($monthlyRevenue as $m)
                    @php
                        $amount = $m['amount'] ?? 0;

                        $h = max(8, ($amount / $maxAmount) * $maxChartHeight);
                    @endphp
                    <div class="flex flex-col items-center">
                        <div class="w-full rounded-t-md prism-bg opacity-80 transition-all hover:opacity-100"
                            style="height: {{ $h }}px" title="Rp {{ number_format($amount, 0, ',', '.') }}">
                        </div>
                        <p class="mt-1 text-[10px] font-mono text-ink-500">{{ $m['month'] }}</p>
                        @php
                            $val = $m['amount'];
                            if ($val >= 1000000000) {
                                $displayAmount = round($val / 1000000000, 1) . ' M';
                            } elseif ($val >= 1000000) {
                                $displayAmount = round($val / 1000000, 1) . ' Jt';
                            } elseif ($val >= 1000) {
                                $displayAmount = round($val / 1000, 1) . ' Rb';
                            } else {
                                $displayAmount = number_format($val, 0, ',', '.');
                            }
                        @endphp
                        <p
                            class="text-[10px] font-bold text-ink-700 leading-tight text-center whitespace-nowrap max-[375px]:whitespace-normal">
                            <span class="max-[470px]:block">Rp</span>
                            <span class="max-[470px]:block">
                                {{ str_replace('.', ',', $displayAmount) }}
                            </span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- TOP PRICED --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Most expensive cards</p>
            <ul class="mt-3 space-y-2">
                @foreach ($mostExpensive as $c)
                    <li class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-8 overflow-hidden rounded-md bg-ink-100">
                            @if ($c->image_small)
                                <img src="{{ $c->image_small }}" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-1 text-xs font-bold">{{ $c->name }}</p>
                            <p class="text-[10px] text-ink-500">{{ $c->rarity }}</p>
                        </div>
                        <span class="font-mono text-xs font-bold prism-text">@idr($c->market_price)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-8 rounded-3xl border border-ink-200 bg-white">
        <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
            <h2 class="font-display text-lg font-black">Recent orders</h2>
            <a href="{{ route('admin.orders.index') }}"
                class="text-xs font-semibold text-ink-500 hover:text-ink-900">View all →</a>
        </div>
        @if ($recentOrders->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-ink-500">No orders yet.</p>
        @else
            <div class="overflow-x-auto [-webkit-overflow-scrolling:touch]">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                        <tr>
                            <th class="px-6 py-3">Order</th>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($recentOrders as $o)
                            <tr class="hover:bg-ink-50">
                                <td class="px-6 py-3 font-mono text-xs whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $o) }}"
                                        class="hover:text-prism-violet">{{ $o->code }}</a>
                                </td>
                                <td class="px-6 py-3">{{ $o->user?->name }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        class="rounded-full bg-{{ $o->status_color }}-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-{{ $o->status_color }}-700">{{ $o->status }}</span>
                                </td>
                                <td class="px-6 py-3 text-right font-mono font-bold whitespace-nowrap">@idr($o->total)
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-admin-layout>
