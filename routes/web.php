<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->group(function () {
        Route::get('/', App\Livewire\Auth\Login::class)->name('login');

        Route::get('/register', App\Livewire\Auth\Register::class)->name('register');
    });

Route::middleware('auth')
    ->prefix('profile')
    ->name('profile.')
    ->group(function () {

        Route::get('/', App\Livewire\Profile\Index::class)->name('index');
        Route::get('/notif', App\Livewire\Profile\Notif::class)->name('notif');
        Route::get('/setting', App\Livewire\Profile\Setting::class)->name('setting');
        Route::get('/jadwal-tugas-saya', App\Livewire\Profile\JadwalTugasSaya::class)->name('jadwal-tugas-saya');
    });

Route::prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        // Admin Panel URL
        Route::middleware(['auth'])
            ->get('/display-monitor/admin', App\Livewire\Dashboard\DisplayMonitorAdmin::class)
            ->name('display-monitor.admin');
    });


Route::middleware('auth')
    ->group(function () {

        Route::get('/dashboard', App\Livewire\Dashboard\Home::class)->name('dashboard');
    });

Route::get('surat/verification', App\Livewire\Surat\Verifikasi\Index::class)->name('surat.verification');

// // Administrator
// Route::middleware('auth')
//     ->prefix('admin')
//     ->name('admin.')
//     ->group(function () {

//         Route::prefix('user')
//             ->name('user.')
//             ->group(function () {
//                 Route::get('/', App\Livewire\User\Index::class)->name('index');
//             });

//         Route::prefix('settings')
//             ->name('settings.')
//             ->group(function () {
//                 Route::get('/menu', App\Livewire\Settings\Menu\Index::class)->name('menu');
//                 Route::get('/perusahaan', App\Livewire\Settings\Perusahaan\Index::class)->name('perusahaan');
//                 Route::get('/role', App\Livewire\Settings\Role\Index::class)->name('role');
//                 Route::get('/permission', App\Livewire\Settings\Permission\Index::class)->name('permission');
//             });
//     });
