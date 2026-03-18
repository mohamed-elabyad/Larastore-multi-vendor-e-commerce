<div>

    {{-- ===== CARD HEADER ===== --}}
    <div class="flex items-center gap-3 mb-6">
        <h2 class="text-lg font-medium text-gray-900">Vendor Details</h2>

        @if ($user->vendor)
            @php
                $status = $user->vendor->status instanceof \App\Enums\VendorStatusEnum
                    ? $user->vendor->status->value
                    : $user->vendor->status;

                $badgeClasses = match($status) {
                    'approved' => 'bg-green-500 text-white',
                    'pending'  => 'bg-yellow-400 text-white',
                    'rejected' => 'bg-red-500 text-white',
                    default    => 'bg-gray-400 text-white',
                };

                $badgeLabel = $vendorStatusLabels[$status] ?? ucfirst($status);
            @endphp
            <span class="px-3 py-0.5 text-sm font-semibold rounded-full {{ $badgeClasses }}">
                {{ $badgeLabel }}
            </span>
        @endif
    </div>

    {{-- ======================================================= --}}
    {{-- CASE 1: No vendor yet — "Become a Vendor" button/form  --}}
    {{-- ======================================================= --}}
    @if (! $user->vendor)

        {{-- Step 1: Button to show confirmation --}}
        @if (! $showOnboardingForm)
            <div>
                <x-primary-button type="button" wire:click="confirmBecome">
                    Become a Vendor
                </x-primary-button>
            </div>
        @else
            {{-- Step 2: Onboarding Form (shows after clicking "Yes, Continue") --}}
            <div class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                <p class="text-sm font-medium text-indigo-700">
                    ✅ Great! Fill in your store details below to get started.
                </p>
            </div>

            <form wire:submit.prevent="becomeVendor" class="space-y-5" enctype="multipart/form-data">

                {{-- Store Name --}}
                <div>
                    <label for="new_store_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Store Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="new_store_name"
                        type="text"
                        wire:model="new_store_name"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
                        placeholder="my-awesome-store"
                    >
                    @error('new_store_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Store Address --}}
                <div>
                    <label for="new_store_address" class="block text-sm font-medium text-gray-700 mb-1">
                        Store Address
                    </label>
                    <textarea
                        id="new_store_address"
                        wire:model="new_store_address"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition resize-none"
                        placeholder="Your store address"
                    ></textarea>
                    @error('new_store_address')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cover Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Cover Image <span class="text-gray-400 text-xs">(optional)</span>
                    </label>

                    {{-- Preview --}}
                    @if ($new_cover_image)
                        <div class="mb-2 rounded-xl overflow-hidden h-32 w-full">
                            <img src="{{ $new_cover_image->temporaryUrl() }}"
                                 class="w-full h-full object-cover" alt="Cover preview">
                        </div>
                    @endif

                    <label class="flex items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition">
                        <div class="flex flex-col items-center gap-1 text-gray-400">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 16.5V19a1 1 0 001 1h16a1 1 0 001-1v-2.5M16 10l-4-4m0 0L8 10m4-4v12"/>
                            </svg>
                            <span class="text-sm">Click to upload cover image</span>
                        </div>
                        <input type="file" wire:model="new_cover_image" class="hidden" accept="image/*">
                    </label>

                    <div wire:loading wire:target="new_cover_image" class="mt-2 text-xs text-indigo-500">Uploading...</div>
                    @error('new_cover_image')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <x-primary-button type="submit">
                        <span wire:loading.remove wire:target="becomeVendor">Create My Store</span>
                        <span wire:loading wire:target="becomeVendor">Creating...</span>
                    </x-primary-button>
                    <x-secondary-button type="button" wire:click="$set('showOnboardingForm', false)">
                        Cancel
                    </x-secondary-button>
                </div>
            </form>
        @endif

    {{-- ================================================= --}}
    {{-- CASE 2: Vendor exists — edit form + Stripe        --}}
    {{-- ================================================= --}}
    @else
        <form wire:submit.prevent="updateVendor" class="space-y-5" enctype="multipart/form-data">

            {{-- Store Name --}}
            <div>
                <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Store Name
                </label>
                <input
                    id="store_name"
                    type="text"
                    wire:model="store_name"
                    x-on:input="$el.value = $el.value.toLowerCase().replace(/\s+/g, '-')"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
                    placeholder="Your store name"
                >
                @error('store_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Store Address --}}
            <div>
                <label for="store_address" class="block text-sm font-medium text-gray-700 mb-1">
                    Store Address
                </label>
                <textarea
                    id="store_address"
                    wire:model="store_address"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition resize-none"
                    placeholder="Your store address"
                ></textarea>
                @error('store_address')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cover Image --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cover Image
                </label>

                @php $currentCover = $cover_image ? $cover_image->temporaryUrl() : ($user->vendor->cover_image ? asset('storage/' . $user->vendor->cover_image) : null); @endphp

                @if($currentCover)
                    <div class="relative rounded-xl overflow-hidden h-40 w-full mb-2">
                        <img src="{{ $currentCover }}" class="w-full h-full object-cover" alt="Cover">
                        <button
                            type="button"
                            wire:click="$set('changeCover', true)"
                            class="absolute bottom-2 right-2 bg-black/60 hover:bg-black/80 text-white text-xs px-3 py-1.5 rounded-lg transition backdrop-blur-sm"
                        >
                            ✏️ Change Cover
                        </button>
                    </div>
                @endif

                @if(! $currentCover || $changeCover)
                    <label class="flex items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition">
                        <div class="flex flex-col items-center gap-1 text-gray-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 16.5V19a1 1 0 001 1h16a1 1 0 001-1v-2.5M16 10l-4-4m0 0L8 10m4-4v12"/>
                            </svg>
                            <span class="text-sm">{{ $user->vendor->cover_image ? 'Choose new cover' : 'Upload cover image' }}</span>
                        </div>
                        <input type="file" wire:model="cover_image" class="hidden" accept="image/*">
                    </label>
                    <div wire:loading wire:target="cover_image" class="mt-1 text-xs text-indigo-500">Uploading...</div>
                @endif

                @error('cover_image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Update Button --}}
            <div>
                <x-primary-button type="submit">
                    <span wire:loading.remove wire:target="updateVendor">Update</span>
                    <span wire:loading wire:target="updateVendor">Saving...</span>
                </x-primary-button>
            </div>
        </form>

        {{-- Stripe Section --}}
        <div class="mt-6 pt-5 border-t border-gray-200">
            @if ($user->stripe_account_active)
                <p class="text-sm font-semibold text-green-600 mb-3">
                    ✓ You are already connected to Stripe
                </p>
                <button type="button" disabled
                    class="w-full px-6 py-2.5 font-medium text-white bg-indigo-300 rounded-lg cursor-not-allowed opacity-60">
                    Connect to Stripe
                </button>
            @else
                <x-link-button href="{{ route('stripe.connect') }}" class="w-full justify-center">
                    Connect to Stripe
                </x-link-button>
            @endif
        </div>
    @endif
</div>
