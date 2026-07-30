<?php

namespace App\Models\Sdm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AbsensiKoreksiLog extends Model
{
    protected $table = 'sdm_absensi_koreksi_log';
    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'absen_masuk_lama' => 'datetime',
        'absen_masuk_baru' => 'datetime',
        'absen_keluar_lama' => 'datetime',
        'absen_keluar_baru' => 'datetime',
    ];

    public function detail()
    {
        return $this->belongsTo(JadwalKerjaDetail::class, 'detail_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
