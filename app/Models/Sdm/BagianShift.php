<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class BagianShift extends Model
{
    protected $table = 'sdm_bagian_shift';
    protected $guarded = [];

    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'bagian_id');
    }

    public function shift()
    {
        return $this->belongsTo(JadwalShift::class, 'shift_id');
    }
}
