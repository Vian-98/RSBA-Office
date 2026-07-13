<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ruangan;

class RuanganKoordinator extends Model
{
    protected $table = 'sdm_ruangan_koordinator';
    protected $guarded = [];
    protected $casts = ['aktif' => 'boolean'];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
