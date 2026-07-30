<?php

namespace App\Models\Gudang;

use Illuminate\Database\Eloquent\Model;

class OpnameInvestigasi extends Model
{
    protected $table = 'um_opname_investigasi';
    protected $guarded = ['id'];

    public function opname_detail()
    {
        return $this->belongsTo(OpnameStokDetail::class, 'opname_detail_id', 'id');
    }
}
