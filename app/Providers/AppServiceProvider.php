<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Filesystem\WindowsCompatibleFilesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the Windows-compatible filesystem class to solve Access Denied rename issue
        $this->app->singleton('files', function () {
            return new WindowsCompatibleFilesystem;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix Windows permission issue for compiled views directory
        if (PHP_OS_FAMILY === 'Windows') {
            $viewsDir = config('view.compiled', storage_path('framework/views'));
            if (is_dir($viewsDir) && !is_writable($viewsDir)) {
                @exec('icacls "' . $viewsDir . '" /grant Everyone:(OI)(CI)F /T 2>&1');
            }
        }

        // Register Observers for Docstore Synchronisation
        \App\Models\Surat\SuratSp3::observe(\App\Observers\SuratSp3Observer::class);
        \App\Models\Surat\SuratCuti::observe(\App\Observers\SuratCutiObserver::class);
        \App\Models\Surat\SuratSp3Approval::observe(\App\Observers\SuratSp3ApprovalObserver::class);
        \App\Models\Surat\SuratCutiApproval::observe(\App\Observers\SuratCutiApprovalObserver::class);
    }
}
