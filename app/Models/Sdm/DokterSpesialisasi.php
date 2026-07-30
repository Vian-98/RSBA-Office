<?php

namespace App\Models\Sdm;

use App\Models\Sdm\Dokter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DokterSpesialisasi extends Model
{
    protected $table = 'dokter_spesialisasi';
    protected $guarded = [];

    function dokter(): HasMany
    {
        return $this->hasMany(Dokter::class, 'spesialis_id', 'id');
    }
}
