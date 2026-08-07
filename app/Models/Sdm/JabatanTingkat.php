<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JabatanTingkat extends Model
{
    protected $table = 'sdm_jabatan_tingkat';
    protected $guarded = [];

    protected $casts = [
        'urutan' => 'integer',
        'is_penyusun_jadwal' => 'boolean',
    ];

    public function jabatans(): HasMany
    {
        return $this->hasMany(Jabatan::class, 'tingkat_id');
    }
}
