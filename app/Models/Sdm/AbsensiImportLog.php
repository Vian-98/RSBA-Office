<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class AbsensiImportLog extends Model
{
    protected $table = 'sdm_absensi_import_log';
    protected $guarded = [];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'dikunci_at' => 'datetime',
    ];

    public function staging()
    {
        return $this->hasMany(AbsensiStaging::class, 'import_batch_id');
    }
}
