<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangPenyimpanan extends Model
{
    protected $table = 'um_penyimpanan';
    protected $guarded = [];

    public function lemaris(): HasMany
    {
        return $this->hasMany(BarangPenyimpananLemari::class, 'penyimpanan_id', 'id');
    }
}
