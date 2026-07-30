<?php

namespace App\Models\Gudang;

use App\Models\Master\Barang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianDetail extends Model
{
    use SoftDeletes;

    protected $table = 'um_pembelian_det';
    protected $guarded = [];

    function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id', 'id');
    }

    function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    function terimas(): HasMany
    {
        return $this->hasMany(PenerimaanDetail::class, 'pembelian_det_id', 'id');
    }

    function satuanBeli(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\BarangSatuan::class, 'satuan_beli_id', 'id');
    }
}
