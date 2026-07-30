<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class BagianKoordinator extends Model
{
    protected $table = 'sdm_bagian_koordinator';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean'];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
