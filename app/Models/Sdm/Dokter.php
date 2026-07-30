<?php

namespace App\Models\Sdm;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\DokterSpesialisasi;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';
    protected $guarded = [];


    function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    function spesialis()
    {
        return $this->belongsTo(DokterSpesialisasi::class, 'spesialis_id', 'id');
    }
}
