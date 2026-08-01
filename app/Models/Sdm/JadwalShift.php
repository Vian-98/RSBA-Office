<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class JadwalShift extends Model
{
    use Blameable;
    protected $table = 'sdm_jadwal_shift';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean', 'lintas_hari' => 'boolean'];
}
