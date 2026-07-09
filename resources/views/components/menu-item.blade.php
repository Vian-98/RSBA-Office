@props(['menu', 'active'])

@php
    $baseClass = $active ?? false
        ? 'bg-primary-500 text-white shadow-xs'
        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';

    // conditional submenu active or not, when active open carret icon
    $submenuActive = false;
    if (!empty($menu['submenus'])) {
        foreach ($menu['submenus'] as $submenu) {
            if (request()->routeIs($submenu['route'])) {
                $submenuActive = true;
                break;
            }

            if (!empty($submenu['submenus'])) {
                foreach ($submenu['submenus'] as $subsubmenu) {
                    if (request()->routeIs($subsubmenu['route'])) {
                        $submenuActive = true;
                        break;
                    }
                }
            }
        }
    }
@endphp

{{-- menu with or without submenu --}}
@if (!empty($menu['submenus']))
    <div {{ $attributes->merge([]) }} 
         :class="isCollapsed && isAboveBreakpoint ? 'justify-center mx-1 px-1' : 'justify-between mx-2 px-3'"
         class="py-2.5 rounded-lg transition-all duration-200 cursor-pointer flex items-center relative group {{ $baseClass }}"
         onclick="toggleMenu('{{ $menu['id'] }}')">

        <div class="flex items-center gap-3 min-w-0">
            {{-- icon --}}
            @if ($menu['icon'])
                <x-ts:icon name="tabler.{{ $menu['icon'] }}" class="h-5 w-5 shrink-0" />
            @endif
            {{-- title --}}
            <span x-show="!(isCollapsed && isAboveBreakpoint)"
                  x-transition:enter="transition ease-out duration-150"
                  x-transition:enter-start="opacity-0 transform -translate-x-1"
                  x-transition:enter-end="opacity-100 transform translate-x-0"
                  class="text-xs font-semibold whitespace-nowrap overflow-hidden text-ellipsis">{{ $menu['nama'] }}</span>
        </div>

        <svg id="{{ $menu['id'] }}Icon" 
             x-show="!(isCollapsed && isAboveBreakpoint)"
             class="h-3.5 w-3.5 transform transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>

        <!-- Tooltip -->
        <span x-show="isCollapsed && isAboveBreakpoint"
              class="absolute left-full ml-4 px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
            {{ $menu['nama'] }}
        </span>
    </div>
@else
    <a href="{{ (!empty($menu['route']) && Route::has($menu['route'])) ? route($menu['route']) : '#' }}" 
       {{ $attributes->merge([]) }} 
       :class="isCollapsed && isAboveBreakpoint ? 'justify-center mx-1 px-1' : 'justify-start gap-3 mx-2 px-3'"
       class="py-2.5 rounded-lg transition-all duration-200 cursor-pointer flex items-center relative group {{ $baseClass }}" 
       wire:navigate>

        {{-- icons --}}
        @if ($menu['icon'])
            <x-ts:icon name="tabler.{{ $menu['icon'] }}" class="h-5 w-5 shrink-0" />
        @endif

        {{-- title --}}
        <span x-show="!(isCollapsed && isAboveBreakpoint)"
              x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0 transform -translate-x-1"
              x-transition:enter-end="opacity-100 transform translate-x-0"
              class="text-xs font-semibold whitespace-nowrap overflow-hidden text-ellipsis">{{ $menu['nama'] }}</span>

        <!-- Tooltip -->
        <span x-show="isCollapsed && isAboveBreakpoint"
              class="absolute left-full ml-4 px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
            {{ $menu['nama'] }}
        </span>
    </a>
@endif

{{-- submenu dependence --}}
@if (!empty($menu['submenus']))
    <div id="{{ $menu['id'] }}" 
         x-show="!(isCollapsed && isAboveBreakpoint)"
         {{ $attributes->merge(['class' => 'submenu ml-4 ' . ($submenuActive ? 'active' : 'hidden')]) }}>
        <div class="ml-2 space-y-1 border-l-2 border-indigo-500/25">
            @foreach ($menu['submenus'] as $submenu)
                <x-menu-item :menu="$submenu" :active="request()->routeIs($submenu['route'])" />
            @endforeach
        </div>
    </div>
@endif

@push('script')
    <script defer>
        function toggleMenu(menuId) {
            const menu = document.getElementById(menuId);
            const menuIcon = document.getElementById(menuId + 'Icon');

            if(menu && menuIcon) {
                menu.classList.toggle('hidden');
                menuIcon.classList.toggle('rotate-180');
            }
        }

        // Select all elements with the class 'active'
        var activeElements = document.getElementsByClassName('active');

        // Loop through all elements and remove the 'hidden' class
        Array.from(activeElements).forEach((element) => {
            element.classList.remove('hidden');
        });
    </script>
@endpush
