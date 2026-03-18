<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
            </div>

            @if(count($groupedItems) > 0)
                <div x-data="cartManager({{ $totalPrice }}, {{ $totalQuantity }})" class="flex flex-col md:flex-row md:gap-6">
                    <!-- Left Side: Cart Items (2/3 width) -->
                    <div class="flex-1 space-y-6 order-1">
                        @foreach($groupedItems as $groupIndex => $group)
                            <!-- Seller Group Card -->
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <!-- Seller Header -->
                                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                    <h2 class="text-base font-medium text-gray-700 underline">
                                        {{ $group['user']['name'] }}
                                    </h2>
                                    <form action="{{ route('cart.checkout') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="vendor_id" value="{{ $group['user']['id'] }}">
                                        <button type="submit" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            Pay Only for this seller
                                        </button>
                                    </form>
                                </div>

                                <div class="divide-y divide-gray-100">
                                    @foreach($group['items'] as $itemIndex => $item)
                                        <div class="p-4 sm:p-6 hover:bg-gray-50 transition" x-show="!items['{{ $item['product_id'] }}_{{ implode('_', $item['option_ids']) }}']?.deleted">
                                            <div class="flex gap-4">

                                                <!-- Product Image -->
                                                <a href="{{ $item['variation_url'] }}" class="flex-shrink-0">
                                                    <img src="{{ $item['image'] }}"
                                                         alt="{{ $item['title'] }}"
                                                         class="w-24 h-24 object-cover rounded">
                                                </a>

                                                <!-- Product Info + Price -->
                                                <div class="flex-1 min-w-0 flex flex-col gap-2">
                                                    <!-- Top row: title + price -->
                                                    <div class="flex items-start justify-between gap-2">
                                                        <a href="{{ $item['variation_url'] }}"
                                                           class="font-medium text-gray-900 hover:text-indigo-600 block">
                                                            {{ $item['title'] }}
                                                        </a>
                                                        <!-- Price -->
                                                        <div class="text-base font-semibold text-gray-900 flex-shrink-0"
                                                             x-text="'$' + ((items['{{ $item['product_id'] }}_{{ implode('_', $item['option_ids']) }}']?.quantity || {{ $item['quantity'] }}) * {{ $item['price'] }}).toFixed(2)">
                                                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                                        </div>
                                                    </div>

                                                    <!-- Variations Display -->
                                                    @if(count($item['options']) > 0)
                                                        <div class="text-xs text-gray-500 space-y-0.5">
                                                            @foreach($item['options'] as $option)
                                                                <div>{{ $option['type']['name'] }}: {{ $option['name'] }}</div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <!-- Actions Row -->
                                                    <div class="flex flex-wrap items-center gap-3 text-sm">
                                                        <!-- Quantity -->
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 text-xs">Qty:</span>
                                                            <div class="flex items-center border border-gray-300 rounded">
                                                                <button @click="updateQuantity('{{ $item['product_id'] }}', {{ json_encode(array_values($item['option_ids'])) }}, -1, {{ $item['price'] }})"
                                                                        :disabled="loading"
                                                                        class="px-2 py-1 hover:bg-gray-100 disabled:opacity-50">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                                    </svg>
                                                                </button>
                                                                <span class="px-3 py-1 text-center font-medium min-w-[2rem]"
                                                                      x-text="items['{{ $item['product_id'] }}_{{ implode('_', $item['option_ids']) }}']?.quantity || {{ $item['quantity'] }}">
                                                                    {{ $item['quantity'] }}
                                                                </span>
                                                                <button @click="updateQuantity('{{ $item['product_id'] }}', {{ json_encode(array_values($item['option_ids'])) }}, 1, {{ $item['price'] }})"
                                                                        :disabled="loading"
                                                                        class="px-2 py-1 hover:bg-gray-100 disabled:opacity-50">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Delete -->
                                                        <button @click="deleteItem('{{ $item['product_id'] }}', {{ json_encode(array_values($item['option_ids'])) }}, {{ $item['price'] }}, {{ $item['quantity'] }})"
                                                                :disabled="loading"
                                                                class="text-red-500 hover:text-red-700 text-xs font-medium disabled:opacity-50">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Right Side: Checkout Summary (1/3 width) -->
                    <div class="mt-6 md:mt-0 md:w-80 order-2 md:order-2">
                        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                            <div class="mb-6">
                                <div class="text-gray-700 mb-2">
                                    <span class="font-medium">Subtotal</span>
                                    <span class="text-gray-600"> (<span x-text="totalQuantity">{{ $totalQuantity }}</span> items):</span>
                                </div>
                                <div class="text-2xl font-bold text-gray-900" x-text="'$' + totalPrice.toFixed(2)">
                                    ${{ number_format($totalPrice, 2) }}
                                </div>
                            </div>

                            <form action="{{ route('cart.checkout') }}" method="POST" class="w-full">
                                @csrf
                                <x-primary-button type="submit" class="w-full justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Proceed to checkout
                                </x-primary-button>
                            </form>

                            <a href="{{ route('home') }}"
                               class="mt-4 block text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h2 class="mt-6 text-2xl font-bold text-gray-900">Your cart is empty</h2>
                    <p class="mt-2 text-gray-600">Start shopping to add items!</p>
                    <div class="mt-8">
                        <x-link-button href="{{ route('home') }}">
                            Browse Products
                        </x-link-button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function cartManager(initialPrice, initialQuantity) {
            return {
                loading: false,
                totalPrice: initialPrice,
                totalQuantity: initialQuantity,
                items: {},

                init() {
                    // Initialize all items from the page
                    @foreach($groupedItems as $group)
                        @foreach($group['items'] as $item)
                            this.items['{{ $item['product_id'] }}_{{ implode('_', $item['option_ids']) }}'] = {
                                quantity: {{ $item['quantity'] }},
                                deleted: false,
                                price: {{ $item['price'] }}
                            };
                        @endforeach
                    @endforeach
                },

                async updateQuantity(productId, optionIds, change, price) {
                    const itemKey = productId + '_' + optionIds.join('_');

                    if (!this.items[itemKey]) {
                        console.error('Item not found:', itemKey);
                        return;
                    }

                    const newQuantity = this.items[itemKey].quantity + change;
                    if (newQuantity < 1) return;

                    this.loading = true;

                    try {
                        const response = await fetch(`/cart/${productId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                quantity: newQuantity,
                                option_ids: optionIds
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update local state
                            const oldQuantity = this.items[itemKey].quantity;
                            this.items[itemKey].quantity = newQuantity;

                            // Update totals
                            this.totalPrice += (newQuantity - oldQuantity) * price;
                            this.totalQuantity += (newQuantity - oldQuantity);

                            // Show success message
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: { message: data.message, type: 'success' }
                            }));
                        } else {
                            alert('Failed to update quantity');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteItem(productId, optionIds, price, quantity) {
                    const itemKey = productId + '_' + optionIds.join('_');
                    this.loading = true;

                    try {
                        const formData = new FormData();
                        formData.append('_method', 'DELETE');
                        optionIds.forEach(id => formData.append('option_ids[]', id));

                        const response = await fetch(`/cart/${productId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Mark as deleted
                            if (this.items[itemKey]) {
                                this.items[itemKey].deleted = true;

                                // Update totals
                                this.totalPrice -= price * this.items[itemKey].quantity;
                                this.totalQuantity -= this.items[itemKey].quantity;
                            }

                            // Show success message
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: { message: data.message, type: 'success' }
                            }));

                            // Reload if cart is empty
                            if (this.totalQuantity <= 0) {
                                window.location.reload();
                            }
                        } else {
                            alert('Failed to delete item');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
