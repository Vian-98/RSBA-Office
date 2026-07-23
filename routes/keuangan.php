<?php

use Illuminate\Support\Facades\Route;


Route::prefix('hutang')
    ->name('hutang.')
    ->group(function () {
        Route::get('/', App\Livewire\Hutang\Index::class)->name('index');
    });

Route::prefix('piutang')
    ->name('piutang.')
    ->group(function () {
        Route::get('/', App\Livewire\Piutang\Index::class)->name('index');
    });

Route::prefix('laporan')
    ->name('laporan.')
    ->group(function () {
        Route::get('/', App\Livewire\Laporan\Keuangan\Index::class)->name('index');
    });

Route::prefix('akuntansi')
    ->name('akuntansi.')
    ->group(
        function () {
            Route::get('coa', App\Livewire\Akuntansi\Coa\Index::class)->name('coa');

            // jurnal
            Route::prefix('jurnal')
                ->name('jurnal.')
                ->group(function () {
                    Route::get('umum', App\Livewire\Akuntansi\Jurnal\Index::class)->name('umum');
                });
        }
    );

Route::prefix('master')
    ->name('master.')
    ->group(function () {
        Route::get('rekanan', App\Livewire\Master\Supplier\Index::class)->name('rekanan');
    });
