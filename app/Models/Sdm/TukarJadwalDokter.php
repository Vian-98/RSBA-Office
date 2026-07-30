<?php

namespace App\Models\Sdm;

use App\Enums\StatusTukarJadwal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TukarJadwalDokter extends Model
{
    protected $table = 'sdm_tukar_jadwal_dokter';
    protected $guarded = [];

    protected $casts = [
        'status'               => StatusTukarJadwal::class,
        'konfirmasi_dokter_at' => 'datetime',
        'disetujui_wadir_at'   => 'datetime',
    ];

    public function dokterPengaju(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'dokter_pengaju_id');
    }

    public function dokterPengganti(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'dokter_pengganti_id');
    }

    public function jadwalDetailPengaju(): BelongsTo
    {
        return $this->belongsTo(JadwalKerjaDetail::class, 'jadwal_detail_pengaju_id');
    }

    public function jadwalDetailPengganti(): BelongsTo
    {
        return $this->belongsTo(JadwalKerjaDetail::class, 'jadwal_detail_pengganti_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_wadir_oleh');
    }
}
