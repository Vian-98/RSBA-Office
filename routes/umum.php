<?php

use Illuminate\Support\Facades\Route;

Route::prefix('master')
    ->name('master.')
    ->group(function () {
        Route::get('barang', App\Livewire\Master\Barang\Index::class)->name('barang');
        Route::get('kategori', App\Livewire\Master\Barang\Kategori\Index::class)->name('kategori');
        Route::get('satuan', App\Livewire\Master\Barang\Satuan\Index::class)->name('satuan');
        Route::get('supplier', App\Livewire\Master\Supplier\Index::class)->name('supplier');
        Route::get('penyimpanan', App\Livewire\Master\Penyimpanan\Index::class)->name('penyimpanan');
    });


Route::prefix('pembelian')
    ->name('pembelian.')
    ->group(function () {
        Route::get('/', App\Livewire\Pembelian\Index::class)->name('index');
    });

Route::prefix('distribusi')
    ->name('distribusi.')
    ->group(function () {
        Route::get('/', App\Livewire\Distribusi\Index::class)->name('index');
    });


Route::prefix('gudang')
    ->name('gudang.')
    ->group(function () {
        Route::get('/', App\Livewire\Gudang\Index::class)->name('index');
    });

Route::prefix('asset')
    ->name('asset.')
    ->group(function () {
        Route::get('/', App\Livewire\Asset\Index::class)->name('index');
        // Route::get('/maintenance/{id?}', App\Livewire\Asset\Maintenance\Index::class)->name('maintenance');
    });

Route::prefix('maintenance')
    ->name('maintenance.')
    ->group(function () {
        Route::get('/', App\Livewire\Maintenance\Index::class)->name('index');
        Route::get('/ticket/{id}', App\Livewire\Maintenance\Ticket\Detail::class)->name('ticket.detail');
        // Route::get('/create', App\Livewire\Maintenance\Create::class)->name('create');
    });


Route::prefix('opname')
    ->name('opname.')
    ->group(function () {
        Route::get('/', App\Livewire\StokOpname\Index::class)->name('index');
    });

Route::prefix('laporan')
    ->name('laporan.')
    ->group(function () {
        Route::get('/', App\Livewire\Laporan\Umum\Index::class)->name('index');
    });


Route::prefix('pengajuan')
    ->name('pengajuan.')
    ->group(function () {
        Route::get('/', App\Livewire\Pembelian\Permintaan\Index::class)->name('index');
    });


// Route::livewire('/opname-block', App\Livewire\StokOpname\BlockBlocked::class)->name('opname.blocked');
