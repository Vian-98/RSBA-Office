<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;

class SuratBalasanPklMahasiswa extends Model
{
    protected $table = 'surat_balasan_pkl_mahasiswa';
    protected $guarded = [];

    public function suratBalasanPkl()
    {
        return $this->belongsTo(SuratBalasanPkl::class, 'surat_balasan_pkl_id', 'id');
    }
}
