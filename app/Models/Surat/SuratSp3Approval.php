<?php

namespace App\Models\Surat;

use Carbon\Carbon;
use App\Enums\StatusApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratSp3Approval extends Model
{
    protected $table = 'surat_sp3_approval';
    protected $guarded = [];

    protected $casts = [
        'status' => StatusApproval::class
    ];

    function surat(): BelongsTo
    {
        return $this->belongsTo(SuratSp3::class, 'surat_sp3_id', 'id');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui', 'id');
    }

    public function getApprovedAtAttribute($value)
    {
        return Carbon::parse($value)->toIso8601String();
    }
}
