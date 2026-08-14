<?php

namespace App\Models\Keuangan;

use App\Enums\StatusApproval;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuitansiApproval extends Model
{
    protected $table = 'kuitansi_approval';
    protected $guarded = [];

    protected $casts = [
        'status' => StatusApproval::class,
    ];

    public function kuitansi(): BelongsTo
    {
        return $this->belongsTo(Kuitansi::class, 'kuitansi_id', 'id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'disetujui_oleh', 'id');
    }

    public function getApprovedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->toIso8601String() : null;
    }
}
