<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratTarifPenelitian extends Model
{
    protected $table = 'surat_tarif_penelitian';
    protected $guarded = [];

    protected $casts = [
        'jasa_sarana'    => 'double',
        'jasa_pelayanan' => 'double',
        'tgl_berlaku'    => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
