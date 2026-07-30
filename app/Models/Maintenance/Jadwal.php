<?php

namespace App\Models\Maintenance;

use App\Models\User;
use App\Models\Assets\AssetBarang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Jadwal extends Model
{
    protected $table = 'asset_maintc_jadwal';
    protected $guarded = [];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'maintc_request_id');
    }

    public function teknisi(): HasMany
    {
        return $this->hasMany(TeknisiAssignment::class, 'maintc_jadwal_id', 'id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }

    public function work(): HasOne
    {
        return $this->hasOne(Work::class, 'maintc_jadwal_id', 'id');
    }
}
