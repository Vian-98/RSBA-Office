<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Karyawan;
use Illuminate\Database\Eloquent\Model;

class SuratCutiApproval extends Model
{
    protected $table = 'surat_cuti_approval';
    protected $guarded = [];

    protected $casts = [
        'status' => StatusApproval::class
    ];

    public function cuti()
    {
        return $this->belongsTo(SuratCuti::class, 'surat_cuti_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'disetujui_oleh', 'id');
    }
}
