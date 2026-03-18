<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">

            {{-- ── Status Banner (polling-driven) ───────────────────────────────── --}}
            <div x-data="{
                    status: 'processing',
                    pollInterval: null,
                    maxAttempts: 12,
                    attempts: 0,
                    sessionId: '{{ request()->query('session_id') }}',

                    init() {
                        this.startPolling();
                    },

                    startPolling() {
                        this.pollInterval = setInterval(() => {
                            this.checkStatus();
                        }, 5000);
                    },

                    async checkStatus() {
                        this.attempts++;

                        try {
                            const res  = await fetch('/stripe/order-status?session_id=' + this.sessionId, {
                                headers: { 'Accept': 'application/json' }
                            });
                            const data = await res.json();

                            if (data.status === 'paid') {
                                this.status = 'paid';
                                clearInterval(this.pollInterval);
                            } else if (this.attempts >= this.maxAttempts) {
                                this.status = 'failed';
                                clearInterval(this.pollInterval);
                            }
                        } catch (e) {
                            if (this.attempts >= this.maxAttempts) {
                                this.status = 'failed';
                                clearInterval(this.pollInterval);
                            }
                        }
                    }
                }"
                x-init="init()">

                {{-- Processing --}}
                <div x-show="status === 'processing'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="text-center mb-8">
                    <div class="flex justify-center mb-4">
                        <svg class="animate-spin h-16 w-16 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-700">Payment Processing...</h1>
                    <p class="text-gray-500 mt-2">Please wait while we confirm your payment.</p>
                    <p class="text-xs text-gray-400 mt-1">This may take a few seconds.</p>
                </div>

                {{-- Paid / Confirmed --}}
                <div x-show="status === 'paid'"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     style="display:none"
                     class="text-center mb-8">
                    <div class="text-green-500 mb-4">
                        <svg class="w-24 h-24 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-green-700 uppercase">Payment Confirmed ✓</h1>
                    <p class="text-gray-500 mt-2">Thanks for your purchase! Your order has been confirmed.</p>
                </div>

                {{-- Failed --}}
                <div x-show="status === 'failed'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="display:none"
                     class="text-center mb-8">
                    <div class="text-red-400 mb-4">
                        <svg class="w-24 h-24 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-red-600">Payment Failed</h1>
                    <p class="text-gray-500 mt-2">We couldn't confirm your payment. Please try again later.</p>
                    <div class="mt-4">
                        <a href="{{ route('cart.index') }}"
                           class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                            Return to Cart
                        </a>
                    </div>
                </div>

            </div>

            {{-- ── Order Summary Cards ─────────────────────────────────────────── --}}
            @foreach ($orders as $order)
                <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 border-b pb-3 mb-4">Order Summary</h2>

                    <div class="space-y-3 text-sm text-gray-600">
                        {{-- Seller --}}
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Seller</span>
                            <a href="#" class="font-semibold text-gray-800 hover:underline hover:text-blue-700">
                                {{ $order->vendor->store_name }}</a >
                        </div>

                        {{-- Order Number --}}
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Order Number</span>
                            <span class="font-semibold text-gray-800"># {{ $order->id }}</span>
                        </div>

                        {{-- Items --}}
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Items</span>
                            <span class="font-semibold text-gray-800">{{ $order->orderItems->count() }}</span>
                        </div>

                        {{-- Total --}}
                        <div class="flex justify-between border-t pt-3 mt-2">
                            <span class="font-medium text-gray-500">Total</span>
                            <span class="font-bold text-indigo-600 text-base">$ {{ $order->total_price }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center justify-center px-6 py-2 font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition">
                            Back to Home
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
