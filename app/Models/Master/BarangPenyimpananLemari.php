<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class BarangPenyimpananLemari extends Model
{
    protected $table = 'um_penyimpanan_lemaris';
    protected $guarded = [];

    public function penyimpanan()
    {
        return $this->belongsTo(BarangPenyimpanan::class, 'penyimpanan_id', 'id');
    }
}
