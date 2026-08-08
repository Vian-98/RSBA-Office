@props([
    'title' => '',
    'icon' => null,
    'defaultOpen' => true,
    'color' => 'indigo',
])

<div x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }" class="rounded-md bg-white p-2">
    {{-- Header --}}
    <div class="text-{{ $color }}-500 flex flex-row items-center justify-between gap-2">
        <div class="flex items-center gap-2 font-medium">
            @if ($icon)
                <x-dynamic-component :component="$icon" class="h-5 w-5" />
            @endif
            {{ $title }}
        </div>
        <button @click="open = !open"
            class="text-{{ $color }}-400 hover:bg-{{ $color }}-50 hover:text-{{ $color }}-600 flex items-center justify-center rounded p-1 transition-colors"
            :title="open ? 'Minimize' : 'Maximize'">
            {{-- Minimize icon --}}
            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="4 14 10 14 10 20" />
                <polyline points="20 10 14 10 14 4" />
                <line x1="10" y1="14" x2="21" y2="3" />
                <line x1="3" y1="21" x2="14" y2="10" />
            </svg>
            {{-- Maximize icon --}}
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <polyline points="15 3 21 3 21 9" />
                <polyline points="9 21 3 21 3 15" />
                <line x1="21" y1="3" x2="14" y2="10" />
                <line x1="3" y1="21" x2="10" y2="14" />
            </svg>
        </button>
    </div>

    {{-- Content --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2 w-full">
        {{ $slot }}
    </div>
</div>
