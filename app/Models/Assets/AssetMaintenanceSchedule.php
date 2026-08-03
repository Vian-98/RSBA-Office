<?php

namespace App\Models\Assets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceSchedule extends Model
{
    protected $table = 'asset_maintenance_schedules';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_berikutnya' => 'date',
        'terakhir_dilakukan' => 'date',
        'is_active' => 'boolean',
    ];

    public function assetBarang(): BelongsTo
    {
        return $this->belongsTo(AssetBarang::class, 'asset_barang_id', 'id');
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', 1)
            ->where('tgl_berikutnya', '<=', now()->format('Y-m-d 23:59:59'));
    }

    public function calculateNextDueDate(Carbon $fromDate = null): Carbon
    {
        $base = $fromDate ? $fromDate->copy() : Carbon::parse($this->tgl_berikutnya ?? $this->tgl_mulai ?? now());

        if ($this->interval_unit === 'year') {
            return $base->addYears((int) $this->interval_value);
        }

        return $base->addMonths((int) $this->interval_value);
    }
}
