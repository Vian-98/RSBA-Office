<?php

namespace App\Models\Assets;

use App\Models\Ruangan;
use App\Models\Master\Barang;
use Illuminate\Database\Eloquent\Model;
use App\Models\Maintenance\Request as MaintenanceRequest;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetBarang extends Model
{
    protected $table = 'asset_barang';
    protected $guarded = [];


    function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id', 'id');
    }

    function parent(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'main_asset_id', 'id');
    }

    function child(): HasMany
    {
        return $this->hasMany(AssetBarang::class, 'main_asset_id', 'id');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(AssetSpecs::class, 'asset_id', 'id');
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(AssetMaintenanceSchedule::class, 'asset_barang_id', 'id');
    }

    function logs(): HasMany
    {
        return $this->hasMany(AssetLogs::class, 'asset_id', 'id');
    }

    function mutasis()
    {
        return $this->hasMany(AssetMutasi::class, 'asset_id', 'id');
    }


    /**
     * ===========================================================
     * Relasi hierarki
     * ===========================================================
     **/
    public function mainAsset()
    {
        return $this->belongsTo(AssetBarang::class, 'main_asset_id');
    }

    public function components()
    {
        return $this->hasMany(AssetBarang::class, 'main_asset_id');
    }

    // Scope untuk sorting hierarki
    public function scopeWithHierarchySort($query)
    {
        return $query->orderByRaw('
            CASE 
                WHEN main_asset_id IS NULL THEN id 
                ELSE main_asset_id 
            END ASC,
            CASE 
                WHEN main_asset_id IS NULL THEN 0 
                ELSE 1 
            END ASC,
            kode ASC
        ');
    }


    // Maintenance Requests
    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'asset_id', 'id');
    }
}
