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
            if ($user->hasRole('Super-Admin')) {
                return true;
            }

            if ($user->isKoordinator()) {
                $allowedAbilities = [
                    'view-kepegawaian-jadwal-kerja',
                    'add-kepegawaian-jadwal-kerja',
                    'edit-kepegawaian-jadwal-kerja',
                    'delete-kepegawaian-jadwal-kerja',
                    'view-kepegawaian-absensi',
                ];
                if (in_array($ability, $allowedAbilities)) {
                    return true;
                }
            }

            // Izinkan semua user biasa yang login untuk melihat/mengakses halaman index jadwal kerja
            if ($ability === 'view-kepegawaian-jadwal-kerja') {
                return true;
            }

            return null;
        });
    }
}
