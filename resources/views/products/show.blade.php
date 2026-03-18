{{--
    ╔══════════════════════════════════════════════════════════════════╗
    ║                   SEO & Open Graph – Product Show                ║
    ╚══════════════════════════════════════════════════════════════════╝
--}}
@php
    /* ── Title ─────────────────────────────────────────────────── */
    $seoTitle       = $product->meta_title
                        ?: $product->title;
    $fullTitle      = $seoTitle . ' | ' . config('app.name');

    /* ── Description ────────────────────────────────────────────── */
    $seoDescription = $product->meta_description
                        ?: Str::limit(strip_tags($product->description ?? ''), 160);

    /* ── Canonical URL ──────────────────────────────────────────── */
    $canonicalUrl   = route('products.show', $product->slug);

    /* ── OG Image ───────────────────────────────────────────────── */
    $ogImage        = $product->image                           // uses getImageAttribute()
                        ?: asset('images/og-default.png');      // fallback

    /* ── Site name ──────────────────────────────────────────────── */
    $siteName       = config('app.name');
@endphp


@push('seo')
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  Standard SEO Meta Tags                                     --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots"      content="index, follow">
    <link rel="canonical"    href="{{ $canonicalUrl }}">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  Open Graph – Facebook / Instagram / WhatsApp / LinkedIn    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <meta property="og:type"        content="product">
    <meta property="og:site_name"   content="{{ $siteName }}">
    <meta property="og:title"       content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image"       content="{{ $ogImage }}">
    <meta property="og:image:alt"   content="{{ $seoTitle }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height"content="630">
    <meta property="og:url"         content="{{ $canonicalUrl }}">
    <meta property="og:locale"      content="{{ str_replace('_', '-', app()->getLocale()) }}">

    {{-- Product-specific OG (Facebook catalog) --}}
    <meta property="product:price:amount"   content="{{ $product->price }}">
    <meta property="product:price:currency" content="USD">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  Twitter Card – X / Twitter share previews                  --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image"       content="{{ $ogImage }}">
    <meta name="twitter:image:alt"   content="{{ $seoTitle }}">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{--  WhatsApp / Telegram / Messenger (use standard OG above)    --}}
    {{--  Extra hint tag that WhatsApp reads for link previews        --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
@endpush

<x-app-layout>
    <x-slot:title>{{ $seoTitle }}</x-slot:title>

    <div class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="{
                // Product data
                productId: {{ $product->id }},
                defaultPrice: {{ $product->price }},
                defaultQuantity: {{ $product->quantity ?? 'null' }},
                productImages: {{ json_encode($productImages) }},
                variationTypes: {{ json_encode($variationTypes) }},
                productVariations: {{ json_encode($productVariations) }},

                // State
                selectedOptions: {},
                currentVariation: null,
                quantity: 1,
                currentPrice: {{ $product->price }},
                availableQuantity: {{ $product->quantity ?? 'null' }},
                galleryImages: {{ json_encode($productImages) }},
                mainImage: '{{ $productImages[0]['large'] ?? '' }}',
                quantityError: '',

                // Initialize from URL parameters
                init() {
                    this.initFromUrl();
                    this.setDefaultGallery();
                    this.updateGallery();
                },

                initFromUrl() {
                    const params = new URLSearchParams(window.location.search);
                    
                    // Get quantity from URL if present
                    const urlQuantity = params.get('quantity');
                    if (urlQuantity) {
                        this.quantity = parseInt(urlQuantity);
                    }
                    
                    // Get variation options from URL
                    this.variationTypes.forEach(vType => {
                        const optionId = params.get('options[' + vType.id + ']');
                        if (optionId) {
                            this.selectedOptions[vType.id] = parseInt(optionId);
                        }
                    });
                    this.findMatchingVariation();
                },

                // Select variation option
                selectOption(typeId, optionId) {
                    this.selectedOptions[typeId] = optionId;
                    this.syncUrlParams();
                    this.findMatchingVariation();
                    this.updateGallery();
                    this.validateQuantity();
                },

                // Set default gallery to first color option
                setDefaultGallery() {
                    const colorType = this.variationTypes.find(vt => vt.type === 'image');

                    if (colorType && colorType.options && colorType.options.length > 0) {
                        const firstOption = colorType.options[0];
                        if (firstOption.images && firstOption.images.length > 0) {
                            this.galleryImages = firstOption.images;
                            this.mainImage = firstOption.images[0].large;
                        }
                    } else if (this.productImages.length > 0) {
                        this.galleryImages = this.productImages;
                        this.mainImage = this.productImages[0].large;
                    }
                },

                // Update gallery based on selected color
                updateGallery() {
                    // Find color variation type (usually type 'image')
                    const colorType = this.variationTypes.find(vt => vt.type === 'image');

                    if (colorType && this.selectedOptions[colorType.id]) {
                        const selectedOption = colorType.options.find(opt => opt.id === this.selectedOptions[colorType.id]);

                        if (selectedOption && selectedOption.images && selectedOption.images.length > 0) {
                            this.galleryImages = selectedOption.images;
                            this.mainImage = selectedOption.images[0].large;
                            return;
                        }
                    }
                },

                // Find matching variation
                findMatchingVariation() {
                    const selectedIds = Object.values(this.selectedOptions).sort((a, b) => a - b);

                    if (selectedIds.length !== this.variationTypes.length) {
                        this.currentVariation = null;
                        this.currentPrice = this.defaultPrice;
                        this.availableQuantity = this.defaultQuantity;
                        return;
                    }

                    const match = this.productVariations.find(variation => {
                        const varIds = [...variation.variation_type_option_ids].sort((a, b) => a - b);
                        return JSON.stringify(varIds) === JSON.stringify(selectedIds);
                    });

                    if (match) {
                        this.currentVariation = match;
                        this.currentPrice = match.price !== null ? Number(match.price) : this.defaultPrice;
                        this.availableQuantity = match.quantity !== null ? match.quantity : this.defaultQuantity;
                    } else {
                        this.currentVariation = null;
                        this.currentPrice = this.defaultPrice;
                        this.availableQuantity = this.defaultQuantity;
                    }

                    this.validateQuantity();
                },

                // Sync URL parameters
                syncUrlParams() {
                    const params = new URLSearchParams();
                    Object.entries(this.selectedOptions).forEach(([typeId, optionId]) => {
                        params.set('options[' + typeId + ']', optionId);
                    });
                    const newUrl = window.location.pathname + '?' + params.toString();
                    history.pushState({}, '', newUrl);
                },

                // Validate quantity
                validateQuantity() {
                    if (this.availableQuantity !== null && this.quantity > this.availableQuantity) {
                        this.quantityError = 'Requested quantity exceeds available stock (' + this.availableQuantity + ')';
                    } else {
                        this.quantityError = '';
                    }
                },

                // Check if form is valid
                get isValid() {
                    // If product has no variations, only check quantity
                    if (this.variationTypes.length === 0) {
                        return this.quantity > 0 &&
                            this.quantityError === '' &&
                            (this.availableQuantity === null || this.quantity <= this.availableQuantity);
                    }

                    // If product has variations, check selection + quantity
                    return Object.keys(this.selectedOptions).length === this.variationTypes.length &&
                        this.quantity > 0 &&
                        this.quantityError === '' &&
                        (this.availableQuantity === null || this.quantity <= this.availableQuantity);
                },

                // Change main image
                changeMainImage(imageUrl) {
                    this.mainImage = imageUrl;
                },

                // Check if option is selected
                isOptionSelected(typeId, optionId) {
                    return this.selectedOptions[typeId] === optionId;
                }
            }" x-init="init()">
                {{-- Breadcrumb --}}
                <nav class="mb-6 text-sm">
                    <ol class="flex items-center space-x-2 rtl:space-x-reverse">
                        <li><a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800">Home</a></li>
                        <li class="text-gray-400">/</li>
                        <li><a href="{{route('product.byDepartment', $product->department->slug)}}"
                                class="text-blue-600 hover:text-blue-800">{{ $product->department->name }}</a></li>
                        <li class="text-gray-400">/</li>
                        <li><a href="#"
                                class="text-blue-600 hover:text-blue-800">{{ $product->category->name }}</a></li>
                        <li class="text-gray-400">/</li>
                        <li class="text-gray-600">{{ $product->title }}</li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Left Column: Gallery --}}
                    <div class="flex flex-col-reverse lg:flex-row gap-4 lg:sticky lg:top-8 lg:self-start">
                        {{-- Thumbnails (Bottom on mobile, Left on desktop) --}}
                        <div class="flex flex-row lg:flex-col gap-3 w-full lg:w-20 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0"
                            x-show="galleryImages.length > 1">
                            <template x-for="(image, index) in galleryImages" :key="index">
                                <div @click="changeMainImage(image.large)"
                                    class="flex-shrink-0 w-20 h-20 lg:w-full lg:h-auto bg-white rounded-lg overflow-hidden aspect-square cursor-pointer border-2 transition-all hover:border-blue-500"
                                    :class="mainImage === image.large ? 'border-blue-600 ring-2 ring-blue-300' :
                                        'border-gray-200'">
                                    <img :src="image.thumb" :alt="'{{ $product->title }} - ' + (index + 1)"
                                        class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>

                        {{-- Main Image --}}
                        <div
                            class="w-full lg:flex-1 bg-white rounded-lg shadow-lg overflow-hidden aspect-square lg:aspect-auto lg:h-[650px]">
                            <img :src="mainImage" :alt="'{{ $product->title }}'"
                                class="w-full h-full object-cover">
                        </div>
                    </div>

                    {{-- Right Column: Product Info --}}
                    <div class="space-y-6">
                        {{-- Product Title --}}
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->title }}</h1>
                            <p class="text-sm text-gray-600">
                                by <a href="{{route('vendor.profile', $product->user->vendor->store_name)}}"
                                    class="text-blue-600 hover:underline">{{ $product->user->vendor->store_name }}</a>
                                in <a href="{{route('product.byDepartment', $product->department->slug)}}"
                                    class="text-blue-600 hover:underline">{{ $product->department->name }}</a>
                            </p>
                        </div>

                        {{-- Price --}}
                        <div class="bg-gray-100 rounded-lg p-4">
                            <div class="flex items-baseline space-x-2 rtl:space-x-reverse">
                                <span class="text-3xl font-bold text-gray-900">$<span
                                        x-text="currentPrice.toFixed(2)"></span></span>
                                <span class="text-sm text-gray-500" x-show="availableQuantity !== null">
                                    (<span x-text="availableQuantity"></span> available)
                                </span>
                                <span class="text-sm text-green-600" x-show="availableQuantity === null">
                                    Unlimited stock
                                </span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="prose prose-sm max-w-none">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                            <div class="text-gray-700">{!! $product->description !!}</div>
                        </div>

                        {{-- Variations --}}
                        @foreach ($variationTypes as $variationType)
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $variationType['name'] }}</h3>

                                @if ($variationType['type'] === 'image')
                                    {{-- Image Type --}}
                                    <div class="grid grid-cols-4 gap-3">
                                        @foreach ($variationType['options'] as $option)
                                            <div @click="selectOption({{ $variationType['id'] }}, {{ $option['id'] }})"
                                                class="cursor-pointer rounded-lg overflow-hidden border-2 transition-all hover:border-blue-500"
                                                :class="isOptionSelected({{ $variationType['id'] }}, {{ $option['id'] }}) ?
                                                    'border-blue-600 ring-2 ring-blue-300' : 'border-gray-200'">
                                                @if (!empty($option['images']))
                                                    <img src="{{ $option['images'][0]['thumb'] }}"
                                                        alt="{{ $option['name'] }}"
                                                        class="w-full aspect-square object-cover">
                                                @else
                                                    <div
                                                        class="w-full aspect-square bg-gray-200 flex items-center justify-center">
                                                        <span
                                                            class="text-xs text-gray-600">{{ $option['name'] }}</span>
                                                    </div>
                                                @endif
                                                <div class="text-center py-1 text-xs font-medium">
                                                    {{ $option['name'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($variationType['type'] === 'radio')
                                    {{-- Radio Type --}}
                                    <div class="flex flex-wrap gap-3">
                                        @foreach ($variationType['options'] as $option)
                                            <button type="button"
                                                @click="selectOption({{ $variationType['id'] }}, {{ $option['id'] }})"
                                                class="px-6 py-3 rounded-lg border-2 font-medium transition-all hover:border-blue-500"
                                                :class="isOptionSelected({{ $variationType['id'] }}, {{ $option['id'] }}) ?
                                                    'border-blue-600 bg-blue-50 text-blue-700' :
                                                    'border-gray-300 bg-white text-gray-700'">
                                                {{ $option['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($variationType['type'] === 'select')
                                    {{-- Select Type --}}
                                    <select
                                        @change="selectOption({{ $variationType['id'] }}, parseInt($event.target.value))"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                        :value="selectedOptions[{{ $variationType['id'] }}] || ''">
                                        <option value="">Select {{ $variationType['name'] }}</option>
                                        @foreach ($variationType['options'] as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        @endforeach

                        {{-- Quantity Selection --}}
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quantity</h3>
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <button type="button" @click="quantity = Math.max(1, quantity - 1); validateQuantity()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold transition-colors">
                                    -
                                </button>
                                <input type="number" x-model.number="quantity" @input="validateQuantity()"
                                    min="1" :max="availableQuantity || 999999"
                                    class="w-20 px-4 py-2 border-2 border-gray-300 rounded-lg text-center font-semibold focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                <button type="button" @click="quantity++; validateQuantity()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold transition-colors"
                                    :disabled="availableQuantity !== null && quantity >= availableQuantity">
                                    +
                                </button>
                            </div>
                            <p x-show="quantityError" x-text="quantityError" class="text-red-600 text-sm mt-2"></p>
                        </div>
                        {{-- Add to Cart Form --}}
                        <div class="border-t pt-6" x-data="{ 
                            localLoading: false,
                            async submitAddToCart() {
                                this.localLoading = true;
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
                                    this.localLoading = false;
                                }
                            }
                        }">
                            <form x-ref="form" @submit.prevent="submitAddToCart" action="{{ route('cart.store',  $product) }}" method="POST">
                                @csrf
                                {{-- product_id is sent in URL, removing hidden input --}}
                                <input type="hidden" name="quantity" :value="quantity">
 
                                {{-- Hidden inputs for selected options (renamed to option_ids) --}}
                                <template x-for="(optionId, typeId) in selectedOptions" :key="typeId">
                                    <input type="hidden" :name="'option_ids[' + typeId + ']'" :value="optionId">
                                </template>
 
                                <button type="submit" :disabled="!isValid || localLoading"
                                    class="w-full py-4 rounded-lg font-bold text-lg transition-all disabled:opacity-50"
                                    :class="isValid ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer' :
                                        'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                    <span x-show="isValid && !localLoading">Add to Cart</span>
                                    <span x-show="localLoading">Adding...</span>
                                    <span x-show="!isValid && !localLoading">
                                        <span
                                            x-show="variationTypes.length > 0 && Object.keys(selectedOptions).length < variationTypes.length">
                                            Please select all options
                                        </span>
                                        <span
                                            x-show="Object.keys(selectedOptions).length === variationTypes.length && quantityError">
                                            Invalid quantity
                                        </span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
