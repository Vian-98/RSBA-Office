<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class JadwalKerjaLog extends Model
{
    protected $table = 'sdm_jadwal_kerja_log';
    protected $guarded = [];

    public function jadwalKerja()
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    public function detail()
    {
        return $this->belongsTo(JadwalKerjaDetail::class, 'detail_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function shiftLama()
    {
        return $this->belongsTo(JadwalShift::class, 'shift_lama_id');
    }

    public function shiftBaru()
    {
        return $this->belongsTo(JadwalShift::class, 'shift_baru_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(Karyawan::class, 'diubah_oleh');
    }
}
