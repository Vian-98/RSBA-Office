<?php

namespace App\Models;

use App\Models\JmPasien;
use Illuminate\Database\Eloquent\Model;

class JmDokter extends Model
{
    protected $table = 'jm_dokter';

    protected $guarded = [];

    function pasien()
    {
        return $this->belongsTo(JmPasien::class, 'jm_pasien_id', 'id');
    }
}
