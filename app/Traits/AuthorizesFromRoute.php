<?php

namespace App\Traits;

use Exception;

trait AuthorizesFromRoute
{
    public string $currentRouteName = '';

    public function bootAuthorizesFromRoute(): void
    {

        if (!empty($this->currentRouteName)) return;

        $currentRoute = request()->route();
        $routeName = $currentRoute ? $currentRoute->getName() : null;

        if ($routeName && !str_contains($routeName, 'livewire.update')) {
            $this->currentRouteName = $routeName;
            return;
        }

        // Ambil dari Referer header — jika ini request via livewire update
        $referer = request()->header('referer');

        if (!$referer) return;

        try {
            $request   = request()->create($referer);
            $route     = app('router')->getRoutes()->match($request);
            $routeName = $route->getName();

            if ($routeName && !str_contains($routeName, 'livewire.update')) {
                $this->currentRouteName = $routeName;
            }
        } catch (Exception) {
            // route tidak ditemukan
        }
    }

    protected function buildPermission(): string
    {
        $segments = explode('.', $this->currentRouteName);
        $last     = end($segments);

        $actions = ['index', 'show', 'create', 'edit', 'delete'];

        // Jika segmen terakhir adalah action, buang dari resource
        if (in_array($last, $actions)) {
            array_pop($segments);
        }

        $resource = implode('-', $segments);
        $action = $this->getActionFromComponent();

        // dd("{$action}-{$resource}");
        return "{$action}-{$resource}";
    }

    protected function getActionFromComponent(): string
    {
        $className = strtolower(class_basename(static::class));

        return match ($className) {
            'index', 'show' => 'view',
            'add'         => 'add',
            'edit'        => 'edit',
            'delete'      => 'delete',
            default       => $className
        };
    }

    protected function authorizeFromRoute(): void
    {
        if (empty($this->currentRouteName)) {
            return;
        }

        $permission = $this->buildPermission();

        // Bypassing permission check untuk Koordinator Ruangan / Atasan pada menu utama kepegawaian
        if ((auth()->user()?->isKoordinator() || auth()->user()?->isKepalaDept() || auth()->user()?->isWadir()) && in_array($permission, [
            'view-kepegawaian-jadwal-kerja',
            'view-kepegawaian-absensi',
            'view-kepegawaian-konfigurasi-jadwal',
            'view-kepegawaian-surat-cuti',
            'view-kepegawaian-surat-sp3',
        ])) {
            return;
        }

        abort_unless(
            auth()->user()?->can($permission),
            403,
            "Tidak memiliki akses: {$permission}"
        );
    }
}
