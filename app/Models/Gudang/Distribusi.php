<?php

namespace App\Models\Gudang;

use App\Models\User;
use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribusi extends Model
{
    protected $table = 'um_distribusi';
    protected $guarded = ['id'];


    public function details()
    {
        return $this->hasMany(DistribusiDetail::class, 'distribusi_id', 'id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'tujuan', 'id');
    }

    // pengirim belng to user, based auth.
    public function pengirimUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim', 'id');
    }

    // penerima belong to karyawan, 
    // karena belum tentu semua karyawan sudah mendaftar user,
    public function penerimaKaryawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penerima', 'id');
    }

    // use accesor, Changed to use get prefix as per Laravel convention
    public function getPengirimNamaAttribute(): string
    {
        return $this->pengirimUser->karyawan->nama ?? 'n/a';
    }

    // use accesor, Changed to use get prefix as per Laravel convention
    public function getPenerimaNamaAttribute(): ?string
    {
        return $this->penerimaKaryawan->nama ?? null;
    }
}
