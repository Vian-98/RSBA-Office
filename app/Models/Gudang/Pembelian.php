<?php

namespace App\Models\Gudang;

use App\Models\Master\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembelian extends Model
{
    protected $table = 'um_pembelian';
    protected $guarded = [];
    protected $casts = [
        'lampirans' => 'array'
    ];

    protected $with = ['created_oleh.karyawan'];

    protected function jenis(): Attribute
    {
        return Attribute::make(
            get: fn($value) => match ($value) {
                'langsung' => 'Pembelian Langsung',
                'pre_order' => 'Pre Order',
                default => ucfirst(str_replace('_', ' ', $value)),
            }
        );
    }

    function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    function details(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id', 'id');
    }

    function pembelians(): HasMany
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id', 'id');
    }


    // Mutator untuk validasi
    public function setLampiransAttribute($value)
    {
        // Validasi sebelum save jika perlu
        $this->attributes['lampirans'] = json_encode($value);
    }

    public function created_oleh()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // accessor user_created
    public function getUserCreatedAttribute(): string
    {
        if ($this->relationLoaded('created_oleh') && $this->created_oleh?->relationLoaded('karyawan')) {
            return $this->created_oleh?->karyawan->nama ?? '-';
        }

        return optional(optional($this->created_oleh)?->karyawan)?->nama ?? '-';
    }
}
