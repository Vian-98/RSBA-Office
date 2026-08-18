<?php

namespace App\Services;

use App\Models\Sdm\Karyawan;
use App\Livewire\Gaji\Services\PayrollCalculator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollRekapService
{
    /**
     * Get summary metrics for a given payroll period.
     */
    public function getSummary(string $periode): array
    {
        $slips = DB::table('sdm_payroll_slips')
            ->where('periode', $periode)
            ->get();

        $totalGajiBersih = (float) $slips->sum('gaji_bersih');
        $totalPotongan = (float) ($slips->sum('total_potongan') + $slips->sum('potongan_pph21') + $slips->sum('potongan_bank'));
        $jumlahKaryawan = $slips->count();

        $lastMonthPeriode = Carbon::parse($periode . '-01')->subMonth()->format('Y-m');
        $lastMonthNet = (float) DB::table('sdm_payroll_slips')
            ->where('periode', $lastMonthPeriode)
            ->sum('gaji_bersih');

        $percentChange = 0.0;
        if ($lastMonthNet > 0) {
            $percentChange = (($totalGajiBersih - $lastMonthNet) / $lastMonthNet) * 100;
        }

        $estimasiBulanDepan = 0.0;
        foreach ($slips as $slip) {
            $routineEarnings = (double) $slip->gaji_pokok + 
                (double) $slip->tunjangan_tetap + 
                (double) $slip->tunjangan_absensi + 
                (double) $slip->tunjangan_jabatan + 
                (double) $slip->tunjangan_shift + 
                (double) $slip->tunjangan_radiologi + 
                (double) $slip->tunjangan_lain;

            $routineDeductions = (double) $slip->potongan_bpjs_kes + 
                (double) $slip->potongan_bpjs_tk + 
                (double) $slip->potongan_pph21 + 
                (double) $slip->potongan_bank;

            $estimasiBulanDepan += ($routineEarnings - $routineDeductions);
        }

        $potonganBreakdown = [
            'bpjs_kes' => (double) $slips->sum('potongan_bpjs_kes'),
            'bpjs_tk' => (double) $slips->sum('potongan_bpjs_tk'),
            'pph21' => (double) $slips->sum('potongan_pph21'),
            'absensi' => (double) $slips->sum('potongan_absensi'),
            'cash_bon' => (double) $slips->sum('potongan_cash_bon'),
            'obat' => (double) $slips->sum('potongan_obat'),
            'bank' => (double) $slips->sum('potongan_bank'),
            'lain' => (double) $slips->sum('potongan_lain'),
        ];

        return [
            'slips' => $slips,
            'totalGajiBersih' => $totalGajiBersih,
            'totalPotongan' => $totalPotongan,
            'jumlahKaryawan' => $jumlahKaryawan,
            'lastMonthNet' => $lastMonthNet,
            'percentChange' => $percentChange,
            'estimasiBulanDepan' => $estimasiBulanDepan,
            'potonganBreakdown' => $potonganBreakdown,
        ];
    }

    /**
     * Calculate department breakdown from slips collection.
     */
    public function getDepartmentBreakdown($slips): array
    {
        $karyawanIds = $slips->pluck('karyawan_id')->unique()->toArray();
        $karyawans = Karyawan::with(['jabatan.bagian'])
            ->whereIn('id', $karyawanIds)
            ->get()
            ->keyBy('id');

        $bagianBreakdown = [];
        foreach ($slips as $slip) {
            $karyawan = $karyawans->get($slip->karyawan_id);
            $bagianNama = $karyawan && $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian 
                ? $karyawan->jabatan->first()->bagian->nama 
                : 'Umum';

            if (!isset($bagianBreakdown[$bagianNama])) {
                $bagianBreakdown[$bagianNama] = [
                    'total_gaji_bersih' => 0.0,
                    'karyawan_count' => 0,
                ];
            }
            $bagianBreakdown[$bagianNama]['total_gaji_bersih'] += $slip->gaji_bersih;
            $bagianBreakdown[$bagianNama]['karyawan_count'] += 1;
        }

        uasort($bagianBreakdown, fn($a, $b) => $b['total_gaji_bersih'] <=> $a['total_gaji_bersih']);

        return $bagianBreakdown;
    }

    /**
     * Get 6-month trend statistics ending at specified period.
     */
    public function getSixMonthTrend(string $periode): array
    {
        $currentDate = Carbon::parse($periode . '-01');
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $currentDate->copy()->subMonths($i)->format('Y-m');
        }

        $trendMonths = [];
        foreach ($months as $m) {
            $mSlips = DB::table('sdm_payroll_slips')->where('periode', $m)->get();
            $lock = DB::table('sdm_payroll_period_locks')->where('periode', $m)->first();
            $trendMonths[] = [
                'periode' => $m,
                'label' => Carbon::parse($m . '-01')->translatedFormat('F Y'),
                'total_gaji_bersih' => $mSlips->sum('gaji_bersih'),
                'total_potongan' => $mSlips->sum('total_potongan') + $mSlips->sum('potongan_pph21') + $mSlips->sum('potongan_bank'),
                'karyawan_count' => $mSlips->count(),
                'is_approved' => $lock ? (bool) $lock->is_approved : false,
                'status' => $lock->status ?? 'draft',
                'sp3_status' => DB::table('surat_sp3')->where('payroll_periode', $m)->value('status'),
            ];
        }

        return $trendMonths;
    }

    /**
     * Generate dynamic insight text based on current and last month metrics.
     */
    public function generateInsight(string $periode, int $jumlahKaryawan, float $lastMonthNet, float $percentChange): string
    {
        if ($jumlahKaryawan === 0) {
            return "Belum ada data slip gaji yang dicatat untuk periode " . Carbon::parse($periode . '-01')->translatedFormat('F Y') . ". Silakan kelola gaji karyawan terlebih dahulu.";
        }

        if ($lastMonthNet > 0) {
            $absChange = abs(round($percentChange, 1));
            if ($percentChange > 0) {
                return "Pengeluaran gaji bulan ini meningkat " . $absChange . "% dibandingkan bulan lalu (" . Carbon::parse($periode . '-01')->subMonth()->translatedFormat('F Y') . "). Hal ini dipengaruhi oleh penambahan slip gaji baru atau peningkatan jam lembur karyawan.";
            } elseif ($percentChange < 0) {
                return "Pengeluaran gaji bulan ini menurun " . $absChange . "% dibandingkan bulan lalu (" . Carbon::parse($periode . '-01')->subMonth()->translatedFormat('F Y') . "). Hal ini menunjukkan adanya efisiensi biaya atau pengurangan jumlah potongan/lembur pada periode ini.";
            } else {
                return "Pengeluaran gaji bulan ini sama persis dengan bulan lalu (" . Carbon::parse($periode . '-01')->subMonth()->translatedFormat('F Y') . "). Anggaran belanja pegawai terpantau stabil.";
            }
        }

        return "Bulan lalu (" . Carbon::parse($periode . '-01')->subMonth()->translatedFormat('F Y') . ") belum memiliki data penggajian. Ini adalah bulan awal rekapitulasi data penggajian yang tercatat di sistem.";
    }

    /**
     * Export employee payroll list to formatted HTML/Excel stream response.
     */
    public function exportExcelResponse($karyawans, string $periode)
    {
        $isDecember = str_ends_with($periode, '-12');
        $year = (int) substr($periode, 0, 4);

        $filename = "rekap_gaji_" . $periode . "_" . now()->format('Ymd_His') . ".xls";
        
        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($karyawans, $isDecember, $year, $periode) {
            $output = fopen('php://output', 'w');
            fwrite($output, '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">');
            fwrite($output, '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" />');
            fwrite($output, '<style>
                table { border-collapse: collapse; }
                th { background-color: #4F46E5; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #D1D5DB; padding: 10px 8px; font-size: 11pt; font-family: Calibri, sans-serif; }
                .th-ytd { background-color: #0369A1; }
                td { border: 1px solid #D1D5DB; padding: 8px 6px; font-size: 10pt; font-family: Calibri, sans-serif; }
                .text-cell { mso-number-format: "\@"; text-align: left; }
                .money-cell { text-align: right; }
                .bold-money-cell { font-weight: bold; text-align: right; }
                .center-cell { text-align: center; }
            </style></head><body>');
            
            $titlePeriode = Carbon::parse($periode . '-01')->translatedFormat('F Y');
            fwrite($output, '<h3 style="font-family: Calibri, sans-serif; margin-bottom: 15px;">REKAP PENGGAJIAN KARYAWAN - PERIODE ' . strtoupper($titlePeriode) . '</h3>');
            fwrite($output, '<table><thead><tr>');
            
            $columns = [
                'No', 'NIP', 'Nama Karyawan', 'Bagian', 'Jabatan', 'Status Kerja', 'Status Input',
                'Gaji Pokok', 'Tunjangan Tetap', 'Tunjangan Absensi', 'Tunjangan Jabatan', 'Tunjangan Shift', 
                'Tunjangan Radiologi', 'Tunjangan Lain', 'Uang Lembur', 'Tunjangan THR', 'Total Pendapatan (Bruto)',
                'Potongan Absensi', 'Potongan Cash Bon', 'Potongan Obat', 'Potongan Lain', 'Potongan Bank',
                'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'PPh 21', 'Total Potongan', 'Gaji Bersih'
            ];

            foreach ($columns as $col) {
                fwrite($output, '<th>' . $col . '</th>');
            }

            if ($isDecember) {
                fwrite($output, '<th class="th-ytd">Bruto YTD (Setahun)</th>');
                fwrite($output, '<th class="th-ytd">PPh21 YTD (Setahun)</th>');
            }

            fwrite($output, '</tr></thead><tbody>');

            $no = 1;
            foreach ($karyawans as $karyawan) {
                $slip = DB::table('sdm_payroll_slips')
                    ->where('karyawan_id', $karyawan->id)
                    ->where('periode', $periode)
                    ->first();

                $brutoYtd = 0.0;
                $pph21Ytd = 0.0;

                if ($isDecember) {
                    $priorSlips = DB::table('sdm_payroll_slips')
                        ->where('karyawan_id', $karyawan->id)
                        ->where('periode', 'like', "$year-%")
                        ->where('periode', '!=', $periode)
                        ->get();

                    foreach ($priorSlips as $ps) {
                        $brutoYtd += (double) $ps->gaji_pokok + (double) $ps->tunjangan_tetap + (double) $ps->tunjangan_absensi + (double) $ps->tunjangan_jabatan + (double) $ps->tunjangan_shift + (double) $ps->tunjangan_radiologi + (double) $ps->tunjangan_lain + (double) $ps->uang_lembur + (double) $ps->tunjangan_hari_raya;
                        $pph21Ytd += (double) $ps->potongan_pph21;
                    }
                }

                if ($slip) {
                    $statusInput = 'Selesai';
                    $gajiPokok = (double) $slip->gaji_pokok;
                    $tunjanganTetap = (double) $slip->tunjangan_tetap;
                    $tunjanganAbsensi = (double) $slip->tunjangan_absensi;
                    $tunjanganJabatan = (double) $slip->tunjangan_jabatan;
                    $tunjanganShift = (double) $slip->tunjangan_shift;
                    $tunjanganRadiologi = (double) $slip->tunjangan_radiologi;
                    $tunjanganLain = (double) $slip->tunjangan_lain;
                    $uangLembur = (double) $slip->uang_lembur;
                    $thr = (double) $slip->tunjangan_hari_raya;
                    $totalBruto = $gajiPokok + $tunjanganTetap + $tunjanganAbsensi + $tunjanganJabatan + $tunjanganShift + $tunjanganRadiologi + $tunjanganLain + $uangLembur + $thr;
                    
                    $potAbsensi = (double) $slip->potongan_absensi;
                    $potCashBon = (double) $slip->potongan_cash_bon;
                    $potObat = (double) $slip->potongan_obat;
                    $potLain = (double) $slip->potongan_lain;
                    $potBank = (double) $slip->potongan_bank;
                    $bpjsKes = (double) $slip->potongan_bpjs_kes;
                    $bpjsTk = (double) $slip->potongan_bpjs_tk;
                    $pph21 = (double) $slip->potongan_pph21;
                    $totalPotongan = $potAbsensi + $potCashBon + $potObat + $potLain + $potBank + $bpjsKes + $bpjsTk + $pph21;
                    $gajiBersih = (double) $slip->gaji_bersih;

                    if ($isDecember) {
                        $brutoYtd += $totalBruto;
                        $pph21Ytd += $pph21;
                    }
                } else {
                    $statusInput = 'Belum Input';
                    $base = PayrollCalculator::calculate($karyawan);
                    $totalPendapatan = (double) ($base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan']);
                    $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $periode);
                    
                    $gajiPokok = (double) $base['gaji_pokok'];
                    $tunjanganTetap = (double) $base['tunjangan_tetap'];
                    $tunjanganAbsensi = (double) $base['tunjangan_absensi'];
                    $tunjanganJabatan = (double) $base['tunjangan_jabatan'];
                    $tunjanganShift = 0.0;
                    $tunjanganRadiologi = 0.0;
                    $tunjanganLain = 0.0;
                    $uangLembur = 0.0;
                    $thr = 0.0;
                    $totalBruto = $totalPendapatan;
                    
                    $potAbsensi = 0.0;
                    $potCashBon = 0.0;
                    $potObat = 0.0;
                    $potLain = 0.0;
                    $potBank = 0.0;
                    $bpjsKes = (double) $deductions['potongan_bpjs_kes'];
                    $bpjsTk = (double) $deductions['potongan_bpjs_tk'];
                    $pph21 = (double) $deductions['potongan_pph21'];
                    $totalPotongan = $bpjsKes + $bpjsTk + $pph21;
                    $gajiBersih = $totalBruto - $totalPotongan;

                    if ($isDecember) {
                        $brutoYtd += $totalBruto;
                        $pph21Ytd += $pph21;
                    }
                }

                $bagian = $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum';
                $jabatan = $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff';

                fwrite($output, '<tr>');
                fwrite($output, '<td class="center-cell">' . $no++ . '</td>');
                fwrite($output, '<td class="text-cell">' . $karyawan->nip . '</td>');
                fwrite($output, '<td>' . htmlspecialchars($karyawan->full_nama) . '</td>');
                fwrite($output, '<td>' . htmlspecialchars($bagian) . '</td>');
                fwrite($output, '<td>' . htmlspecialchars($jabatan) . '</td>');
                fwrite($output, '<td class="center-cell">' . htmlspecialchars($karyawan->status->nama()) . '</td>');
                fwrite($output, '<td class="center-cell">' . htmlspecialchars($statusInput) . '</td>');
                
                fwrite($output, '<td class="money-cell">Rp ' . number_format($gajiPokok, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganTetap, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganAbsensi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganJabatan, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganShift, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganRadiologi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganLain, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($uangLembur, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($thr, 0, ',', '.') . '</td>');
                
                fwrite($output, '<td class="bold-money-cell">Rp ' . number_format($totalBruto, 0, ',', '.') . '</td>');
                
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potAbsensi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potCashBon, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potObat, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potLain, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potBank, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($bpjsKes, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($bpjsTk, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($pph21, 0, ',', '.') . '</td>');
                
                fwrite($output, '<td class="bold-money-cell">Rp ' . number_format($totalPotongan, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="bold-money-cell" style="color: #4F46E5;">Rp ' . number_format($gajiBersih, 0, ',', '.') . '</td>');

                if ($isDecember) {
                    fwrite($output, '<td class="bold-money-cell">Rp ' . number_format($brutoYtd, 0, ',', '.') . '</td>');
                    fwrite($output, '<td class="bold-money-cell" style="color: #ea580c;">Rp ' . number_format($pph21Ytd, 0, ',', '.') . '</td>');
                }

                fwrite($output, '</tr>');
            }

            fwrite($output, '</tbody></table></body></html>');
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
