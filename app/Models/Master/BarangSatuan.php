<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BarangSatuan extends Model
{
    protected $table = 'um_satuan';
    protected $guarded = [];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'satuan_id', 'id');
    }
}
