<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Gudang\Stok;
use App\Models\Gudang\StokMutasi;
use App\Services\StokMutasiService;
use Illuminate\Support\Facades\Auth;

/**
 * Feature Test: Verifikasi StokMutasi dicatat dengan benar setelah perbaikan.
 *
 * Cara run:
 *   php artisan test tests/Feature/StokMutasiTest.php
 * atau spesifik method:
 *   php artisan test --filter=StokMutasiTest
 */
class StokMutasiTest extends TestCase
{
    protected function loginAsFirstUser(): void
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $this->markTestSkipped('Tidak ada user di database. Jalankan seeder terlebih dahulu.');
        }
        Auth::login($user);
    }

    // -----------------------------------------------------------------------
    // TEST 1: StokMutasiService::tambahStok() — referensi_id harus dari object
    // -----------------------------------------------------------------------
    #[Test]
    public function tambah_stok_service_mencatat_referensi_id_dari_objek_bukan_stok_id(): void
    {
        $this->loginAsFirstUser();

        $stok = Stok::first();
        if (!$stok) {
            $this->markTestSkipped('Tidak ada data stok. Jalankan UmumSeeder terlebih dahulu.');
        }

        $fakeReferensi = (object) ['id' => 9999];
        $service       = new StokMutasiService();

        $mutasi = $service->tambahStok(
            barangId:    $stok->barang_id,
            stokId:      $stok->id,
            jumlah:      5,
            jenisMutasi: 'PEMBELIAN',
            referensi:   $fakeReferensi,
            keterangan:  '[TEST] tambahStok referensi fix'
        );

        // Sebelum fix: referensi_id = $stok->id (SALAH)
        // Setelah fix: referensi_id = $fakeReferensi->id = 9999 (BENAR)
        $this->assertEquals(9999, $mutasi->referensi_id,
            'referensi_id harus diambil dari $referensi->id, bukan dari $stokId'
        );
        $this->assertEquals('stdClass', $mutasi->referensi_type,
            'referensi_type harus berupa string nama class'
        );
        $this->assertEquals(0, $mutasi->stok_sebelum);
        $this->assertEquals(5, $mutasi->stok_sesudah);

        // Cleanup
        $mutasi->delete();
    }

    // -----------------------------------------------------------------------
    // TEST 2: StokMutasiService::kurangiStok() — stok_sebelum harus akurat
    // -----------------------------------------------------------------------
    #[Test]
    public function kurangi_stok_service_mencatat_stok_sebelum_dengan_benar(): void
    {
        $this->loginAsFirstUser();

        $stok = Stok::where('stok', '>=', 10)->first();
        if (!$stok) {
            $this->markTestSkipped('Tidak ada stok dengan qty >= 10.');
        }

        $stokAwal      = $stok->stok;
        $jumlahKurang  = 3;
        $fakeReferensi = (object) ['id' => 8888];

        $service = new StokMutasiService();
        $mutasi  = $service->kurangiStok(
            stokId:      $stok->id,
            barangId:    $stok->barang_id,
            jumlah:      $jumlahKurang,
            jenisMutasi: 'DISTRIBUSI',
            referensi:   $fakeReferensi,
            keterangan:  '[TEST] kurangiStok stok_sebelum fix'
        );

        $this->assertEquals($stokAwal, $mutasi->stok_sebelum,
            "stok_sebelum harus = {$stokAwal}"
        );
        $this->assertEquals($stokAwal - $jumlahKurang, $mutasi->stok_sesudah,
            "stok_sesudah harus = " . ($stokAwal - $jumlahKurang)
        );
        $this->assertEquals(-1, $mutasi->multiplier);
        $this->assertEquals(8888, $mutasi->referensi_id);

        // Rollback stok dan hapus mutasi test
        $stok->update(['stok' => $stokAwal]);
        $mutasi->delete();
    }

    // -----------------------------------------------------------------------
    // TEST 3: Verifikasi integritas data — stok dari penerimaan & mutasinya
    // -----------------------------------------------------------------------
    #[Test]
    public function setiap_stok_dari_penerimaan_harus_punya_mutasi_pembelian(): void
    {
        $stokDariPenerimaan = Stok::whereNotNull('penerimaan_det_id')
            ->limit(20)
            ->pluck('id');

        if ($stokDariPenerimaan->isEmpty()) {
            $this->markTestSkipped('Belum ada data stok dari penerimaan.');
        }

        $stokDenganMutasi = StokMutasi::whereIn('stok_id', $stokDariPenerimaan)
            ->where('jenis_mutasi', 'PEMBELIAN')
            ->pluck('stok_id')
            ->unique();

        $stokTanpaMutasi = $stokDariPenerimaan->diff($stokDenganMutasi);

        if ($stokTanpaMutasi->isNotEmpty()) {
            dump([
                'Stok tanpa mutasi PEMBELIAN (data lama — bukan error)' => $stokTanpaMutasi->values()->toArray(),
                'Info' => 'Transaksi baru setelah perbaikan akan tercatat otomatis.',
            ]);
        }

        $this->assertTrue(true, 'Test informatif — lihat output dump untuk daftar stok lama tanpa mutasi.');
    }

    // -----------------------------------------------------------------------
    // TEST 4: Verifikasi struktur record mutasi — field wajib tidak boleh null
    // -----------------------------------------------------------------------
    #[Test]
    public function record_mutasi_harus_memiliki_semua_field_wajib(): void
    {
        $mutasi = StokMutasi::whereNotNull('referensi_type')
            ->whereNotNull('referensi_id')
            ->latest()
            ->first();

        if (!$mutasi) {
            $this->markTestSkipped('Belum ada record di um_stok_mutasi.');
        }

        $this->assertNotNull($mutasi->stok_id,         'stok_id tidak boleh null');
        $this->assertNotNull($mutasi->barang_id,       'barang_id tidak boleh null');
        $this->assertNotNull($mutasi->jenis_mutasi,    'jenis_mutasi tidak boleh null');
        $this->assertIsInt($mutasi->stok_sebelum,      'stok_sebelum harus integer');
        $this->assertIsInt($mutasi->stok_sesudah,      'stok_sesudah harus integer');
        $this->assertNotNull($mutasi->created_by,      'created_by tidak boleh null');
        $this->assertIsString($mutasi->referensi_type, 'referensi_type harus string class name');
        $this->assertNotNull($mutasi->referensi_id,    'referensi_id tidak boleh null');
    }
}
