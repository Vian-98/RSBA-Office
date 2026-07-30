<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JmRincian extends Model
{
    protected $table = 'jm_rincian';
    protected $guarded = [];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(JmPasien::class, 'jm_pasien_id', 'id');
    }
}
