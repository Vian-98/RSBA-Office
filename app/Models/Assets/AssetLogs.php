<?php

namespace App\Models\Assets;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLogs extends Model
{
    protected $table = 'asset_logs';
    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
