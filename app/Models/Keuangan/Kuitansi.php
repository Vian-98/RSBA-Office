<?php

namespace App\Models\Keuangan;

use App\Enums\StatusKuitansi;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuitansi extends Model
{
    protected $table = 'kuitansi';
    protected $guarded = [];

    protected $casts = [
        'status'             => StatusKuitansi::class,
        'tanggal'            => 'date',
        'dibatalkan_at'      => 'datetime',
        'signed_at'          => 'datetime',
        'docstore_synced_at' => 'datetime',
        'is_valid'           => 'boolean',
        'jumlah'             => 'decimal:2',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(KuitansiDetail::class, 'kuitansi_id', 'id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(KuitansiApproval::class, 'kuitansi_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function dibatalkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_by', 'id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penerima_id', 'id');
    }

    /**
     * Terbilang mentah dari helper global (sudah termasuk kata "Rupiah").
     */
    public function getTerbilangAttribute(): string
    {
        return terbilang((float) $this->jumlah);
    }
}
