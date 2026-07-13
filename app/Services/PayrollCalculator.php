<?php

namespace App\Services;

use App\Models\Sdm\Karyawan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollCalculator
{
    /**
     * Calculate base salary and allowance configurations for an employee.
     *
     * @param Karyawan $karyawan
     * @param int $bpjsKeluargaTambahan
     * @return array
     */
    public static function calculate(Karyawan $karyawan, int $bpjsKeluargaTambahan = 0): array
    {
        // 1. Load UMK setting from database
        $umk = (double) DB::table('sdm_payroll_settings')->where('key', 'umk')->value('value') ?: 3000000.0;

        // 2. Load dynamic allocations (25% UMK portion)
        $dbAllocations = DB::table('sdm_payroll_allowance_allocations')->get();
        if ($dbAllocations->isEmpty()) {
            // Fallback default
            $dbAllocations = collect([
                (object) ['id' => 1, 'nama' => 'Tunjangan Tetap', 'persen' => 80.0, 'is_absensi' => 0],
                (object) ['id' => 2, 'nama' => 'Tunjangan Absensi', 'persen' => 20.0, 'is_absensi' => 1],
            ]);
        }

        // Calculate years of service
        $yearsOfService = 0.0;
        if (!empty($karyawan->tgl_masuk)) {
            $yearsOfService = Carbon::parse($karyawan->tgl_masuk)->diffInYears(now());
        }

        $gajiPokok = 0.0;
        $tunjanganTetap = 0.0;
        $tunjanganAbsensi = 0.0;
        $golonganGrade = null;
        $tunjanganGolonganVal = 0.0;
        $allocationsBreakdown = [];

        $isTetap = ($karyawan->status?->value === 'tetap');

        if ($isTetap) {
            // --- KARYAWAN TETAP ---
            // 1. Determine education row
            $latestPendidikan = DB::table('sdm_kary_pendidikan')
                ->where('karyawan_id', $karyawan->id)
                ->orderBy('tahun_lulus', 'desc')
                ->first();

            $tingkat = $latestPendidikan ? $latestPendidikan->tingkat : 'sma';
            $rowKey = match ($tingkat) {
                'sd', 'smp', 'sma', 'lain' => 'SMA/SMK',
                'd3', 'd4' => 'DIII/DIV',
                's1', 'profesi', 'dokter' => 'SI/Profesi',
                's2', 's3', 'spesialis' => 'SII',
                default => 'SMA/SMK',
            };

            // 2. Determine years of service column (step interval: 0, 3, 6, ..., 39)
            $masaKerjaKeys = [0, 3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36, 39];
            $selectedKey = 0;
            foreach ($masaKerjaKeys as $key) {
                if ($yearsOfService >= $key) {
                    $selectedKey = $key;
                } else {
                    break;
                }
            }

            // 3. Matrix lookup for Golongan (1 to 15)
            $grid = [
                'SMA/SMK' => [0 => 15, 3 => 15, 6 => 14, 9 => 13, 12 => 12, 15 => 11, 18 => 10, 21 => 9, 24 => 8, 27 => 7, 30 => 6, 33 => 5, 36 => 4, 39 => 3],
                'DIII/DIV' => [0 => 15, 3 => 14, 6 => 13, 9 => 12, 12 => 11, 15 => 10, 18 => 9, 21 => 8, 24 => 7, 27 => 6, 30 => 5, 33 => 4, 36 => 3, 39 => 2],
                'SI/Profesi' => [0 => 14, 3 => 13, 6 => 12, 9 => 11, 12 => 10, 15 => 9, 18 => 8, 21 => 7, 24 => 6, 27 => 5, 30 => 4, 33 => 3, 36 => 2, 39 => 1],
                'SII' => [0 => 13, 3 => 12, 6 => 11, 9 => 10, 12 => 9, 15 => 8, 18 => 7, 21 => 6, 24 => 5, 27 => 4, 30 => 3, 33 => 2, 36 => 1, 39 => 1],
            ];

            $golonganGrade = $grid[$rowKey][$selectedKey] ?? 15;

            // 4. Calculate Basic Salary (Gapok) based on Golongan Grade
            // Formula: 75% * UMK * (1 + (15 - Golongan) * 0.05)
            $gajiPokok = 0.75 * $umk * (1.0 + (15.0 - $golonganGrade) * 0.05);

            // 5. Load Tunjangan Golongan from DB
            $tunjanganGolonganVal = (double) DB::table('sdm_payroll_golongans')
                ->where('golongan', $golonganGrade)
                ->value('tunjangan_golongan') ?? ((15 - $golonganGrade) * 50000.0);

            // 6. Calculate Dynamic UMK Allocations
            $tunjanganPokok = 0.25 * $umk;
            foreach ($dbAllocations as $alloc) {
                $nominal = $tunjanganPokok * ((double) $alloc->persen / 100.0);
                
                if ($alloc->is_absensi) {
                    $tunjanganAbsensi += $nominal;
                } else {
                    $tunjanganTetap += $nominal;
                }

                $allocationsBreakdown[] = [
                    'allowance_allocation_id' => $alloc->id,
                    'nama' => $alloc->nama,
                    'persen' => (double) $alloc->persen,
                    'is_absensi' => (bool) $alloc->is_absensi,
                    'nominal' => round($nominal),
                ];
            }
            $tunjanganTetap += $tunjanganGolonganVal;
        } else {
            // --- KARYAWAN TIDAK TETAP ---
            if ($yearsOfService <= 1.5) {
                $gajiPokok = 0.857 * $umk;
            } elseif ($yearsOfService <= 5.0) {
                $gajiPokok = 0.903 * $umk;
            } elseif ($yearsOfService <= 6.0) {
                $gajiPokok = 0.910290 * $umk;
            } else {
                $gajiPokok = 0.947 * $umk; // Tetap Non Golongan
            }
            $tunjanganTetap = 0.0;
            $tunjanganAbsensi = 0.0;
            $allocationsBreakdown = [];
        }

        // 7. Calculate Tunjangan Jabatan based on Jabatan
        $tunjanganJabatan = 0.0;
        $latestJab = $karyawan->jabatan->first();
        if ($latestJab) {
            $tunjanganJabatan = (double) $latestJab->tunjangan_jabatan;
        }

        return [
            'gaji_pokok' => round($gajiPokok),
            'tunjangan_tetap' => round($tunjanganTetap),
            'tunjangan_absensi' => round($tunjanganAbsensi),
            'tunjangan_jabatan' => round($tunjanganJabatan),
            'golongan' => $golonganGrade,
            'tunjangan_golongan_value' => round($tunjanganGolonganVal),
            'umk' => $umk,
            'allocations_breakdown' => $allocationsBreakdown,
        ];
    }

    /**
     * Calculate deductions (BPJS, PPh21) based on base salary variables.
     *
     * @param double $gajiPokok
     * @param double $tunjanganTetap
     * @param double $totalPendapatan
     * @param int $bpjsKeluargaTambahan
     * @return array
     */
    public static function calculateDeductions($gajiPokok, $tunjanganTetap, $totalPendapatan, int $bpjsKeluargaTambahan = 0): array
    {
        // BPJS calculation basis: Gapok + Tunjangan Tetap
        $basis = $gajiPokok + $tunjanganTetap;

        // BPJS Kesehatan (BPJS Kes)
        $bpjsKesRate = 0.01 + ($bpjsKeluargaTambahan * 0.01);
        $potonganBpjsKes = $basis * $bpjsKesRate;

        // BPJS Ketenagakerjaan (BPJS TK)
        $potonganBpjsTk = $basis * 0.03;

        // PPh 21 Calculation: 5% flat of taxable income
        $taxableIncome = $totalPendapatan - ($potonganBpjsKes + $potonganBpjsTk);
        if ($taxableIncome < 0) {
            $taxableIncome = 0;
        }
        $potonganPph21 = $taxableIncome * 0.05;

        return [
            'potongan_bpjs_kes' => round($potonganBpjsKes),
            'potongan_bpjs_tk' => round($potonganBpjsTk),
            'potongan_pph21' => round($potonganPph21),
        ];
    }
}
