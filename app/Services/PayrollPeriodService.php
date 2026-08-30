<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollPeriodService
{
    /**
     * Get lock status & permissions for a payroll period.
     */
    public function getLockStatus(string $periode): array
    {
        $lock = DB::table('sdm_payroll_period_locks')
            ->where('periode', $periode)
            ->first();

        $status = $lock->status ?? 'draft';
        $user = auth()->user();
        $canApprovePajak = $user && $user->can('approve-kepegawaian-gaji-pajak');
        $canApproveGaji = $user && $user->can('approve-kepegawaian-gaji');

        $isLocked = false;
        if ($status === 'approved') {
            $isLocked = true;
        } elseif ($status === 'review_pajak') {
            $isLocked = !$canApprovePajak;
        } elseif ($status === 'review_sdm') {
            $isLocked = true;
        } else {
            // Draft: terkunci bagi reviewer pajak murni
            $isLocked = $canApprovePajak && !$canApproveGaji;
        }

        return [
            'status' => $status,
            'is_locked' => $isLocked,
            'is_approved' => $lock ? (bool) $lock->is_approved : false,
        ];
    }

    /**
     * SDM submits draft payroll to Pajak team for review.
     */
    public function submitToReviewPajak(string $periode): void
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'draft') {
            throw new \Exception('Periode ini sudah tidak dalam status draft.');
        }

        DB::table('sdm_payroll_period_locks')->updateOrInsert(
            ['periode' => $periode],
            [
                'status' => 'review_pajak',
                'is_approved' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Pajak team approves & sends back to SDM for final approval.
     */
    public function approveByPajak(string $periode): void
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'review_pajak') {
            throw new \Exception('Periode ini tidak dalam status review pajak.');
        }

        DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
            'status' => 'review_sdm',
            'updated_at' => now(),
        ]);
    }

    /**
     * Pajak team rejects and sends back to SDM draft.
     */
    public function rejectByPajak(string $periode): void
    {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        $currentStatus = $lock->status ?? 'draft';

        if ($currentStatus !== 'review_pajak') {
            throw new \Exception('Periode ini tidak dalam status review pajak.');
        }

        DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
            'status' => 'draft',
            'updated_at' => now(),
        ]);
    }

    /**
     * Submit finalisation: Lock period & generate automatic SP3 document.
     */
    public function submitFinalisasi(
        string $periode,
        string $formSp3Tgl,
        string $formSp3Bayar,
        int $formSp3JabatanId,
        int $karyawanCount,
        float $totalGajiBersih,
        ?int $formSp3VerifikatorKeuanganId = null,
        ?int $userId = null
    ): void {
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', $periode)->first();
        if (!$lock || $lock->status !== 'review_sdm') {
            throw new \Exception('Periode ini belum mendapat persetujuan dari Tim Pajak.');
        }

        DB::beginTransaction();
        try {
            // 1. Lock period & set approved status
            DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
                'is_approved' => true,
                'status' => 'approved',
                'approved_by' => $userId ?: auth()->id(),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Generate automatic SP3 number
            $last = DB::table('surat_sp3')
                ->select('no')
                ->where('jabatan_id', $formSp3JabatanId)
                ->orderBy('id', 'desc')
                ->first();

            $jab = DB::table('sdm_jabatan')->where('id', $formSp3JabatanId)->first();
            $tanggal = date('d.m.Y', strtotime($formSp3Tgl));
            $no = 1;
            if ($last) {
                $fullNomor = explode('/', $last->no);
                $lastNomor = $fullNomor[0];
                $no = (int) $lastNomor + 1;
            }
            $kodeSurat = $jab->kode_surat ?? 'DIR';
            $noSurat = "{$no}/S4/SP.3/PBA-{$kodeSurat}/{$tanggal}";

            // 3. Create SP3
            $sp3Id = DB::table('surat_sp3')->insertGetId([
                'no' => $noSurat,
                'tahun' => date('Y', strtotime($formSp3Tgl)),
                'tgl' => $formSp3Tgl,
                'rekanan' => 'Gaji Karyawan',
                'bayar' => $formSp3Bayar,
                'keterangan' => 'Pembayaran Gaji Karyawan RSBA Periode ' . Carbon::parse($periode . '-01')->translatedFormat('F Y'),
                'status' => 'pending',
                'payroll_periode' => $periode,
                'jabatan_id' => $formSp3JabatanId,
                'verifikator_keuangan_id' => $formSp3VerifikatorKeuanganId,
                'created_by' => $userId ?: auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create SP3 details
            DB::table('surat_sp3_details')->insert([
                'sp3_id' => $sp3Id,
                'keterangan' => 'Total Gaji Bersih Periode ' . Carbon::parse($periode . '-01')->translatedFormat('F Y') . ' (' . $karyawanCount . ' Karyawan)',
                'nominal' => $totalGajiBersih,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Unlock a locked payroll period.
     */
    public function unlockPeriode(string $periode, bool $isSuperAdmin = false): void
    {
        $sp3 = DB::table('surat_sp3')->where('payroll_periode', $periode)->first();
        if ($sp3 && $sp3->status === 'approved' && !$isSuperAdmin) {
            throw new \Exception('Surat SP3 untuk periode ini telah disetujui Direksi. Hanya Super Admin yang dapat membuka kunci.');
        }

        DB::beginTransaction();
        try {
            DB::table('sdm_payroll_period_locks')->where('periode', $periode)->update([
                'status' => 'draft',
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => now(),
            ]);

            if ($sp3) {
                DB::table('surat_sp3_details')->where('sp3_id', $sp3->id)->delete();
                DB::table('surat_sp3')->where('id', $sp3->id)->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
