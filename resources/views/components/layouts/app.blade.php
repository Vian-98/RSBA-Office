<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ isset($title) ? config('app.name') . " | $title" : config('app.name') }}</title>
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
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
                <div id="main-content" class="max-h-screen flex-1 flex flex-col overflow-hidden transition-all duration-300">

                    {{-- NAVBAR --}}
                    <div class="h-16 rounded-md bg-white shadow-md shrink-0 mx-6 mt-6">
                        <livewire:Partials.Navbar :title="isset($title) ? $title : config('app.name')" key="navbar" />
                    </div>

                    {{-- CONTENT --}}
                    <main id="main" class="flex-1 overflow-y-auto scrollbar-hidden px-6 py-4">
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
