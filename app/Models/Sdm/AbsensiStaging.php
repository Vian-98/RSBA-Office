<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class AbsensiStaging extends Model
{
    protected $table = 'sdm_absensi_staging';
    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function log()
    {
        return $this->belongsTo(AbsensiImportLog::class, 'import_batch_id');
    }
}
