<?php

namespace App\Models\Sdm;

use App\Models\Ruangan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends Model
{
    protected $table = 'sdm_workflow_approval';
    protected $guarded = [];

    protected $casts = [
        'step_number' => 'integer',
    ];

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function tingkatJabatan(): BelongsTo
    {
        return $this->belongsTo(JabatanTingkat::class, 'tingkat_jabatan_id');
    }

    /**
     * Resolve target approver level for a specific room and schedule type
     */
    public static function getTargetTingkatId(?int $ruanganId, int $stepNumber, string $tipeJadwal = 'karyawan'): ?int
    {
        // 1. Cek rule khusus per ruangan
        if ($ruanganId) {
            $rule = static::where('ruangan_id', $ruanganId)
                ->where('step_number', $stepNumber)
                ->where(function ($q) use ($tipeJadwal) {
                    $q->whereNull('tipe_jadwal')->orWhere('tipe_jadwal', $tipeJadwal);
                })
                ->first();

            if ($rule) {
                return $rule->tingkat_jabatan_id;
            }
        }

        // 2. Fallback rule global
        $globalRule = static::whereNull('ruangan_id')
            ->where('step_number', $stepNumber)
            ->where(function ($q) use ($tipeJadwal) {
                $q->whereNull('tipe_jadwal')->orWhere('tipe_jadwal', $tipeJadwal);
            })
            ->first();

        return $globalRule?->tingkat_jabatan_id ?? ($stepNumber === 1 ? 3 : 2);
    }
}
