<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\SupplierController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('bpjs')->name('api.bpjs.')->group(function () {
    Route::get('/wards', [\App\Http\Controllers\Api\BpjsDataController::class, 'wards'])->name('wards');
    Route::get('/rooms', [\App\Http\Controllers\Api\BpjsDataController::class, 'rooms'])->name('rooms');
});


Route::prefix('karyawan')
    ->name('api.karyawan.')
    ->group(function () {
        Route::get('/register', [KaryawanController::class, 'register'])->name('register');
        Route::get('/ref', [KaryawanController::class, 'list'])->name('ref');
        Route::get('/listnjabatan/{atasan?}', [KaryawanController::class, 'listWithJabatan'])->name('listnjabatan');
        Route::get('/atasan-approver/{jabatanId?}', [KaryawanController::class, 'atasanApprover'])->name('atasan.approver');
        Route::get('/kuitansi-approver', [KaryawanController::class, 'kuitansiApprover'])->name('kuitansi.approver');
        Route::get('/verifikator-keuangan', [KaryawanController::class, 'verifikatorKeuangan'])->name('verifikator.keuangan');
        Route::get('reg/dokter', [KaryawanController::class, 'registerDokter'])->name('reg.dokter');
    });

Route::prefix('metode-bayar')->name('api.metode-bayar.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Keuangan\MetodeBayarController::class, 'list'])->name('list');
    Route::post('/', [\App\Http\Controllers\Keuangan\MetodeBayarController::class, 'store'])->name('store');
});


# Wilayah
Route::get('prov', [WilayahController::class, 'prov'])->name('api.prov');
Route::get('kab/{id?}', [WilayahController::class, 'kab'])->name('api.kab');
Route::get('kec/{id?}', [WilayahController::class, 'kec'])->name('api.kec');
Route::get('desa/{id?}', [WilayahController::class, 'desa'])->name('api.desa');


// Master Data
Route::get('ruangan', [RuanganController::class, 'list'])->name('api.ruangan');
Route::get('supplier', [SupplierController::class, 'list'])->name('api.supplier');
// Route::get('ruangan', [RuanganController::class, 'list'])->name('api.ruangan');

Route::prefix('barang')
    ->name('api.barang.')
    ->group(function () {
        Route::get('ref', [BarangController::class, 'list'])->name('ref');
        Route::get('by_kategori/{kategori?}', [BarangController::class, 'listByKategori'])->name('by_kategori');

        Route::get('stok', [BarangController::class, 'stok'])->name('stok');
    });


// Asset
Route::prefix('asset')
    ->name('api.asset.')
    ->group(function () {
        Route::get('main_item/{in?}', [AssetController::class, 'mainItem'])->name('main_item');
        Route::get('ref', [AssetController::class, 'list'])->name('ref');
    });


Route::prefix('users')
    ->name('api.users.')
    ->group(function () {
        Route::get('ref', [UsersController::class, 'list'])->name('ref');

        Route::get('avatar/{userId}', [App\Http\Controllers\ProfileImageCacheController::class, 'show'])
            ->name('avatar');
    });


Route::prefix('akreditasi')
    ->name('api.akreditasi.')
    ->group(function () {
        Route::get('chapters/{kegiatan?}', [App\Http\Controllers\AkreditasiController::class, 'chapters'])->name('chapters');

        Route::get('babs/{chapter?}/{type?}', [App\Http\Controllers\AkreditasiController::class, 'babs'])->name('babs');

        Route::get('elements/{sub?}', [App\Http\Controllers\AkreditasiController::class, 'elements'])->name('elements');

        Route::get('documents/{element?}', [App\Http\Controllers\AkreditasiController::class, 'documents'])->name('documents');
    });
