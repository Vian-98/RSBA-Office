<?php

namespace App\Models\Maintenance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TeknisiAssignment extends Model
{
    protected $table = 'asset_maintc_teknisi_assigment';
    protected $guarded = [];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'maintc_jadwal_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'teknisi_id', 'id');
    }
}
