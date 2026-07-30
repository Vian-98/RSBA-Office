<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    protected $table = 'bagian';
    protected $guarded = [];

    function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'bagian_id', 'id');
    }

    public function koordinators()
    {
        return $this->belongsToMany(Karyawan::class, 'sdm_bagian_koordinator', 'bagian_id', 'karyawan_id')
            ->wherePivot('aktif', true);
    }

    public function shiftValid()
    {
        return $this->belongsToMany(JadwalShift::class, 'sdm_bagian_shift', 'bagian_id', 'shift_id');
    }
}
