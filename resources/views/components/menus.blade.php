<nav class="flex flex-col gap-4">
    {{ $slot }}

    {{-- Menus with submenus --}}
    @foreach ($menus as $groupName => $group)
        <div class="relative rounded-lg border border-gray-200">
            <span class="absolute -left-0 -top-3 rounded-xl bg-white px-2 text-sm text-indigo-500">
                {{ $groupName }}
            </span>

            <div class="my-2 flex flex-col gap-1">
                @foreach ($group as $menu)
                    <x-menu-item :menu="$menu" :active="request()->routeIs($menu['route'])" />
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
