<?php

namespace App\Models\Gudang;

use Illuminate\Database\Eloquent\Model;

class DistribusiDetail extends Model
{
    protected $table = 'um_distribusi_det';
    protected $guarded = [];

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class, 'distribusi_id', 'id');
    }

    function stoks()
    {
        return $this->belongsTo(Stok::class, 'stok_id', 'id');
    }
}
