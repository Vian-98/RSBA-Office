<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratBalasanPkl extends Model
{
    protected $table = 'surat_balasan_pkl';
    protected $guarded = [];

    protected $casts = [
        'status'                 => StatusApproval::class,
        'tgl'                    => 'date',
        'tgl_surat_masuk'        => 'date',
        'tgl_mulai'              => 'date',
        'tgl_selesai'            => 'date',
        'snap_biaya_praktik'     => 'double',
        'snap_biaya_orientasi'   => 'double',
        'docstore_synced_at'     => 'datetime',
        'signed_at'              => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->hasMany(SuratBalasanPklMahasiswa::class, 'surat_balasan_pkl_id', 'id');
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

    /**
     * Hitung total biaya izin praktik (per orang x jumlah bulan x jumlah siswa)
     */
    public function getTotalBiayaPraktikAttribute(): float
    {
        return (float) ($this->snap_biaya_praktik * $this->jumlah_mahasiswa * $this->lama_praktik_bulan);
    }

    /**
     * Hitung total biaya orientasi (per orang x jumlah siswa)
     */
    public function getTotalBiayaOrientasiAttribute(): float
    {
        return (float) ($this->snap_biaya_orientasi * $this->jumlah_mahasiswa);
    }

    /**
     * Hitung grand total keseluruhan biaya
     */
    public function getGrandTotalBiayaAttribute(): float
    {
        return $this->total_biaya_praktik + $this->total_biaya_orientasi;
    }

    /**
     * Format nama universitas untuk tampilan rapi tanpa duplikasi awalan institusi
     */
    public function getDisplayUniversitasAttribute(): string
    {
        $univ = trim($this->tujuan_universitas ?? '');
        if (empty($univ)) {
            return '....................';
        }
        if (preg_match('/^(universitas|institut|politeknik|poltekkes|stikes|akademi|sekolah tinggi)/i', $univ)) {
            return $univ;
        }
        return 'Universitas ' . $univ;
    }

    /**
     * Format nama universitas tanpa prefix 'Universitas' jika dibutuhkan
     */
    public function getFormattedUniversitasAttribute(): string
    {
        $univ = trim($this->tujuan_universitas ?? '');
        return preg_replace('/^(universitas|univ\.?)\s+/i', '', $univ);
    }

    /**
     * Format nomor surat yang aman untuk nama file unduhan PDF
     */
    public function getNoCleanAttribute(): string
    {
        return str_replace(['/', '\\', ' '], '-', $this->no ?? 'surat-balasan-pkl');
    }
}
