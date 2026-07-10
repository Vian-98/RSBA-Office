<?php

namespace App\Livewire\Partials;

use App\Models\Menu;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;

#[Isolate]
class Sidebar extends Component
{
    public $menus = [];
    public string $searchMenu = '';

    public function mount(): void
    {
        $this->loadMenus();
    }

    public function updatedSearchMenu(): void
    {
        // saat pencarian
        $this->loadMenus();
    }

    #[On('updated-role-user')]
    #[On('updated-permission-user')]
    #[On('new-role-created')]
    #[On('new-permission-created')]
    #[On('menu-updated')]
    #[On('new-menu-created')]
    public function refreshMenus(): void
    {
        // Hapus cache milik user yang sedang login agar perubahan role/permission langsung berefek di UI-nya
        cache()->forget('user-sidebar-menu:' . auth()->id());
        cache()->forget('user-permissions:view:' . auth()->id());
        cache()->forget('user-sidebar-menu:base');
        
        $this->loadMenus();
    }

    /**
     * Main entry point: load + filter + search menus into $this->menus
     */
    private function loadMenus(): void
    {
        $allMenus = $this->getPermittedMenus(auth()->id());
        $allMenus = $this->injectAkreditasiSubMenus($allMenus);
        $this->menus = $this->applySearchFilter($allMenus);
    }

    /**
     * Get full menu tree filtered by user permissions (cached per user)
     */
    private function getPermittedMenus(int $userId): array
    {
        $cacheKey = 'user-sidebar-menu:' . $userId;

        return cache()->remember($cacheKey, 60 * 60, function () use ($userId) {
            $allMenus = $this->getCachedBaseMenus();

            if (Auth::user()->hasRole('Super-Admin')) {
                return $allMenus;
            }

            $userViewPermissions = $this->getCachedUserViewPermissions($userId);

            return collect($allMenus)
                ->map(function ($groupedMenus, $groupName) use ($userViewPermissions) {
                    $filtered = collect($groupedMenus)
                        ->map(fn($menu) => $this->filterMenuWithViewPermissions($menu, $userViewPermissions))
                        ->filter()
                        ->values()
                        ->toArray();

                    return count($filtered) > 0 ? [$groupName => $filtered] : null;
                })
                ->filter()
                ->collapse()
                ->toArray();
        });
    }

    /**
     * Base menu structure — cached globally (no user/search context)
     */
    private function getCachedBaseMenus(): array
    {
        return cache()->remember('user-sidebar-menu:base', 60 * 720, function () {
            $mainMenu = Menu::first();

            return Menu::where('parent_id', $mainMenu->id)
                ->with('submenus')
                ->orderBy('group')
                ->orderBy('nama')
                ->get()
                ->map(fn($menu) => [
                    'id'         => $menu->id,
                    'nama'       => $menu->nama,
                    'route'      => $menu->route ?? '',
                    'icon'       => $menu->icon ?? '',
                    'permission' => $menu->permission ?? '',
                    'group'      => $menu->group?->nama() ?? '',
                    'submenus'   => $menu->submenus
                        ->sortBy('nama')
                        ->map(fn($sub) => [
                            'id'           => $sub->id,
                            'nama'         => $sub->nama,
                            'route'        => $sub->route ?? '',
                            'route_params' => $sub->route_params ?? [],
                            'icon'         => $sub->icon ?? '',
                            'permission'   => $sub->permission ?? '',
                            'group'        => $sub->group?->nama() ?? '',
                        ])->values()->toArray(),
                ])
                ->groupBy('group')
                ->toArray();
        });
    }

    /**
     * Inject dynamic akreditasi sub-menus (kegiatan + chapters) from DB
     * under the Akreditasi parent menu (id = 40)
     */
    private function injectAkreditasiSubMenus(array $menus): array
    {
        $user = Auth::user();
        $hasAccess = $user?->hasRole('Super-Admin')
            || $user?->can('view-kepegawaian-akreditasi')
            || $user?->can('assesor-akreditasi');

        if (!$hasAccess) {
            return $menus;
        }

        try {
            $latestKegiatan = DB::table('akre_kegiatan')
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable $e) {
            return $menus;
        }

        $dynamicSubMenus = [];

        if ($latestKegiatan) {
            $dynamicSubMenus[] = [
                'id'           => 'akre-standar-dinamis',
                'nama'         => 'Standar Akreditasi',
                'route'        => 'kepegawaian.akreditasi.chapters',
                'route_params' => ['uuid' => $latestKegiatan->uuid],
                'icon'         => '',
                'permission'   => [],
                'group'        => 'sdm',
            ];
        }

        // Inject into Akreditasi parent (id = 40)
        foreach ($menus as $group => &$groupMenus) {
            foreach ($groupMenus as &$menu) {
                if ((int)$menu['id'] === 40) {
                    $menu['submenus'] = array_merge(
                        $menu['submenus'],   // existing: "Semua Kegiatan" (id=64)
                        $dynamicSubMenus
                    );
                    break 2;
                }
            }
        }
        unset($groupMenus, $menu);

        return $menus;
    }



    private function applySearchFilter(array $menus): array
    {
        if (empty($this->searchMenu)) {
            return $menus;
        }

        $search = strtolower($this->searchMenu);
        $result = [];

        foreach ($menus as $group => $groupMenus) {
            $matched = array_values(array_filter(
                array_map(function ($menu) use ($search) {
                    $menuMatches = str_contains(strtolower($menu['nama']), $search);

                    $menu['submenus'] = array_values(array_filter(
                        $menu['submenus'],
                        fn($sub) => str_contains(strtolower($sub['nama']), $search)
                    ));

                    // Include menu if its name matches OR it has matching submenus
                    return ($menuMatches || !empty($menu['submenus'])) ? $menu : null;
                }, $groupMenus)
            ));

            if (!empty($matched)) {
                $result[$group] = $matched;
            }
        }

        return $result;
    }

    /**
     * Get only 'view' permissions for a user (cached per user)
     */
    private function getCachedUserViewPermissions(int $userId): array
    {
        return cache()->remember('user-permissions:view:' . $userId, 60 * 60, function () {
            $user = Auth::user();

            $all = method_exists($user, 'getAllPermissions')
                ? $user->getAllPermissions()->pluck('name')->toArray()
                : $user->permissions->pluck('name')->toArray();

            return array_values(array_filter($all, fn($p) => str_starts_with($p, 'view')));
        });
    }

    /**
     * Filter a single menu item and its submenus by view permissions
     */
    private function filterMenuWithViewPermissions(array $menu, array $userViewPermissions): ?array
    {
        $menuPermissions    = !empty($menu['permission']) ? $menu['permission'] : [];
        $hasParentPermission = !empty(array_intersect($menuPermissions, $userViewPermissions));

        $permittedSubmenus = collect($menu['submenus'] ?? [])
            ->filter(function ($submenu) use ($userViewPermissions) {
                $submenuPermissions = !empty($submenu['permission']) ? $submenu['permission'] : [];
                return !empty(array_intersect($submenuPermissions, $userViewPermissions));
            })
            ->values()
            ->toArray();

        if ($hasParentPermission || count($permittedSubmenus) > 0) {
            $menu['submenus'] = $permittedSubmenus;
            return $menu;
        }

        return null;
    }

    public function logout(): void
    {
        try {
            Auth::guard('web')->logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->redirect(\App\Livewire\Auth\Login::class, navigate: true);
        } catch (\Throwable $e) {
            // silent fail
        }
    }

    public function render()
    {
        return view('livewire.partials.sidebar');
    }
}
