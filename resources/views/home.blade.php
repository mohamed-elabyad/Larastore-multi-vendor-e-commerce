<x-app-layout>

    {{-- ──────────────────────────────────────────────────────
         Hero Section
         ──────────────────────────────────────────────────────
    --}}
    <section class="w-full bg-white dark:bg-gray-800 shadow-md overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
            <div class="flex flex-col lg:flex-row items-center gap-8">

                {{-- Text Block --}}
                <div class="flex-1 text-center lg:text-left">
                    <h1 class="text-3xl sm:text-4xl xl:text-5xl font-extrabold tracking-tight leading-tight text-gray-900 dark:text-white mb-4">
                        Discover Quality Products
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 text-base sm:text-lg max-w-xl mx-auto lg:mx-0">
                        Your one-stop shop for everything you need. Quality, Affordable, and Fast Shipping.
                    </p>
                    <x-link-button href="#products" :row="true">
                        Shop Now
                    </x-link-button>
                </div>

                {{-- Images Grid — 2 columns on all screens, slightly more prominent on lg --}}
                <div class="flex-1 w-full max-w-sm sm:max-w-md mx-auto lg:mx-0">
                    <div class="grid grid-cols-2 gap-3">
                        <img class="w-full rounded-lg shadow-lg transform -rotate-2 hover:rotate-0 transition duration-300 aspect-square object-cover"
                            src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80"
                            alt="Smart Watch">
                        <img class="w-full mt-6 rounded-lg shadow-lg transform rotate-2 hover:rotate-0 transition duration-300 aspect-square object-cover"
                            src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80"
                            alt="Headphones">
                        <img class="w-full rounded-lg shadow-lg transform rotate-2 hover:rotate-0 transition duration-300 aspect-square object-cover"
                            src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80"
                            alt="Nike Shoes">
                        <img class="w-full mt-6 rounded-lg shadow-lg transform -rotate-2 hover:rotate-0 transition duration-300 aspect-square object-cover"
                            src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=400&q=80"
                            alt="Camera">
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ──────────────────────────────────────────────────────
         Products Grid
         ──────────────────────────────────────────────────────
    --}}
    <div id="products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <x-product-card :$product />
            @endforeach
        </div>
    </div>

</x-app-layout>
