<x-app-layout>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Vendor Hero — Cover Image + Store Name                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="relative w-full h-64 md:h-80 rounded-2xl overflow-hidden mb-8 shadow-lg">

        {{-- Cover Image or fallback gradient --}}
        @if($vendor->cover_image)
            <img
                src="{{ asset('storage/' . $vendor->cover_image) }}"
                alt="{{ $vendor->store_name }} cover"
                class="absolute inset-0 w-full h-full object-cover"
            >
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500"></div>
        @endif

        {{-- Dark gradient overlay from bottom --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

        {{-- Store Name & Address on top of the image --}}
        <div class="absolute bottom-0 left-0 right-0 px-6 py-6 md:py-8">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white tracking-tight drop-shadow-lg">
                {{ $vendor->store_name }}
            </h1>
            @if($vendor->store_address)
                <p class="mt-2 text-sm md:text-base text-white/75 drop-shadow">
                    📍 {{ $vendor->store_address }}
                </p>
            @endif
        </div>
    </div>

    {{-- Active filter chips --}}
    @if($activeDepartment || $activeCategory || $keyword)
        <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            @if($activeDepartment)
                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">
                    {{ $activeDepartment->name }}
                    <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'keyword' => $keyword])) }}" class="hover:text-blue-900 font-bold">×</a>
                </span>
            @endif
            @if($activeCategory)
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">
                    {{ $activeCategory->name }}
                    <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'department_id' => $activeDepartment?->id, 'keyword' => $keyword])) }}" class="hover:text-indigo-900 font-bold">×</a>
                </span>
            @endif
            @if($keyword)
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">
                    "{{ $keyword }}"
                    <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'department_id' => $activeDepartment?->id, 'category_id' => $activeCategory?->id])) }}" class="hover:text-gray-900 font-bold">×</a>
                </span>
            @endif
            <a href="{{ route('vendor.profile', $vendor) }}" class="text-xs text-indigo-600 hover:underline">Clear all</a>
        </div>
    @endif


        <div class="flex flex-col md:flex-row gap-6">

            {{-- Sidebar: Departments & Categories --}}
            @if($departments->isNotEmpty())
                <aside class="w-full md:w-48 md:flex-shrink-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Departments</p>
                    <ul class="flex flex-row md:flex-col overflow-x-auto gap-1 mb-4 pb-1"
                        style="scrollbar-width: none; -ms-overflow-style: none;">
                        <li class="shrink-0">
                            <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'keyword' => $keyword])) }}"
                               class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ !$activeDepartment ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                All
                            </a>
                        </li>
                        @foreach($departments as $dept)
                            <li class="shrink-0">
                                <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'department_id' => $dept->id, 'keyword' => $keyword])) }}"
                                   class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ $activeDepartment?->id === $dept->id ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                    {{ $dept->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Categories — only shown when a department is selected --}}
                    @if($activeDepartment && $categories->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Categories</p>
                        <ul class="flex flex-row md:flex-col overflow-x-auto gap-1 pb-1"
                            style="scrollbar-width: none; -ms-overflow-style: none;">
                            <li class="shrink-0">
                                <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'department_id' => $activeDepartment->id, 'keyword' => $keyword])) }}"
                                   class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ !$activeCategory ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                    All
                                </a>
                            </li>
                            @foreach($categories as $cat)
                                <li class="shrink-0">
                                    <a href="{{ route('vendor.profile', array_filter(['vendor' => $vendor->store_name, 'department_id' => $activeDepartment->id, 'category_id' => $cat->id, 'keyword' => $keyword])) }}"
                                       class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ $activeCategory?->id === $cat->id ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                        {{ $cat->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif

            {{-- Products --}}
            <div class="flex-1 min-w-0">

                @if($products->isEmpty())
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <svg class="w-14 h-14 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-xl font-semibold text-gray-400">
                            @if($keyword && $activeCategory)
                                No results for "{{ $keyword }}" in {{ $activeCategory->name }}
                            @elseif($keyword && $activeDepartment)
                                No results for "{{ $keyword }}" in {{ $activeDepartment->name }}
                            @elseif($keyword)
                                No results for "{{ $keyword }}" in this store
                            @elseif($activeCategory)
                                No products in {{ $activeCategory->name }}
                            @elseif($activeDepartment)
                                No products in {{ $activeDepartment->name }}
                            @else
                                This vendor has no products yet
                            @endif
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($products as $product)
                            <x-product-card :$product />
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <div class="mt-8">{{ $products->appends(request()->query())->links() }}</div>
                    @endif
                @endif
            </div>

        </div>
    </div>

</x-app-layout>
