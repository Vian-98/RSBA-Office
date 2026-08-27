@props(['menu', 'active'])


@php
    $class =
        $active ?? false
            ? 'py-1.5 px-2 mx-3 rounded-md transition duration-200 cursor-pointer bg-primary-500 text-white flex justify-between items-center'
            : 'py-1.5 px-2 mx-3 rounded-md transition duration-200 hover:bg-indigo-500/20 cursor-pointer flex justify-between items-center';

    // conditional submenu active or not, when active open carret icon
    $submenuActive = false;
    if (!empty($menu['submenus'])) {
        foreach ($menu['submenus'] as $submenu) {
            if (!empty($submenu['route']) && request()->routeIs($submenu['route'])) {
                $submenuActive = true;
                break;
            }

            if (!empty($submenu['submenus'])) {
                foreach ($submenu['submenus'] as $subsubmenu) {
                    if (!empty($subsubmenu['route']) && request()->routeIs($subsubmenu['route'])) {
                        $submenuActive = true;
                        break;
                    }
                }
            }
        }
    }

    $menuHref = (!empty($menu['route']) && \Illuminate\Support\Facades\Route::has($menu['route'])) 
        ? route($menu['route']) 
        : '#';
@endphp

{{-- @can('view-' . $menu['id']) --}}
{{-- menu with or without submenu --}}
@if (!empty($menu['submenus']))
    <div {{ $attributes->merge(['class' => $class]) }} onclick="toggleMenu('{{ $menu['id'] }}')">

        <div class="flex items-center gap-2">
            {{-- icon --}}
            @if ($menu['icon'])
                @php
                    $iconName = 'tabler.' . ltrim(str_replace('tabler.', '', $menu['icon']), '.');
                @endphp
                <x-ts:icon :name="$iconName" class="h-5 w-5 shrink-0" />
            @endif
            {{-- title --}}
            {{ $menu['nama'] }}
        </div>

        <svg id="{{ $menu['id'] }}Icon" class="h-4 w-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    </div>
@else
    <a href="{{ $menuHref }}" {{ $attributes->merge(['class' => $class]) }} @if($menuHref !== '#') wire:navigate @endif>

        <div class="flex items-center gap-2">
            {{-- icons --}}
            @if ($menu['icon'])
                @php
                    $iconName = 'tabler.' . ltrim(str_replace('tabler.', '', $menu['icon']), '.');
                @endphp
                <x-ts:icon :name="$iconName" class="h-5 w-5 shrink-0" />
            @endif

            {{-- title --}}
            {{ $menu['nama'] }}
        </div>

    </a>
@endif


{{-- submenu dependence --}}
@if (!empty($menu['submenus']))
    <div id="{{ $menu['id'] }}" {{ $attributes->merge(['class' => 'submenu ml-4 ' . ($submenuActive ? 'active' : 'hidden')]) }}>
        <div class="ml-2 space-y-1 border-l-2 border-indigo-500/25">
            @foreach ($menu['submenus'] as $submenu)
                {{-- {{ request()->routeIs($submenu['route']) }} --}}
                <x-menu-item :menu="$submenu" :active="request()->routeIs($submenu['route'])" />
            @endforeach
        </div>
    </div>
@endif
{{-- @endcan --}}


@push('script')
    <script defer>
        function toggleMenu(menuId) {
            const menu = document.getElementById(menuId);
            const menuIcon = document.getElementById(menuId + 'Icon');

            menu.classList.toggle('hidden')
            menuIcon.classList.toggle('rotate-180');
        }

        // Select all elements with the class 'active'
        var activeElements = document.getElementsByClassName('active');

        // Loop through all elements and remove the 'hidden' class
        Array.from(activeElements).forEach((element) => {
            element.classList.remove('hidden');
        });
    </script>
@endpush
