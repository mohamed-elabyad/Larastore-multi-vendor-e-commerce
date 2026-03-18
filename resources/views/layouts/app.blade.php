<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' | ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

    {{-- SEO / OG meta tags – pushed from individual pages via @push('seo') --}}
    @stack('seo')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
            /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */
            @layer theme {

                :root,
                :host {
                    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                    --font-serif: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
                    --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                }
            }

            @layer base {

                *,
                :after,
                :before,
                ::backdrop {
                    box-sizing: border-box;
                    border: 0 solid;
                    margin: 0;
                    padding: 0
                }

                html,
                :host {
                    -webkit-text-size-adjust: 100%;
                    -moz-tab-size: 4;
                    tab-size: 4;
                    line-height: 1.5;
                    font-family: var(--default-font-family, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");
                }
            }

            @layer components;

            @layer utilities {
                .absolute {
                    position: absolute
                }

                .relative {
                    position: relative
                }

                .flex {
                    display: flex
                }

                .hidden {
                    display: none
                }

                .w-full {
                    width: 100%
                }
            }
        </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Flash Messages -->
        <div x-data="{
            show: false,
            message: '',
            type: 'success',
            init() {
                @if(session('success'))
                    this.showFlash('{{ session('success') }}', 'success');
                @elseif(session('error'))
                    this.showFlash('{{ session('error') }}', 'error');
                @endif

                window.addEventListener('notify', (event) => {
                    this.showFlash(event.detail.message, event.detail.type || 'success');
                });
            },
            showFlash(message, type) {
                this.message = message;
                this.type = type;
                this.show = true;
                setTimeout(() => this.show = false, 5000);
            }
        }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed top-20 right-4 z-50 max-w-md"
        style="display: none;">
            <div :class="{
                'bg-green-50 border-green-500 text-green-800': type === 'success',
                'bg-red-50 border-red-500 text-red-800': type === 'error'
            }" class="border-l-4 p-4 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Success Icon -->
                        <svg x-show="type === 'success'" class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Error Icon -->
                        <svg x-show="type === 'error'" class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-semibold" x-text="message"></p>
                    </div>
                    <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

</body>

</html>
