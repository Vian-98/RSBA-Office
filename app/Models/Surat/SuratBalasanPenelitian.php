<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratBalasanPenelitian extends Model
{
    protected $table = 'surat_balasan_penelitian';
    protected $guarded = [];

    protected $casts = [
        'status'             => StatusApproval::class,
        'tgl'                => 'date',
        'tgl_surat_masuk'    => 'date',
        'docstore_synced_at' => 'datetime',
        'signed_at'          => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->hasMany(SuratBalasanPenelitianMahasiswa::class, 'surat_balasan_penelitian_id', 'id');
    }

    public function biaya()
    {
        return $this->hasMany(SuratBalasanPenelitianBiaya::class, 'surat_balasan_penelitian_id', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    public function direktur()
    {
        return $this->belongsTo(Karyawan::class, 'disetujui_oleh', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function getDibuatOlehAttribute(): string
    {
        return optional($this->createdBy)->karyawan->nama ?? optional($this->createdBy)->name ?? '-';
    }

    public function getTotalBiayaAttribute(): float
    {
        return (float) $this->biaya->sum(function ($item) {
            return ($item->jasa_sarana + $item->jasa_pelayanan) * ($item->jumlah_orang ?: 1);
        });
    }
}
