<?php

namespace App\Models\Assets;

use App\Models\User;
use App\Models\Ruangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMutasi extends Model
{
    protected $table = 'asset_mutasi';
    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }

    public function ruangan_asal(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_asal_id', 'id');
    }

    public function ruangan_tujuan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_tujuan_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
