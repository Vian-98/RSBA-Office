<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class KaryawanDocument extends Model
{
    protected $table = "sdm_kary_document";
    protected $guarded = [];

    public function karyawan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
