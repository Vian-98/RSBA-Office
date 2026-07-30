<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;

class CutiBersamaTanggal extends Model
{
    protected $table = 'sdm_cuti_bersama_tanggal';
    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function cutiBersama()
    {
        return $this->belongsTo(CutiBersama::class, 'cuti_bersama_id', 'id');
    }
}
