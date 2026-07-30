<?php

namespace App\Models\Gudang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penerimaan extends Model
{
    protected $table = 'um_penerimaan_beli';
    protected $guarded = [];


    function details(): HasMany
    {
        return $this->hasMany(PenerimaanDetail::class, 'penerimaan_id', 'id');
    }

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima', 'id');
    }
}
