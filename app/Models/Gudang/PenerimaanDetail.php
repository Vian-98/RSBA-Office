<?php

namespace App\Models\Gudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PenerimaanDetail extends Model
{
    protected $table = 'um_penerimaan_beli_det';
    protected $guarded = [];


    function pembelianDet(): BelongsTo
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_det_id', 'id');
    }

    function penerimaan(): BelongsTo
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id', 'id');
    }

    function stoks(): HasOne
    {
        return $this->hasOne(Stok::class, 'penerimaan_det_id', 'id');
    }
}
