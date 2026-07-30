<?php

namespace App\Models\Gudang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpnameStok extends Model
{
    protected $table = 'um_opname_stok';
    protected $guarded = ['id'];

    protected $with = ['created_oleh.karyawan', 'selesai_oleh.karyawan', 'validate_oleh.karyawan'];

    public function details(): HasMany
    {
        return $this->hasMany(OpnameStokDetail::class, 'opname_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'process');
    }

    public function created_oleh()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function selesai_oleh()
    {
        return $this->belongsTo(User::class, 'selesai_by', 'id');
    }

    public function validate_oleh()
    {
        return $this->belongsTo(User::class, 'validate_by', 'id');
    }

    public function getUserPjAttribute(): string
    {
        if ($this->relationLoaded('created_oleh') && $this->created_oleh?->relationLoaded('karyawan')) {
            return $this->created_oleh?->karyawan?->nama ?? '-';
        }
        return optional(optional($this->created_oleh)?->karyawan)?->nama ?? "-";
    }

    public function getUserSelesaiAttribute(): string
    {
        if ($this->relationLoaded('selesai_oleh') && $this->selesai_oleh?->relationLoaded('karyawan')) {
            return $this->selesai_oleh?->karyawan?->nama ?? '-';
        }
        return optional(optional($this->selesai_oleh)?->karyawan)?->nama ?? '-';
    }

    public function getUserValidasiAttribute(): string
    {
        if ($this->relationLoaded('validate_oleh') && $this->validate_oleh?->relationLoaded('karyawan')) {
            return $this->validate_oleh?->karyawan?->nama ?? '-';
        }
        return optional(optional($this->validate_oleh)?->karyawan)?->nama ?? '-';
    }
}
