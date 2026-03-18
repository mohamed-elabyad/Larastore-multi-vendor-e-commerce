<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <a href="{{ route('products.show', $product->slug) }}">
        <div class="bg-gray-50">
            <img src="{{$product->image}}" alt="{{$product->title}}" class="w-full aspect-square object-contain">
        </div>
    </a>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-2">{{$product->title}}</h2>
        <p class="text-gray-600 text-sm mb-4">
            by <a href="{{route('vendor.profile', $product->user->vendor->store_name)}}" class="hover:underline">{{$product->user->vendor->store_name}}</a>
            in <a href="{{route('product.byDepartment', $product->department->slug)}}" class="hover:underline">{{$product->department->name}}</a>
        </p>
        <div class="flex items-center justify-between mt-3" x-data="{
            loading: false,
            async addToCart() {
                this.loading = true;
                try {
                    const formData = new FormData(this.$refs.form);
                    const response = await fetch(this.$refs.form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { message: data.message, type: 'success' }
                        }));
                        window.dispatchEvent(new CustomEvent('cart-updated', {
                            detail: data
                        }));
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <form x-ref="form" @submit.prevent="addToCart" action="{{ route('cart.store', $product) }}" method="POST">
                @csrf
                <input type="hidden" name="quantity" value="1">
                @php
                    // Get first variation option for each variation type
                    $firstOptions = $product->variationTypes->map(function($variationType) {
                        return $variationType->options->first()?->id;
                    })->filter()->toArray();
                @endphp
                @foreach($product->variationTypes as $index => $variationType)
                    @if($variationType->options->first())
                        <input type="hidden" name="option_ids[{{ $variationType->id }}]" value="{{ $variationType->options->first()->id }}">
                    @endif
                @endforeach
                <x-primary-button type="submit" x-bind:disabled="loading" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded disabled:opacity-50">
                    <span x-show="!loading">Add to Cart</span>
                    <span x-show="loading">Adding...</span>
                </x-primary-button>
            </form>
            <span class="text-2xl font-bold text-gray-900">
                ${{$product->price}}
            </span>
        </div>
    </div>
</div>
