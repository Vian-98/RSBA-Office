<?php

namespace App\Models\Gudang;

use App\Models\Master\Barang;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\BarangPenyimpanan;
use App\Models\Master\BarangPenyimpananLemari;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stok extends Model
{
    protected $table = 'um_stok';
    protected $guarded = [];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    function penyimpanan(): BelongsTo
    {
        return $this->belongsTo(BarangPenyimpanan::class, 'penyimpanan_id', 'id');
    }

    function lemari(): BelongsTo
    {
        return $this->belongsTo(BarangPenyimpananLemari::class, 'lemari_id', 'id');
    }

    function penerimaanDet(): BelongsTo
    {
        return $this->belongsTo(PenerimaanDetail::class, 'penerimaan_det_id', 'id');
    }

    function scopeBarangAkanHabis($query)
    {
        return $query->where('stok', '<', 10);
    }

    function distribusiDetails(): HasMany
    {
        return $this->hasMany(DistribusiDetail::class, 'stok_id', 'id');
    }

    public function getJumlahMasukAktualAttribute()
    {
        // Cek apakah stok ini adalah hasil pecahan (punya mutasi TRANSFER_MASUK)
        $transferMasuk = \App\Models\Gudang\StokMutasi::where('stok_id', $this->id)
            ->where('jenis_mutasi', 'TRANSFER_MASUK')
            ->sum('jumlah');
            
        if ($transferMasuk > 0) {
            return $transferMasuk;
        }

        // Jika stok original, maka jumlah masuk = (Penerimaan Awal) + (TRANSFER_KELUAR)
        // Note: TRANSFER_KELUAR bernilai negatif, jadi kita tambahkan.
        $penerimaan = $this->penerimaanDet?->jumlah ?? 0;
        
        $transferKeluar = \App\Models\Gudang\StokMutasi::where('stok_id', $this->id)
            ->where('jenis_mutasi', 'TRANSFER_KELUAR')
            ->sum('jumlah');
            
        return $penerimaan + $transferKeluar;
    }
}
