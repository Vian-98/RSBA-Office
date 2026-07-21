<?php

namespace App\Observers;

use App\Models\Surat\SuratCuti;
use App\Services\DocstoreSyncService;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Enums\StatusApproval;
use App\Enums\StatusKehadiran;

class SuratCutiObserver
{
    protected DocstoreSyncService $syncService;

    public function __construct(DocstoreSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saved(SuratCuti $surat)
    {
        // Cuti bersama can create many surat in one request; avoid synchronous
        // external Docstore calls here to keep Livewire apply action responsive.
        if ($surat->sumber !== 'cuti_bersama') {
            $this->syncService->syncCuti($surat);
        }

        // Sync to JadwalKerjaDetail
        $dates = json_decode($surat->tgl_cuti, true);
        if (is_array($dates) && !empty($dates)) {
            if ($surat->status === StatusApproval::APPROVED) {
                if ($surat->sumber === 'cuti_bersama') {
                    $statusKehadiran = StatusKehadiran::CUTI_BERSAMA;
                } else {
                    $statusKehadiran = match ((int)$surat->urgensi_id) {
                        4 => StatusKehadiran::IZIN,
                        default => StatusKehadiran::CUTI,
                    };
                }

                JadwalKerjaDetail::where('karyawan_id', $surat->karyawan_id)
                    ->whereIn('tanggal', $dates)
                    ->update([
                        'status_kehadiran' => $statusKehadiran,
                        'catatan' => $surat->jenis?->nama . ' resmi (' . $surat->no_surat . ')'
                    ]);
            } elseif ($surat->status === StatusApproval::REJECTED) {
                JadwalKerjaDetail::where('karyawan_id', $surat->karyawan_id)
                    ->whereIn('tanggal', $dates)
                    ->whereIn('status_kehadiran', [StatusKehadiran::CUTI, StatusKehadiran::IZIN, StatusKehadiran::CUTI_BERSAMA])
                    ->update([
                        'status_kehadiran' => StatusKehadiran::BELUM_DICEK,
                        'catatan' => null
                    ]);
            }
        }
    }

    public function deleted(SuratCuti $surat)
    {
        // Restore JadwalKerjaDetail to unchecked state on delete
        $dates = json_decode($surat->tgl_cuti, true);
        if (is_array($dates) && !empty($dates)) {
            JadwalKerjaDetail::where('karyawan_id', $surat->karyawan_id)
                ->whereIn('tanggal', $dates)
                ->whereIn('status_kehadiran', [StatusKehadiran::CUTI, StatusKehadiran::IZIN, StatusKehadiran::CUTI_BERSAMA])
                ->update([
                    'status_kehadiran' => StatusKehadiran::BELUM_DICEK,
                    'catatan' => null
                ]);
        }
    }
}
