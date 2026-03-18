<x-app-layout>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Title + active filter chips --}}
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $department->name }}</h1>

            @if($activeCategory || $keyword)
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                    @if($activeCategory)
                        <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $activeCategory->name }}
                            <a href="{{ route('product.byDepartment', array_filter(['department' => $department->slug, 'keyword' => $keyword])) }}" class="hover:text-indigo-900 font-bold">×</a>
                        </span>
                    @endif
                    @if($keyword)
                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">
                            "{{ $keyword }}"
                            <a href="{{ route('product.byDepartment', array_filter(['department' => $department->slug, 'category_id' => $activeCategory?->id])) }}" class="hover:text-gray-900 font-bold">×</a>
                        </span>
                    @endif
                    <a href="{{ route('product.byDepartment', $department) }}" class="text-xs text-indigo-600 hover:underline">Clear all</a>
                </div>
            @endif
        </div>

        <div class="flex flex-col md:flex-row gap-6">

            {{-- Categories Sidebar — horizontal scroll on mobile, vertical on md+ --}}
            @if($categories->isNotEmpty())
                <aside class="w-full md:w-48 md:flex-shrink-0">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Categories</p>
                    <ul class="flex flex-row md:flex-col overflow-x-auto gap-1 pb-1"
                        style="scrollbar-width: none; -ms-overflow-style: none;">
                        <li class="shrink-0">
                            <a href="{{ route('product.byDepartment', array_filter(['department' => $department->slug, 'keyword' => $keyword])) }}"
                               class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ !$activeCategory ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                All
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li class="shrink-0">
                                <a href="{{ route('product.byDepartment', array_filter(['department' => $department->slug, 'category_id' => $cat->id, 'keyword' => $keyword])) }}"
                                   class="block px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap {{ $activeCategory?->id === $cat->id ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
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
                            @elseif($keyword)
                                No results for "{{ $keyword }}" in {{ $department->name }}
                            @elseif($activeCategory)
                                No products in {{ $activeCategory->name }}
                            @else
                                No products for this department
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
