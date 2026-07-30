<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BarangKonversiSatuan extends Model
{
    protected $table = 'um_barang_konversi_satuans';
    protected $guarded = [];

    public function barang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function satuan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan_id', 'id');
    }
}
