<nav x-data="{
    open: false,
    cartOpen: false,
    profileOpen: false,
    menuOpen: false,
    totalItems: {{ app(\App\Services\CartService::class)->getTotalQuantity() }},
    totalPrice: 0,
    cartItems: [],
    async loadCart() {
        try {
            const res = await fetch('{{ route('cart.items') }}', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            this.totalItems = data.totalQuantity;
            this.totalPrice = data.totalPrice;
            this.cartItems = data.items;
        } catch (e) { console.error('Cart load failed', e); }
    }
}" x-init="loadCart()"
    @cart-updated.window="totalItems = $event.detail.totalQuantity; loadCart()"
    class="relative bg-white border-b border-gray-100 shadow-sm">

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center gap-2">

            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <!-- Search Bar – hidden on very small, visible from sm+ -->
            @php
                if (request()->routeIs('product.byDepartment')) {
                    $searchAction = route('product.byDepartment', request()->route('department'));
                } elseif (request()->routeIs('vendor.profile')) {
                    $searchAction = route('vendor.profile', request()->route('vendor'));
                } else {
                    $searchAction = route('home');
                }
            @endphp
            <form method="GET" action="{{ $searchAction }}" class="hidden sm:flex flex-1 max-w-xl items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                    </div>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Search products…"
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-r-lg transition whitespace-nowrap">
                    Search
                </button>
            </form>

            {{-- Desktop quick links: Dashboard --}}
            <div class="hidden sm:flex items-center gap-1 shrink-0">
                @auth
                    @if (auth()->user()->hasAnyRole([\App\Enums\RolesEnum::Admin->value, \App\Enums\RolesEnum::Vendor->value]))
                        <a href="/admin"
                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-100 transition">
                            Dashboard
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Right Side: Cart + Profile (desktop) -->
            <div class="hidden sm:flex sm:items-center gap-3">

                <!-- Shopping Cart -->
                <div class="relative">
                    <button @click="cartOpen = !cartOpen; profileOpen = false"
                        class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="totalItems > 0" x-text="totalItems"
                            class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"></span>
                    </button>

                    <!-- Cart Dropdown -->
                    <div x-show="cartOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        @click.outside="cartOpen = false"
                        class="absolute right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                        style="width: 470px; display: none;">
                        <div class="p-6">
                            <p class="text-2xl font-bold text-gray-800 mb-2" x-text="totalItems + ' Items'"></p>
                            <p class="text-cyan-400 text-base mb-4" x-text="'Total: $' + totalPrice.toFixed(2)"></p>

                            <template x-if="cartItems.length > 0">
                                <div class="max-h-96 overflow-y-auto mb-4 space-y-3">
                                    <template x-for="item in cartItems"
                                        :key="item.product_id + JSON.stringify(item.option_ids)">
                                        <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                                            <a :href="item.variation_url" class="flex-shrink-0">
                                                <img :src="item.image" :alt="item.title"
                                                    class="w-16 h-16 object-cover rounded">
                                            </a>
                                            <div class="flex-1 min-w-0">
                                                <a :href="item.variation_url"
                                                    class="text-sm font-semibold text-gray-900 hover:text-blue-600 block truncate"
                                                    x-text="item.title"></a>
                                                <p class="text-xs text-gray-500 mt-1"
                                                    x-text="'$' + parseFloat(item.price).toFixed(2) + ' × ' + item.quantity">
                                                </p>
                                            </div>
                                            <div class="text-sm font-bold text-gray-900"
                                                x-text="'$' + (item.price * item.quantity).toFixed(2)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="cartItems.length === 0">
                                <p class="text-gray-500 text-center py-4">Your cart is empty</p>
                            </template>

                            <x-link-button href="{{ route('cart.index') }}" class="w-full justify-center mt-4">
                                View cart
                            </x-link-button>
                        </div>
                    </div>
                </div>

                @if (auth()->check())
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen; cartOpen = false"
                            class="flex items-center p-1 rounded-full hover:bg-gray-100 transition">
                            <img class="h-8 w-8 rounded-full object-cover"
                                src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=6366f1&color=fff"
                                alt="{{ Auth::user()->name }}">
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            @click.outside="profileOpen = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
                            style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            </div>

                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Profile') }}
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <x-link-button href="{{ route('login') }}">Log in</x-link-button>
                    @if (Route::has('register'))
                        <x-link-button href="{{ route('register') }}">Register</x-link-button>
                    @endif
                @endif
            </div>

            <!-- Mobile: Cart icon + Menu button (shown on small screens) -->
            <div class="flex items-center gap-2 sm:hidden">

                <!-- Mobile Cart Icon -->
                <a href="{{ route('cart.index') }}"
                    class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="totalItems > 0" x-text="totalItems"
                        class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"></span>
                </a>

                <!-- Mobile Menu Button -->
                <button @click="menuOpen = !menuOpen"
                    class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600 hover:bg-gray-100 rounded-lg border border-gray-200 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path :class="{ 'hidden': menuOpen, 'block': !menuOpen }" class="block"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !menuOpen, 'block': menuOpen }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Menu</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar -->
    <div class="sm:hidden px-4 pb-3 border-t border-gray-100 mt-0">
        <form method="GET" action="{{ $searchAction }}" class="flex items-center gap-2 pt-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                </div>
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                    placeholder="Search products…"
                    class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
            </div>
            <button type="submit"
                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-r-lg transition whitespace-nowrap">
                Search
            </button>
        </form>
    </div>

    <!-- Mobile Menu Popup (slide down) -->
    <div x-show="menuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" @click.outside="menuOpen = false"
        class="sm:hidden absolute left-0 right-0 border-t border-gray-100 bg-white shadow-lg z-50"
        style="display: none;">
        <div class="px-4 py-4 space-y-2">
            @if (auth()->check())
                <!-- Logged-in user info -->
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100 mb-3">
                    <img class="h-10 w-10 rounded-full object-cover"
                        src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=6366f1&color=fff"
                        alt="{{ Auth::user()->name }}">
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                @if (auth()->user()->hasAnyRole([\App\Enums\RolesEnum::Admin->value, \App\Enums\RolesEnum::Vendor->value]))
                    <a href="/admin" @click="menuOpen = false"
                        class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}" @click="menuOpen = false"
                    class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition text-left">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" @click="menuOpen = false"
                    class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log in
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" @click="menuOpen = false"
                        class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        <svg class="h-5 w-5 text-indigo-200" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Register
                    </a>
                @endif
            @endif
        </div>
    </div>

    <!-- Department Sub-Navbar (scrollable on all screen sizes) -->
    @php
        $departments = \App\Models\Department::published()->orderBy('name')->get();
    @endphp
    @if ($departments->isNotEmpty())
        <div class="border-t border-gray-100 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 flex items-center gap-1 overflow-x-auto scroll-smooth"
                style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach ($departments as $dept)
                    @php
                        $isActive =
                            request()->routeIs('product.byDepartment') &&
                            request()->route('department')?->id === $dept->id;
                    @endphp
                    <a href="{{ route('product.byDepartment', $dept) }}"
                        class="shrink-0 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors rounded-sm
                              {{ $isActive
                                  ? 'text-indigo-600 border-b-2 border-indigo-600 font-semibold'
                                  : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-100 border-b-2 border-transparent' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</nav>
