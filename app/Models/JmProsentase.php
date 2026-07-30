<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JmProsentase extends Model
{
    protected $table = 'jm_prosentase';
    protected $guarded = [];

    function jasa(): HasMany
    {
        return $this->hasMany(JmJasa::class);
    }

    function pasien(): BelongsTo
    {
        return $this->belongsTo(JmPasien::class, 'jm_pasien_id', 'id');
    }
}
