<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ isset($title) ? config('app.name') . " | $title" : config('app.name') }}</title>

        {{-- <!-- Premium Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"> --}}

        <style>
            [x-cloak] {
                display: none !important;
            }

            /* body {
                font-family: 'Plus Jakarta Sans', sans-serif !important;
            } */
        </style>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.Alpine && !window.Alpine.store('theme')) {
                    window.Alpine.store('theme', localStorage.getItem('theme') || 'light');
                }
            });
        </script>
        <tallstackui:script />
        @filamentStyles

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- inject style laravel --}}
        @stack('style')
    </head>

    <body x-data="sidebar()" class="flex h-screen bg-gray-100 antialiased" @resize.window="handleResize()">
        {{-- toast , dialog --}}
        <x-ts:toast />
        <x-ts:dialog />

        {{-- loading pages --}}
        <x-loading-page />


        @auth
            <div class="flex h-full w-full flex-row overflow-hidden">
                {{-- SIDEBAR --}}
                <livewire:Partials.Sidebar key="sidebar" />

                <!-- Main content -->
                <div id="main-content" class="flex max-h-screen flex-1 flex-col overflow-hidden transition-all duration-300">

                    {{-- NAVBAR --}}
                    <div class="mx-6 mt-6 h-16 shrink-0 rounded-md bg-white shadow-md">
                        <livewire:Partials.Navbar :title="isset($title) ? $title : config('app.name')" key="navbar" />
                    </div>

                    {{-- CONTENT --}}
                    <main id="main" class="scrollbar-hidden flex-1 overflow-y-auto px-6 py-4">
                        {{ $slot }}
                    </main>

                </div>
            </div>
        @endauth

        @filamentScripts

        {{-- Inject Script Laravel --}}
        @stack('script')
    </body>

</html>
