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

        Route::middleware(['auth'])
            ->get('/poli/admin', App\Livewire\Dashboard\PoliAdmin::class)
            ->name('poli.admin');
    });


Route::middleware('auth')
    ->group(function () {

        Route::get('/dashboard', App\Livewire\Dashboard\Home::class)->name('dashboard');
    });

Route::get('surat/verification', App\Livewire\Surat\Verifikasi\Index::class)->name('surat.verification');

// Public Document Verification Portal
Route::get('/verifikasi-surat/{hash?}', App\Livewire\Public\VerifyDocument::class)->name('surat.verifikasi.publik');

// Explicit PDF & Download Routes Fallback for Herd/Valet
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/kepegawaian/surat/balasan-pkl/{id}/pdf', [App\Http\Controllers\Surat\SuratBalasanPklPdfController::class, 'download'])->name('kepegawaian.surat.balasan-pkl.pdf');
    Route::get('/kepegawaian/surat/balasan-pkl/{id}/preview-pdf', [App\Http\Controllers\Surat\SuratBalasanPklPdfController::class, 'stream'])->name('kepegawaian.surat.balasan-pkl.preview-pdf');

    Route::get('/kepegawaian/surat/balasan-penelitian/{id}/pdf', [App\Http\Controllers\Surat\SuratBalasanPenelitianPdfController::class, 'download'])->name('kepegawaian.surat.balasan-penelitian.pdf');
    Route::get('/kepegawaian/surat/balasan-penelitian/{id}/preview-pdf', [App\Http\Controllers\Surat\SuratBalasanPenelitianPdfController::class, 'stream'])->name('kepegawaian.surat.balasan-penelitian.preview-pdf');

    Route::get('/kepegawaian/surat/perintah-tugas/{id}/pdf', [App\Http\Controllers\Surat\SuratPerintahTugasPdfController::class, 'download'])->name('kepegawaian.surat.perintah-tugas.pdf');
    Route::get('/kepegawaian/surat/perintah-tugas/{id}/preview-pdf', [App\Http\Controllers\Surat\SuratPerintahTugasPdfController::class, 'stream'])->name('kepegawaian.surat.perintah-tugas.preview-pdf');

    // Disposisi Routes
    Route::get('/kepegawaian/surat/disposisi', App\Livewire\Surat\Disposisi\Index::class)->name('kepegawaian.surat.disposisi.index');
    Route::get('/kepegawaian/surat/disposisi/create', App\Livewire\Surat\Disposisi\Add::class)->name('kepegawaian.surat.disposisi.add');
    Route::get('/kepegawaian/surat/disposisi/inbox', App\Livewire\Surat\Disposisi\InboxDisposisi::class)->name('kepegawaian.surat.disposisi.inbox');
    Route::get('/kepegawaian/surat/disposisi/{id}', App\Livewire\Surat\Disposisi\Show::class)->name('kepegawaian.surat.disposisi.show');
    Route::get('/kepegawaian/surat/disposisi/{id}/print', function ($id) {
        $disposisi = App\Models\Surat\SuratDisposisi::with(['details', 'direktur'])->findOrFail($id);
        return view('livewire.surat.disposisi.print', compact('disposisi'));
    })->name('kepegawaian.surat.disposisi.print');
    Route::get('/kepegawaian/surat/disposisi/{id}/download', [App\Http\Controllers\Surat\SuratDisposisiPdfController::class, 'download'])
        ->name('kepegawaian.surat.disposisi.download');
});

