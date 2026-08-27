<?php

namespace App\Models\Surat;

use App\Models\Sdm\Karyawan;
use Illuminate\Database\Eloquent\Model;

class SuratPerintahTugasKaryawan extends Model
{
    protected $table = 'surat_perintah_tugas_karyawan';
    protected $guarded = [];

    public function suratPerintahTugas()
    {
        return $this->belongsTo(SuratPerintahTugas::class, 'surat_perintah_tugas_id', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function getNamaAttribute(): string
    {
        return $this->karyawan?->full_nama ?? $this->karyawan?->nama ?? '-';
    }

    public function getNipAttribute(): string
    {
        return $this->karyawan?->nip ?? '-';
    }

    public function getJabatanNamaAttribute(): string
    {
        return optional($this->karyawan?->jabatan?->first())->nama ?? '-';
    }
}
