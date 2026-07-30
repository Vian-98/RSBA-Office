<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureLogs extends Model
{
    protected $table = 'signature_logs';
    protected $guarded = ['id'];

    public function user_sign()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getUserSignerAttribute(): ?string
    {
        if ($this->relationLoaded('user_sign') && $this->user_sign?->relationLoaded('karyawan')) {
            return $this->user_sign?->karyawan?->nama ?? '-';
        }
        return optional(optional($this->user_sign)?->karyawan)?->nama;
    }

    public function getCerificateInfo() {}
}
