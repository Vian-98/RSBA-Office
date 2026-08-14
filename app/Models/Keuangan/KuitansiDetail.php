<?php

namespace App\Models\Keuangan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuitansiDetail extends Model
{
    protected $table = 'kuitansi_details';
    protected $guarded = [];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function kuitansi(): BelongsTo
    {
        return $this->belongsTo(Kuitansi::class, 'kuitansi_id', 'id');
    }
}
