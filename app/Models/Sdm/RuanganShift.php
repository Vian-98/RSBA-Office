<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ruangan;

class RuanganShift extends Model
{
    protected $table = 'sdm_ruangan_shift';
    protected $guarded = [];
    protected $casts = [
        'jam_masuk_override'  => 'string',
        'jam_keluar_override' => 'string',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function shift()
    {
        return $this->belongsTo(JadwalShift::class, 'shift_id');
    }

    /** Jam masuk efektif: pakai override jika ada, fallback ke master shift */
    public function getJamMasukEfektifAttribute(): string
    {
        if ($this->jam_masuk_override) {
            // override mungkin berformat H:i:s, kita ambil H:i
            return substr($this->jam_masuk_override, 0, 5);
        }
        return substr($this->shift->jam_masuk, 0, 5);
    }

    /** Jam keluar efektif */
    public function getJamKeluarEfektifAttribute(): string
    {
        if ($this->jam_keluar_override) {
            return substr($this->jam_keluar_override, 0, 5);
        }
        return substr($this->shift->jam_keluar, 0, 5);
    }
}
