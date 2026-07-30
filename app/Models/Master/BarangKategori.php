<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BarangKategori extends Model
{
    protected $table = 'um_kategori';
    protected $guarded = [];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'kategori_id', 'id');
    }
}
