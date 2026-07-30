<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Blameable;

class JadwalAturan extends Model
{
    use Blameable;
    protected $table = 'sdm_jadwal_aturan';
    protected $guarded = [];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }
}
