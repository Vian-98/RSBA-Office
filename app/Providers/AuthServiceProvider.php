<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Sdm\JadwalKerja;
use App\Policies\JadwalKerjaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(JadwalKerja::class, JadwalKerjaPolicy::class);

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super-Admin') || $user->hasPermissionTo('super-admin-bypass')) {
                return true;
            }

            if ($user->isKoordinator()) {
                $allowedAbilities = [
                    'view-kepegawaian-jadwal-kerja',
                    'add-kepegawaian-jadwal-kerja',
                    'edit-kepegawaian-jadwal-kerja',
                    'delete-kepegawaian-jadwal-kerja',
                    'view-kepegawaian-absensi',
                    'view-kepegawaian-konfigurasi-jadwal',
                    'view-kepegawaian-surat-cuti',
                    'view-kepegawaian-surat-sp3',
                ];
                if (in_array($ability, $allowedAbilities)) {
                    return true;
                }
            }

            if ($ability === 'view-kepegawaian-jadwal-kerja') {
                if (
                    $user->hasPermissionTo('view-kepegawaian-jadwal-kerja') ||
                    $user->isKoordinator() ||
                    $user->isDokterOrApprover() ||
                    ($user->karyawan && $user->karyawan->kategori_kerja === \App\Enums\KategoriKerja::SHIFT)
                ) {
                    return true;
                }
                return false;
            }

            return null;
        });
    }
}

