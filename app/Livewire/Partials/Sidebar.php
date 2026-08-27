<?php

namespace App\Livewire\Partials;

use App\Models\Menu;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
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
        $this->menus = $this->applySearchFilter($allMenus);
    }

    /**
     * Get full menu tree filtered by user permissions (cached per user)
     */
    private function getPermittedMenus(int $userId): array
    {
        $cacheKey = 'user-sidebar-menu:' . $userId;

        return cache()->remember($cacheKey, 60, function () use ($userId) {
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
     * Base menu structure ΓÇö cached globally (no user/search context)
     */
    private function getCachedBaseMenus(): array
    {
        return cache()->remember('user-sidebar-menu:base', 60 * 720, function () {
            $mainMenu = Menu::first();
            if (!$mainMenu) {
                return [];
            }

            return Menu::where('parent_id', $mainMenu->id)
                ->with('submenus')
                ->orderBy('group')
                ->orderBy('nama')
                ->get()
                ->map(fn($menu) => [
                    'id'           => $menu->id,
                    'nama'         => $menu->nama,
                    'route'        => $menu->route ?? '',
                    'route_params' => $menu->route_params ?? [],
                    'icon'         => $menu->icon ?? '',
                    'permission'   => $menu->permission ?? '',
                    'group'        => $menu->group?->nama() ?? '',
                    'submenus'   => $menu->submenus
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

    private function getCachedUserViewPermissions(int $userId): array
    {
        return cache()->remember('user-permissions:view:' . $userId, 60, function () {
            $user = Auth::user();

            $all = method_exists($user, 'getAllPermissions')
                ? $user->getAllPermissions()->pluck('name')->toArray()
                : $user->permissions->pluck('name')->toArray();

            $permissions = array_values(array_filter($all, fn($p) => str_starts_with($p, 'view')));

            // Tambahkan permission view koordinator & atasan jika user adalah koordinator / kepala dept / wadir
            // if ($user && ($user->isKoordinator() || $user->isKepalaDept() || $user->isWadir())) {
            //     $permissions = array_merge($permissions, [
            //         'view-kepegawaian-jadwal-kerja',
            //         'view-kepegawaian-konfigurasi-jadwal',
            //         'view-kepegawaian-surat-cuti',
            //         'view-kepegawaian-surat-sp3',
            //     ]);
            // }

            // $isAuthorizedSDM = $user && (
            //     $user->can('view-kepegawaian-karyawan')
            //     || $user->isKabagSDM()
            // );

            // if ($isAuthorizedSDM) {
            //     $permissions = array_merge($permissions, [
            //         'view-kepegawaian-jadwal-kerja',
            //         'view-kepegawaian-konfigurasi-jadwal',
            //         'view-kepegawaian-karyawan',
            //         'view-kepegawaian-laporan',
            //         'view-kepegawaian-master',
            //         'view-kepegawaian-penggajian',
            //         'view-kepegawaian-akreditasi',
            //     ]);
            // } else {
            //     // Cabut hak akses administrasi SDM global dari user non-SDM (seperti Kabag Umum, Kabag Farmasi, dll.)
            //     $permissions = array_values(array_filter($permissions, function ($p) {
            //         return !in_array($p, [
            //             'view-kepegawaian-karyawan',
            //             'view-kepegawaian-master',
            //             'view-kepegawaian-master-bagian',
            //             'view-kepegawaian-master-jabatan',
            //             'view-kepegawaian-master-ruangan',
            //             'view-kepegawaian-master-spesialisasi',
            //             'view-kepegawaian-master-cuti',
            //             'view-kepegawaian-akreditasi',
            //             'view-kepegawaian-penggajian',
            //             'view-kepegawaian-master-tunjangan-golongan',
            //             'view-kepegawaian-master-aturan-pajak',
            //             'view-kepegawaian-gaji',
            //             'view-karyawan',
            //             'view-master',
            //         ]);
            //     }));
            // }

            // Modul Umum & Asset: Hanya untuk Kabag Umum / Wadir SDM-Umum / Super-Admin / Bagian-Umum
            // if ($user && ($user->isKabagUmum() || $user->isWadir() || $user->hasRole(['Super-Admin', 'Bagian-Umum']))) {
            //     $permissions = array_merge($permissions, [
            //         'view-umum-asset',
            //         'view-umum-pengajuan',
            //         'view-umum-gudang',
            //         'view-umum-distribusi',
            //     ]);
            // }

            // Modul Keuangan: Hanya untuk Kabag Keuangan / Super-Admin / Keuangan
            // if ($user && ($user->isKabagKeuangan() || $user->hasRole(['Super-Admin', 'Keuangan']))) {
            //     $permissions = array_merge($permissions, [
            //         'view-keuangan-hutang',
            //         'view-keuangan-piutang',
            //         'view-keuangan-laporan',
            //         'view-keuangan-akuntansi-coa',
            //         'view-keuangan-akuntansi-jurnal-umum',
            //         'view-keuangan-master-rekanan',
            //         'view-kepegawaian-jasmed',
            //         'view-kepegawaian-gaji',
            //         'view-kepegawaian-penggajian',
            //         'view-kepegawaian-surat-cuti',
            //         'view-kepegawaian-surat-sp3',
            //     ]);
            // }

            // Kabag Operasional (Farmasi, Medis, KEP, dll.):
            // Diberikan akses Jadwal Kerja (Approval & View Departemen)
            // if ($user && $user->isKepalaDept()) {
            //     if (!in_array('view-kepegawaian-jadwal-kerja', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-jadwal-kerja';
            //     }
            // }

            // Tim Pajak otomatis mendapatkan akses menu Pajak PPh 21, Rekap Gaji, Karyawan, & Dokter
            // if ($user && ($user->hasRole('Pajak') || $user->hasRole('Super-Admin'))) {
            //     if (!in_array('view-kepegawaian-master-aturan-pajak', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-master-aturan-pajak';
            //     }
            //     if (!in_array('view-kepegawaian-gaji', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-gaji';
            //     }
            //     if (!in_array('view-kepegawaian-gaji-index', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-gaji-index';
            //     }
            //     if (!in_array('view-kepegawaian-karyawan', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-karyawan';
            //     }
            //     if (!in_array('view-dokter', $permissions)) {
            //         $permissions[] = 'view-dokter';
            //     }
            // }

            // Setiap Karyawan / Dokter otomatis memiliki akses ke menu "Jadwal Tugas Saya"
            if ($user && ($user->karyawan_id || $user->isDokter())) {
                if (!in_array('view-profile-jadwal-tugas-saya', $permissions)) {
                    $permissions[] = 'view-profile-jadwal-tugas-saya';
                }
            }
            // Dokter otomatis memiliki akses melihat "Jadwal Kerja"
            // if ($user && $user->isDokter()) {
            //     if (!in_array('view-kepegawaian-jadwal-kerja', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-jadwal-kerja';
            //     }
            // }

            // Filter ketersediaan menu Jadwal Kerja sesuai wewenang user
            // if ($user && ($user->can('view-kepegawaian-jadwal-kerja') || $user->isDokter() || $user->isKoordinator())) {
            //     if (!in_array('view-kepegawaian-jadwal-kerja', $permissions)) {
            //         $permissions[] = 'view-kepegawaian-jadwal-kerja';
            //     }
            // } else {
            //     $permissions = array_values(array_filter($permissions, fn($p) => $p !== 'view-kepegawaian-jadwal-kerja'));
            // }

            // Allow users with assigned ruangan to view the asset & pengajuan menu
            // if ($user?->karyawan?->ruangan_id) {
            //     if (!in_array('view-umum-asset', $permissions)) {
            //         $permissions[] = 'view-umum-asset';
            //     }
            //     if (!in_array('view-umum-pengajuan', $permissions)) {
            //         $permissions[] = 'view-umum-pengajuan';
            //     }
            // }

            return $permissions;
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
                $submenuPermissions = !empty($submenu['permission']) ? (array) $submenu['permission'] : [];
                if (empty($submenuPermissions)) {
                    return true;
                }
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
