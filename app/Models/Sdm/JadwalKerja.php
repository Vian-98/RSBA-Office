<?php

namespace App\Models\Sdm;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusJadwalKerja;

class JadwalKerja extends Model
{
    use Blameable;
    
    protected $table = 'sdm_jadwal_kerja';
    protected $guarded = [];
    protected $casts = [
        'status' => StatusJadwalKerja::class
    ];

    public function details()
    {
        return $this->hasMany(JadwalKerjaDetail::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(Karyawan::class, 'dibuat_oleh');
    }

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Ruangan::class, 'ruangan_id');
    }
}
