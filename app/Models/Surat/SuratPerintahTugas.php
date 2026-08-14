<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratPerintahTugas extends Model
{
    protected $table = 'surat_perintah_tugas';
    protected $guarded = [];

    protected $casts = [
        'status'             => StatusApproval::class,
        'tgl'                => 'date',
        'docstore_synced_at' => 'datetime',
        'signed_at'          => 'datetime',
    ];

    public function karyawanTugas()
    {
        return $this->hasMany(SuratPerintahTugasKaryawan::class, 'surat_perintah_tugas_id', 'id');
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
}
