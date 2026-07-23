<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $title ?? config('app.name') }}</title>

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
    </head>

    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
        <x-ts:toast />
        <div class="min-h-screen py-4 px-3 sm:py-6 sm:px-6 lg:px-8">
            <div class="flex w-full flex-col rounded-2xl bg-white border border-slate-200/80 p-3 sm:p-5 shadow-sm">
                {{ $slot }}
            </div>
        </div>

        @filamentScripts
    </body>

</html>
