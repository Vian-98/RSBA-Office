<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use App\Models\Surat\SuratSp3Detail;
use Illuminate\Database\Eloquent\Model;

class SuratSp3 extends Model
{
    protected $table = 'surat_sp3';
    protected $guarded = [];

    protected $casts = [
        'status'             => StatusApproval::class,
        'docstore_synced_at' => 'datetime',
        'signed_at'          => 'datetime',
        'is_valid'           => 'boolean',
    ];


    public function details()
    {
        return $this->hasMany(SuratSp3Detail::class, 'sp3_id', 'id');
    }

    function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    function getDibuatOlehAttribute()
    {
        return optional($this->createdBy)->karyawan->nama ?? '-';
    }

    function jabatans()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    function getMethodBayarAttribute()
    {
        $mapping = [
            'tunai' => 'Tunai',
            'trf'   => 'Transfer',
            'giro'  => 'Giro',
        ];

        return $mapping[$this->attributes['bayar']] ?? $this->attributes['bayar'];
    }

    function approvals()
    {
        return $this->hasMany(SuratSp3Approval::class, 'surat_sp3_id', 'id');
    }

    public function verifikasiKeuangan()
    {
        return $this->hasOne(SuratSp3Approval::class, 'surat_sp3_id', 'id')->where('tahap', 'verifikasi_keuangan');
    }

    public function ttdAtasan()
    {
        return $this->hasOne(SuratSp3Approval::class, 'surat_sp3_id', 'id')->where('tahap', 'ttd_atasan');
    }

    public function verifikatorKeuangan()
    {
        return $this->belongsTo(Karyawan::class, 'verifikator_keuangan_id', 'id');
    }

    public function penyetuju()
    {
        return $this->belongsTo(Karyawan::class, 'disetujui', 'id');
    }
}

