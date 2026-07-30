<?php

namespace App\Models\Gudang;

use App\Models\Master\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnameStokDetail extends Model
{
    protected $table = 'um_opname_stok_details';
    protected $guarded = ['id'];
    protected $with = ['user_opname.karyawan'];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(OpnameStok::class, 'pembelian_id', 'id');
    }

    public function stoks(): BelongsTo
    {
        return $this->belongsTo(Stok::class, 'stok_id', 'id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function user_opname()
    {
        return $this->belongsTo(User::class, 'opname_by', 'id');
    }

    public function getOpnameOlehAttribute(): ?string
    {
        if ($this->relationLoaded('user_opname') && $this->user_opname?->relationLoaded('karyawan')) {
            return $this->user_opname?->karyawan?->nama ?? "-";
        }

        return optional(optional($this->user_opname)?->karyawan)?->nama;
    }

    public function investigasi()
    {
        return $this->hasOne(OpnameInvestigasi::class, 'opname_detail_id', 'id');
    }
}
