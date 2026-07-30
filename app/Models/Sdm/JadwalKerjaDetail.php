<?php

namespace App\Models\Sdm;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatusKehadiran;

class JadwalKerjaDetail extends Model
{
    use Blameable;
    
    protected $table = 'sdm_jadwal_kerja_detail';
    protected $guarded = [];
    protected $casts = [
        'status_kehadiran' => StatusKehadiran::class,
        'tanggal' => 'date'
    ];

    public function jadwalKerja()
    {
        return $this->belongsTo(JadwalKerja::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function shift()
    {
        return $this->belongsTo(JadwalShift::class, 'shift_id');
    }
}
