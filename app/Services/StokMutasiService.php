<?php

namespace App\Services;

use Exception;
use App\Models\Gudang\Stok;
use App\Models\Gudang\StokMutasi;
use Illuminate\Support\Facades\DB;

class StokMutasiService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function tambahStok(int $barangId, int $stokId, int $jumlah, string $jenisMutasi, object $referensi, ?string $keterangan = null): StokMutasi
    {
        // stok_sebelum = 0 karena tambahStok() digunakan untuk batch stok baru
        $stokSebelum = 0;
        $stokSesudah = $jumlah;

        return StokMutasi::create([
            'stok_id'        => $stokId,
            'barang_id'      => $barangId,
            'jenis_mutasi'   => $jenisMutasi,
            'jumlah'         => $jumlah,
            'multiplier'     => 1,
            'stok_sebelum'   => $stokSebelum,
            'stok_sesudah'   => $stokSesudah,
            'keterangan'     => $keterangan,
            'referensi_type' => get_class($referensi),  // Fix: gunakan nama class, bukan object
            'referensi_id'   => $referensi->id,          // Fix: gunakan ID referensi, bukan stokId
            'created_by'     => auth()->id(),
            'is_posted'      => 1,
            'is_reversed'    => 0,
        ]);
    }

    public function kurangiStok(int $stokId, int $barangId, int $jumlah, string $jenisMutasi, object $referensi, ?string $keterangan = null): StokMutasi
    {
        return DB::transaction(function () use ($stokId, $barangId, $jumlah, $jenisMutasi, $referensi, $keterangan) {

            $stoks = Stok::lockForUpdate()->findOrFail($stokId);

            if ($stoks->stok < $jumlah) {
                throw new Exception("Stok tidak mencukupi. Stok tersedia: {$stoks->barang->nama}");
            }

            $stokSebelum = $stoks->stok;
            $stokSesudah = $stoks->stok - $jumlah;

            return StokMutasi::create([
                'stok_id'        => $stokId,
                'barang_id'      => $barangId,
                'jenis_mutasi'   => $jenisMutasi,
                'jumlah'         => $jumlah,
                'multiplier'     => -1,
                // 'jumlah_bersih' TIDAK diisi — ini stored generated column (jumlah * multiplier)
                'stok_sebelum'   => $stokSebelum,
                'stok_sesudah'   => $stokSesudah,
                'keterangan'     => $keterangan,
                'referensi_type' => get_class($referensi),  // Fix: string class name
                'referensi_id'   => $referensi->id,          // Fix: ID referensi, bukan stokId
                'created_by'     => auth()->id(),
                'is_posted'      => 1,
                'is_reversed'    => 0,
            ]);
        });
    }

    public function historyStok(): void {}


    private function getJenisMutasi() {}
}
