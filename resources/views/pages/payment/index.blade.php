<x-app-layout>

    <section class="mx-auto max-w-[1400px] px-4 py-12 md:px-8 md:py-16">
        <div class="mb-10">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Step 3 of 3
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                Complete your <span class="prism-text">payment</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">Securely complete your payment using Midtrans.</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-12">
            {{-- LEFT: Payment Section --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Order Summary Card --}}
                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h2 class="font-display text-lg font-black text-ink-900 mb-6">Order Summary</h2>

                    <dl class="space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-ink-100">
                            <dt class="text-sm text-ink-700">Order ID</dt>
                            <dd class="font-mono font-semibold text-ink-900">{{ $order->code }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <dt class="text-[10px] sm:text-xs text-ink-600 uppercase tracking-wider">Subtotal</dt>
                                <dd class="mt-1 font-display text-sm sm:text-base font-bold text-ink-900"> @idr($order->subtotal)</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] sm:text-xs text-ink-600 uppercase tracking-wider">Shipping</dt>
                                <dd class="mt-1 font-display text-sm sm:text-base font-bold text-ink-900">@idr($order->shipping_fee)</dd>
                            </div>
                            <div>
                                <dt class="text-[10px] sm:text-xs text-ink-600 uppercase tracking-wider">Tax</dt>
                                <dd class="mt-1 font-display text-sm sm:text-base font-bold text-ink-900">@idr($order->tax)</dd>
                            </div>
                        </div>
                        <div class="pt-4 border-t-2 border-prism-violet/20">
                            <dt class="text-xs text-ink-600 uppercase tracking-wider font-bold">Total Amount</dt>
                            <dd
                                class="mt-2 font-display text-3xl font-black bg-gradient-to-r from-prism-violet to-prism-pink bg-clip-text text-transparent">
                                @idr($order->total)
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Payment Methods Info --}}
                <div
                    class="rounded-3xl border border-ink-200 bg-gradient-to-br from-prism-violet/5 to-prism-pink/5 p-6">
                    <h3 class="font-display font-black text-ink-900 mb-4">Available Payment Methods</h3>
                    <p class="text-sm text-ink-700 mb-4">Midtrans supports multiple payment options including:</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            class="flex items-center gap-2 text-sm text-ink-700 bg-white rounded-xl p-3 border border-ink-100">
                            <svg class="h-4 w-4 text-prism-violet flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06m0 0L5.5 15h9m-3-5l3-3m-12-6h14a1 1 0 011 1v1a1 1 0 01-1 1H3a1 1 0 01-1-1V2a1 1 0 011-1z" />
                            </svg>
                            Bank Transfer
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-ink-700 bg-white rounded-xl p-3 border border-ink-100">
                            <svg class="h-4 w-4 text-prism-violet flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" />
                            </svg>
                            E-Wallet
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-ink-700 bg-white rounded-xl p-3 border border-ink-100">
                            <svg class="h-4 w-4 text-prism-violet flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                            Credit Card
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-ink-700 bg-white rounded-xl p-3 border border-ink-100">
                            <svg class="h-4 w-4 text-prism-violet flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 107.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"
                                    clip-rule="evenodd" />
                            </svg>
                            More Options
                        </div>
                    </div>
                </div>

                {{-- Shipping Details --}}
                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h2 class="font-display text-lg font-black text-ink-900 mb-4">Shipping Address</h2>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-ink-700">Name</dt>
                            <dd class="mt-1 text-ink-900">{{ $order->shipping_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-ink-700">Phone</dt>
                            <dd class="mt-1 text-ink-900">{{ $order->shipping_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-ink-700">Address</dt>
                            <dd class="mt-1 text-ink-900">{{ $order->shipping_address }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-widest text-ink-700">City</dt>
                                <dd class="mt-1 text-ink-900">{{ $order->shipping_city }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-widest text-ink-700">Postal Code</dt>
                                <dd class="mt-1 text-ink-900">{{ $order->shipping_postal_code }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- RIGHT: Payment Action --}}
            <aside class="lg:col-span-5">
                <div class="sticky top-24 space-y-4 rounded-3xl border border-ink-200 bg-white p-6">
                    <div class="text-center mb-6">
                        <div
                            class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-prism-violet/10 to-prism-pink/10 mb-4">
                            <svg class="h-10 w-10 text-prism-violet" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-2xl font-black text-ink-900">Ready to Pay?</h3>
                        <p class="mt-2 text-sm text-ink-600">Click below to proceed with secure payment.</p>
                    </div>

                    <button id="pay-button"
                        class="w-full rounded-2xl bg-black px-8 py-6 font-display font-bold text-xl text-white transition-all duration-300 hover:bg-gradient-to-r hover:from-prism-violet hover:to-prism-pink hover:shadow-2xl hover:scale-105 active:scale-95">
                        💳 Proceed to Payment
                    </button>

                    <div class="space-y-3 rounded-2xl bg-ink-50 p-4 border border-ink-100">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-ink-700 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Order Details
                        </h4>
                        <div class="text-sm text-ink-900 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-ink-700">Order Code:</span>
                                <span class="font-mono font-semibold">{{ $order->code }}</span>
                            </div>
                            <div class="flex justify-between border-t border-ink-200 pt-2">
                                <span class="text-ink-700 font-bold">Total:</span>
                                <span class="font-display font-black text-prism-violet">@idr($order->total)</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-ink-700">Status:</span>
                                <span
                                    class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold text-yellow-800">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-ink-100">
                        <p class="text-xs text-ink-600 mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M2.166 4.999a11.954 11.954 0 010 10.002 8 8 0 1015.668 0A11.954 11.954 0 012.166 4.999zm11.541 1.422a6 6 0 01.128 8.159l-5.418-5.418a6 6 0 015.29-2.741z"
                                    clip-rule="evenodd" />
                            </svg>
                            Powered by Midtrans
                        </p>
                        <p class="text-xs text-ink-500">Your payment is encrypted and secure.</p>
                    </div>

                    <a href="{{ route('orders.show', $order->code) }}"
                        class="block text-center text-xs text-prism-violet hover:text-prism-pink font-semibold transition-colors mt-4 py-2 hover:bg-prism-violet/5 rounded-lg">
                        ← View Order Details
                    </a>
                </div>
            </aside>
        </div>
    </section>

    <!-- Midtrans Snap JS setup -->
    <script
        src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function() {
            // SnapToken acquired from the CheckoutController
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    window.location.href = "{{ route('payment_status', $order->code) }}";
                },
                onPending: function(result) {
                    window.location.href = "{{ route('payment_status', $order->code) }}";
                },
                onError: function(result) {
                    alert("Payment failed! Please try again.");
                    window.location.href = "{{ route('payment_status', $order->code) }}";
                },
                onClose: function() {
                    // User closed the popup without finishing payment
                    console.log('Payment popup closed');
                }
            });
        };
    </script>

</x-app-layout>
