<?php

namespace App\Models\Gudang;

use App\Models\Master\Barang;
use Illuminate\Database\Eloquent\Model;

class PembelianRequestDetails extends Model
{
    protected $table = 'um_pembelian_requests_det';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'specs' => 'array'
    ];


    public function request()
    {
        return $this->belongsTo(PembelianRequest::class, 'pembelian_req_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}
