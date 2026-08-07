<?php

namespace App\Livewire\Gaji\Services;

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
            if (!empty($karyawan->pendidikan_setara)) {
                $rowKey = $karyawan->pendidikan_setara;
            } else {
                $allPendidikan = DB::table('sdm_kary_pendidikan')
                    ->where('karyawan_id', $karyawan->id)
                    ->get();

                $tingkat = 'sma';
                $maxScore = 0;
                $scoreMap = [
                    's2' => 4, 's3' => 4, 'spesialis' => 4,
                    's1' => 3, 'profesi' => 3, 'dokter' => 3,
                    'd3' => 2, 'd4' => 2,
                    'sd' => 1, 'smp' => 1, 'sma' => 1, 'lain' => 1,
                ];

                foreach ($allPendidikan as $p) {
                    $score = $scoreMap[$p->tingkat] ?? 1;
                    if ($score > $maxScore) {
                        $maxScore = $score;
                        $tingkat = $p->tingkat;
                    }
                }

                $rowKey = match ($tingkat) {
                    'sd', 'smp', 'sma', 'lain' => 'SMA/SMK',
                    'd3', 'd4' => 'DIII/DIV',
                    's1', 'profesi', 'dokter' => 'SI/Profesi',
                    's2', 's3', 'spesialis' => 'SII',
                    default => 'SMA/SMK',
                };
            }

            $golonganGrade = \App\Models\Sdm\PayrollGolonganMatrix::lookup($rowKey, $yearsOfService);
            $gajiPokok = 0.75 * $umk * (1.0 + (15.0 - $golonganGrade) * 0.05);

            $tunjanganGolonganVal = (double) DB::table('sdm_payroll_golongans')
                ->where('golongan', $golonganGrade)
                ->value('tunjangan_golongan') ?? ((15 - $golonganGrade) * 50000.0);

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
                $gajiPokok = 0.947 * $umk;
            }
            $tunjanganTetap = 0.0;
            $tunjanganAbsensi = 0.0;
            $allocationsBreakdown = [];
        }

        $tunjanganJabatan = 0.0;
        if ($isTetap) {
            $latestJab = $karyawan->jabatan->first();
            if ($latestJab) {
                $tunjanganJabatan = (double) $latestJab->tunjangan_jabatan;
            }
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
            'masa_kerja_tahun' => $yearsOfService,
        ];
    }

    /**
     * Calculate deductions (BPJS, PPh21) based on base salary variables.
     */
    public static function calculateDeductions($gajiPokok, $tunjanganTetap, $totalPendapatan, int $bpjsKeluargaTambahan = 0, ?Karyawan $karyawan = null, ?string $periode = null): array
    {
        $basis = $gajiPokok + $tunjanganTetap;

        $bpjsKesRate = 0.01 + ($bpjsKeluargaTambahan * 0.01);
        $potonganBpjsKes = $basis * $bpjsKesRate;
        $potonganBpjsTk = $basis * 0.03;

        $pph21_calculated = 0.0;
        $hasNpwp = true;
        $ptkpStatus = 'TK0';
        if ($karyawan) {
            $hasNpwp = !empty($karyawan->npwp);
            $ptkpStatus = $karyawan->ptkp_status ?: 'TK0';
        }

        $periode = $periode ?: date('Y-m');
        $month = (int) substr($periode, 5, 2);
        $year = (int) substr($periode, 0, 4);

        $isReconciliationMonth = ($month === 12);
        if ($karyawan && !empty($karyawan->resign_at)) {
            $resignDate = Carbon::parse($karyawan->resign_at);
            if ($resignDate->year === $year && $resignDate->month === $month) {
                $isReconciliationMonth = true;
            }
        }

        if ($karyawan && $isReconciliationMonth) {
            $priorSlips = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawan->id)
                ->where('periode', 'like', "$year-%")
                ->where('periode', '!=', $periode)
                ->get();

            $priorBruto = $priorSlips->sum('total_gaji');
            $priorPph21 = $priorSlips->sum('potongan_pph21');
            $priorBpjsTk = $priorSlips->sum('potongan_bpjs_tk');

            $total_bruto_ytd = $priorBruto + $totalPendapatan;
            $total_bpjs_tk_ytd = $priorBpjsTk + $potonganBpjsTk;

            $biaya_jabatan = min(0.05 * $total_bruto_ytd, 6000000.00);
            $neto_setahun = $total_bruto_ytd - $biaya_jabatan - $total_bpjs_tk_ytd;

            $ptkpNominal = DB::table('sdm_payroll_ptkp')
                ->where('status', $ptkpStatus)
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('berlaku_mulai_tahun', 'desc')
                ->value('nominal_setahun') ?: 54000000.00;

            $pkp = $neto_setahun - $ptkpNominal;
            if ($pkp < 0) {
                $pkp = 0;
            }
            $pkp = floor($pkp / 1000) * 1000;

            $pasal17Brackets = DB::table('sdm_payroll_pasal17')
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('pkp_bawah', 'asc')
                ->get();

            $pajakSetahun = 0;
            $remainingPkp = $pkp;

            foreach ($pasal17Brackets as $bracket) {
                $bawah = (double) $bracket->pkp_bawah;
                $atas = $bracket->pkp_atas ? (double) $bracket->pkp_atas : null;
                $tarif = (double) $bracket->tarif_persen / 100;

                if ($atas !== null) {
                    $range = $atas - $bawah;
                    if ($remainingPkp > $range) {
                        $pajakSetahun += $range * $tarif;
                        $remainingPkp -= $range;
                    } else {
                        $pajakSetahun += $remainingPkp * $tarif;
                        $remainingPkp = 0;
                        break;
                    }
                } else {
                    $pajakSetahun += $remainingPkp * $tarif;
                    $remainingPkp = 0;
                    break;
                }
            }

            $pph21_calculated = $pajakSetahun - $priorPph21;
        } else {
            $kategoriTer = match ($ptkpStatus) {
                'TK0', 'TK1', 'K0' => 'A',
                'TK2', 'TK3', 'K1', 'K2' => 'B',
                'K3' => 'C',
                default => 'A',
            };

            $tarif_persen = DB::table('sdm_payroll_ter')
                ->where('kategori', $kategoriTer)
                ->where('bruto_bawah', '<=', $totalPendapatan)
                ->where('bruto_atas', '>=', $totalPendapatan)
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('berlaku_mulai_tahun', 'desc')
                ->value('tarif_persen') ?: 0.0;

            $pph21_calculated = $totalPendapatan * ($tarif_persen / 100);
        }

        if (!$hasNpwp) {
            $pph21_calculated = $pph21_calculated * 1.2;
        }

        return [
            'potongan_bpjs_kes' => round($potonganBpjsKes),
            'potongan_bpjs_tk' => round($potonganBpjsTk),
            'potongan_pph21' => max(0, round($pph21_calculated)),
        ];
    }
}
