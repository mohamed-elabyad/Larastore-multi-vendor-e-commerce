<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">

            {{-- Cancel Icon & Header --}}
            <div class="text-center mb-8">
                <div class="text-red-500 mb-4">
                    <svg class="w-24 h-24 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Payment Cancelled</h1>
                <p class="text-gray-500 mt-2">Your payment was not completed. Please try again or contact support if you need help.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <p class="text-gray-600 mb-6">No charges were made to your account.</p>

                <div class="flex flex-wrap justify-center items-center gap-3">
                    <x-link-button href="{{ route('cart.index') }}">
                        Back to Cart
                    </x-link-button>

                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center px-6 py-2 font-medium tracking-wide text-gray-700 capitalize transition-colors duration-300 transform bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-80">
                        Back to Home
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
