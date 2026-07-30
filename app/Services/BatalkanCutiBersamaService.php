<?php

namespace App\Services;

use App\Enums\StatusKehadiran;
use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Surat\SuratCuti;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BatalkanCutiBersamaService
{
    /**
     * Membatalkan Cuti Bersama dan mengembalikan kuota/jadwal.
     */
    public function batalkan(CutiBersama $cutiBersama, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($cutiBersama, $userId) {
            $cutiBersama->loadMissing('tanggal');
            $userId = $userId ?? auth()->id() ?? 1;

            // 1. Cek kunci payroll
            foreach ($cutiBersama->tanggal as $tglRecord) {
                $periode = Carbon::parse($tglRecord->tanggal)->format('Y-m');
                
                $isLocked = DB::table('sdm_payroll_period_locks')
                    ->where('periode', $periode)
                    ->where(function ($q) {
                        $q->where('is_approved', true)
                          ->orWhere('status', 'locked');
                    })
                    ->exists();

                if ($isLocked) {
                    throw new Exception("Gagal membatalkan: Periode payroll {$periode} telah dikunci.");
                }
            }

            // 2. Hapus surat_cuti otomatis hasil Cuti Bersama ini
            SuratCuti::where('cuti_bersama_id', $cutiBersama->id)
                ->where('sumber', 'cuti_bersama')
                ->delete();

            // 3. Kembalikan status kehadiran di sdm_jadwal_kerja_detail
            $tglList = $cutiBersama->tanggal->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

            JadwalKerjaDetail::whereIn(DB::raw('DATE(tanggal)'), $tglList)
                ->where('status_kehadiran', StatusKehadiran::CUTI_BERSAMA)
                ->update([
                    'status_kehadiran' => StatusKehadiran::BELUM_DICEK,
                    'catatan' => null,
                    'updated_by' => $userId,
                ]);

            // 4. Update status Cuti Bersama
            $cutiBersama->update([
                'status' => 'dibatalkan',
                'updated_by' => $userId,
            ]);

            return true;
        });
    }
}
