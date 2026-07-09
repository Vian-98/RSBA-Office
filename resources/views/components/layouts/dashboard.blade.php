<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $title ?? config('app.name') }}</title>

        <tallstackui:script />
        @filamentStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-screen">
        <x-ts:toast />
        <div class="min-h-screen rounded-lg py-6 shadow-lg sm:px-6 lg:px-8">
            <div class="space-y-auto flex w-full flex-col rounded-lg border border-gray-200 p-2">
                {{ $slot }}
            </div>
        </div>

        @filamentScripts
    </body>

</html>
