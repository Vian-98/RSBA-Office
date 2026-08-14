<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;

class SuratBalasanPenelitianBiaya extends Model
{
    protected $table = 'surat_balasan_penelitian_biaya';
    protected $guarded = [];

    protected $casts = [
        'jasa_sarana'    => 'double',
        'jasa_pelayanan' => 'double',
        'jumlah_orang'   => 'integer',
    ];

    public function suratBalasanPenelitian()
    {
        return $this->belongsTo(SuratBalasanPenelitian::class, 'surat_balasan_penelitian_id', 'id');
    }

    public function getTotalAttribute(): float
    {
        return (float) (($this->jasa_sarana + $this->jasa_pelayanan) * ($this->jumlah_orang ?: 1));
    }
}
