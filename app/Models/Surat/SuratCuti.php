<?php

namespace App\Models\Surat;

use App\Enums\StatusApproval;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SuratCuti extends Model
{
    protected $table = 'surat_cuti';
    protected $guarded = [];

    protected $casts = [
        'status' => StatusApproval::class,
        'is_penyesuaian_melahirkan' => 'boolean',
        'docstore_synced_at' => 'datetime',
        'tgl_surat' => 'date',
        'tgl_mulai' => 'date',
        'tgl_akhir' => 'date',
        'tgl_melahirkan_aktual' => 'date',
        'signed_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function created_oleh()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // accessor user_created
    public function getUserCreatedAttribute(): string
    {
        if ($this->relationLoaded('created_oleh') && $this->created_oleh?->relationLoaded('karyawan')) {
            return $this->created_oleh?->karyawan->nama ?? '-';
        }

        return optional(optional($this->created_oleh)?->karyawan)?->nama ?? '-';
    }

    public function jenis()
    {
        return $this->belongsTo(CutiJenis::class, 'urgensi_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(SuratCutiApproval::class, 'surat_cuti_id', 'id');
    }

    public function cutiBersama()
    {
        return $this->belongsTo(\App\Models\Sdm\CutiBersama::class, 'cuti_bersama_id', 'id');
    }

    /**
     * Penyesuaian tanggal selesai Cuti Melahirkan (H+45 dari tanggal persalinan aktual) oleh SDM
     */
    public function adjustCutiMelahirkan(string $tglMelahirkanAktual, ?string $catatan = null): void
    {
        $tglMulai = \Carbon\Carbon::parse($this->tgl_mulai);
        $tglAktual = \Carbon\Carbon::parse($tglMelahirkanAktual);

        // H+45 hari dari tanggal melahirkan aktual
        $tglAkhirBaru = $tglAktual->copy()->addDays(45);

        // Generate list tanggal dari tgl_mulai sampai tglAkhirBaru
        $period = \Carbon\CarbonPeriod::create($tglMulai, $tglAkhirBaru);
        $arrTglCuti = [];
        foreach ($period as $date) {
            $arrTglCuti[] = $date->format('Y-m-d');
        }

        $this->update([
            'tgl_akhir' => $tglAkhirBaru->format('Y-m-d'),
            'tgl_cuti' => json_encode($arrTglCuti),
            'lama_cuti' => count($arrTglCuti),
            'tgl_melahirkan_aktual' => $tglMelahirkanAktual,
            'is_penyesuaian_melahirkan' => true,
            'catatan_penyesuaian' => $catatan,
            'updated_by' => auth()->id(),
        ]);

        if (class_exists(\App\Services\DocstoreSyncService::class)) {
            try {
                app(\App\Services\DocstoreSyncService::class)->syncCuti($this->fresh());
            } catch (\Throwable $e) {
                // log exception if needed
            }
        }
    }
}
