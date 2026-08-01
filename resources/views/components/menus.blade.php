<nav class="flex flex-col gap-4">
    {{ $slot }}

    {{-- Menus with submenus --}}
    @foreach ($menus as $groupName => $group)
        <div class="relative rounded-lg border transition-all duration-200"
             :class="isCollapsed && isAboveBreakpoint ? 'border-transparent my-1' : 'border-gray-200 p-2'">
            
            <span x-show="!(isCollapsed && isAboveBreakpoint)"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 scale-95"
                  x-transition:enter-end="opacity-100 scale-100"
                  class="absolute -left-0 -top-3 rounded-xl bg-white px-2 text-xs font-bold uppercase tracking-wider text-indigo-500">
                {{ $groupName }}
            </span>

            <div class="flex flex-col gap-1" :class="isCollapsed && isAboveBreakpoint ? '' : 'mt-1'">
                @foreach ($group as $menu)
                    <x-menu-item :menu="$menu" :active="request()->routeIs($menu['route'])" />
                @endforeach
            </div>
        </div>
        @if (!$loop->last)
            <hr x-show="isCollapsed && isAboveBreakpoint" class="border-t border-slate-200 my-3 mx-4 transition-all duration-200">
        @endif
    @endforeach
</nav>
