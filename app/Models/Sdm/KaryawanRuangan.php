<?php

namespace App\Models\Sdm;

use App\Models\Ruangan;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaryawanRuangan extends Pivot
{
    protected $table = 'sdm_kary_ruangan';
    protected $guarded = [];

    protected $casts = [
        'is_utama' => 'boolean',
        'tgl_mulai' => 'date',
        'tgl_berakhir' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }
    public function document(): BelongsTo
    {
        return $this->belongsTo(KaryawanDocument::class, 'document_id');
    }
}
