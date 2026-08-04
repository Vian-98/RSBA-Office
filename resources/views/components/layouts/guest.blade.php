<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <script>
            document.addEventListener('alpine:init', () => {
                if (window.Alpine && !window.Alpine.store('theme')) {
                    window.Alpine.store('theme', localStorage.getItem('theme') || 'light');
                }
            });
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <tallstackui:script />
        @filamentStyles
    </head>

    <body class="h-screen antialiased">
        {{-- Toast --}}
        <x-ts:toast />

        <div class="flex min-h-screen flex-col justify-center py-12 sm:px-6 lg:px-8">

            <div class="mx-auto w-full max-w-7xl">
                <div class="flex flex-col lg:flex-row">
                    <div class="relative w-full bg-gradient-to-r from-white via-white to-primary-100 bg-cover lg:w-6/12 xl:w-7/12">
                        <div class="relative my-20 flex h-full w-full flex-col items-center justify-center px-10 lg:my-0 lg:px-16">
                            <div class="flex flex-col items-start space-y-8 tracking-tight lg:max-w-3xl">
                                <div class="relative">
                                    <p class="mb-2 font-medium uppercase text-gray-700">{{ config('app.name') }}</p>
                                    <h2 class="text-5xl font-bold text-gray-900 xl:text-6xl">RS Bintang Amin</h2>
                                </div>
                                <p class="text-2xl text-gray-700">
                                    Pelayanan Prima, Sehat Milik Semua <br>
                                    <span class="italic">We Care, We Cure</span>
                                </p>
                             </div>
                        </div>
                    </div>
                    <div class="w-full rounded-lg bg-white shadow-lg shadow-indigo-300 lg:w-6/12 xl:w-5/12">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @filamentScripts
    </body>

</html>
