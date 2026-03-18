<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- LEFT COLUMN: 2/3 — existing profile forms --}}
                <div class="lg:col-span-2 space-y-6">

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: 1/3 — Vendor Details Livewire component --}}
                <div class="lg:col-span-1">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <livewire:vendor-details />
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
