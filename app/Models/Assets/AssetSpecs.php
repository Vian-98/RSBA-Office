<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;

class AssetSpecs extends Model
{
    protected $table = 'asset_specs';
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(AssetBarang::class, 'asset_id', 'id');
    }
}
