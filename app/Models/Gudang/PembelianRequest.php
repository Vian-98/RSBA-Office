<?php

namespace App\Models\Gudang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PembelianRequest extends Model
{
    protected $table = "um_pembelian_requests";
    protected $with = ['user_req.karyawan', 'user_verif.karyawan'];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'lampirans' => 'array',
    ];

    public function details()
    {

        return $this->hasMany(PembelianRequestDetails::class, 'pembelian_req_id', 'id');
    }

    public function user_req()
    {
        return $this->belongsTo(User::class, 'user_req_id', 'id');
    }

    public function user_verif()
    {
        return $this->belongsTo(User::class, 'user_verify_id', 'id');
    }


    public function getUserRequestAttribute(): ?string
    {
        if ($this->relationLoaded('user_req') && $this->user_req?->relationLoaded('karyawan')) {
            return $this->user_req?->karyawan?->nama ?? '-';
        }

        return optional(optional($this->user_req)?->karyawan)?->nama;
    }


    public function getUserVerifyAttribute(): ?string
    {
        if ($this->relationLoaded('user_verif') && $this->user_verif?->relationLoaded('karyawan')) {
            return $this->user_verif?->karyawan?->nama ?? "-";
        }

        return optional(optional($this->user_verif)?->karyawan)?->nama;
    }
}
