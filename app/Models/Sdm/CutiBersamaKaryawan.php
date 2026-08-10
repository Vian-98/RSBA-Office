<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class CutiBersamaKaryawan extends Model
{
    protected $table = 'sdm_cuti_bersama_karyawan';
    protected $guarded = [];

    protected $casts = [
        'is_ikut' => 'boolean',
    ];

    public function cutiBersama()
    {
        return $this->belongsTo(CutiBersama::class, 'cuti_bersama_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
