<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;

class SuratBalasanPenelitianMahasiswa extends Model
{
    protected $table = 'surat_balasan_penelitian_mahasiswa';
    protected $guarded = [];

    public function suratBalasanPenelitian()
    {
        return $this->belongsTo(SuratBalasanPenelitian::class, 'surat_balasan_penelitian_id', 'id');
    }
}
