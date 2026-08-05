<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaryawanJabatan extends Model
{
    protected $table = "sdm_kary_jabatan";
    protected $guarded = [];


    // belong to Karyawan
    function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // belong to Jabatan
    function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }
}
