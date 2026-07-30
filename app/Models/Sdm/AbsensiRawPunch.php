<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class AbsensiRawPunch extends Model
{
    protected $table = 'sdm_absensi_raw_punch';
    protected $guarded = [];

    protected $casts = [
        'tanggal'        => 'date',
        'assigned_date'  => 'date',
        'punch_datetime' => 'datetime',
        'is_discarded'   => 'boolean',
    ];

    public function importLog()
    {
        return $this->belongsTo(AbsensiImportLog::class, 'import_log_id');
    }

    public function duplicateReference()
    {
        return $this->belongsTo(self::class, 'duplicate_reference_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'employee_id', 'pin_absen');
    }
}
