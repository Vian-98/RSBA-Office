<?php

namespace App\Livewire\Gaji\Services;

use App\Models\Sdm\Karyawan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    public function calculateAttendanceStats(int $karyawanId, string $periode): array
    {
        if (empty($periode)) {
            return ['late_minutes' => 0, 'late_count' => 0, 'overtime_minutes' => 0];
        }

        try {
            $parsedDate = Carbon::parse($periode . '-01');
            $bulan = $parsedDate->month;
            $tahun = $parsedDate->year;
        } catch (\Exception $e) {
            return ['late_minutes' => 0, 'late_count' => 0, 'overtime_minutes' => 0];
        }

        $details = DB::table('sdm_jadwal_kerja_detail')
            ->where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $toleransiTelat = (int) (DB::table('sdm_payroll_settings')->where('key', 'toleransi_telat_menit')->value('value') ?: 0);
        $lateMinutes = 0;
        $lateCount = 0;
        $overtimeMinutes = 0;

        foreach ($details as $d) {
            if ($d->status_kehadiran && strtolower($d->status_kehadiran) === 'terlambat') {
                $lateCount++;
                if ($d->catatan && preg_match('/Terlambat (-?\d+) menit/i', $d->catatan, $matches)) {
                    $mins = abs((int) $matches[1]);
                    if ($mins > $toleransiTelat) {
                        $lateMinutes += $mins;
                    }
                }
            }

            if ($d->shift_id && $d->absen_keluar_at) {
                $shift = DB::table('sdm_jadwal_shift')->where('id', $d->shift_id)->first();
                if ($shift) {
                    $jamKeluar = $shift->jam_keluar;
                    $tglKeluar = Carbon::parse($d->tanggal);
                    if ($shift->lintas_hari) {
                        $tglKeluar->addDay();
                    }
                    try {
                        $scheduledOut = Carbon::parse($tglKeluar->format('Y-m-d') . ' ' . $jamKeluar);
                        $actualOut = Carbon::parse($d->absen_keluar_at);

                        if ($actualOut->greaterThan($scheduledOut)) {
                            $overtimeMinutes += abs($actualOut->diffInMinutes($scheduledOut));
                        }
                    } catch (\Exception $e) {
                        // ignore invalid parsed dates
                    }
                }
            }
        }

        return [
            'late_minutes' => $lateMinutes,
            'late_count' => $lateCount,
            'overtime_minutes' => $overtimeMinutes,
        ];
    }

    public function getDecemberPriorYtd(int $karyawanId, string $periode): array
    {
        $priorBruto = 0.0;
        $priorPph21 = 0.0;
        $priorBpjsTk = 0.0;

        if (str_ends_with($periode, '-12')) {
            $year = (int) substr($periode, 0, 4);
            $priorSlips = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawanId)
                ->where('periode', 'like', "$year-%")
                ->where('periode', '!=', $periode)
                ->get();

            foreach ($priorSlips as $ps) {
                $priorBruto += (double) $ps->gaji_pokok + (double) $ps->tunjangan_tetap + (double) $ps->tunjangan_absensi 
                    + (double) $ps->tunjangan_jabatan + (double) $ps->tunjangan_shift + (double) $ps->tunjangan_radiologi 
                    + (double) $ps->tunjangan_lain + (double) $ps->uang_lembur + (double) $ps->tunjangan_hari_raya;
                $priorPph21 += (double) $ps->potongan_pph21;
                $priorBpjsTk += (double) $ps->potongan_bpjs_tk;
            }
        }

        return [
            'prior_bruto' => $priorBruto,
            'prior_pph21' => $priorPph21,
            'prior_bpjs_tk' => $priorBpjsTk,
        ];
    }

    public function recalculateFormData(
        array $formData,
        array $umkAllocations,
        array $tunjanganLainItems,
        ?Karyawan $karyawan,
        string $periode,
        bool $isDecember,
        float $ytdPriorBruto,
        float $ytdPriorBpjsTk,
        float $ytdPriorPph21
    ): array {
        $tTetap = 0.0;
        $tAbsen = 0.0;
        foreach ($umkAllocations as $alloc) {
            if ($alloc['is_absensi']) {
                $tAbsen += (double) $alloc['nominal'];
            } else {
                $tTetap += (double) $alloc['nominal'];
            }
        }
        
        $baseCalculator = $karyawan ? PayrollCalculator::calculate($karyawan) : ['tunjangan_golongan_value' => 0, 'golongan' => null, 'masa_kerja_tahun' => 0];
        $formTunjanganTetap = $tTetap + $baseCalculator['tunjangan_golongan_value'];
        $formTunjanganAbsensi = $tAbsen;
        $calcGolongan = $baseCalculator['golongan'];
        $calcMasaKerja = $baseCalculator['masa_kerja_tahun'];

        $sumTunjanganLain = 0.0;
        foreach ($tunjanganLainItems as $item) {
            $sumTunjanganLain += (double) $item['nominal'];
        }
        $formTunjanganLain = $sumTunjanganLain;

        $totalEarnings = (double) ($formData['gaji_pokok'] ?? 0) +
            (double) $formTunjanganTetap +
            (double) $formTunjanganAbsensi +
            (double) ($formData['tunjangan_jabatan'] ?? 0) +
            (double) ($formData['tunjangan_shift'] ?? 0) +
            (double) ($formData['tunjangan_radiologi'] ?? 0) +
            (double) $formTunjanganLain +
            (double) ($formData['uang_lembur'] ?? 0) +
            (double) ($formData['tunjangan_hari_raya'] ?? 0);

        $calcBpjsKes = (double) ($formData['potongan_bpjs_kes'] ?? 0);
        $calcBpjsTk = (double) ($formData['potongan_bpjs_tk'] ?? 0);

        $calculatedDeductions = PayrollCalculator::calculateDeductions(
            (double) ($formData['gaji_pokok'] ?? 0),
            (double) $formTunjanganTetap,
            (double) $totalEarnings,
            (int) ($formData['bpjs_keluarga_tambahan'] ?? 0),
            $karyawan,
            $periode
        );

        $pph21Calculated = (double) $calculatedDeductions['potongan_pph21'];

        if (!empty($formData['pph21_is_overridden'])) {
            $calcPph21 = (double) ($formData['potongan_pph21'] ?? 0);
        } else {
            $calcPph21 = $pph21Calculated;
        }

        $calcTotalPotongan = (double) ($formData['potongan_absensi'] ?? 0) +
            (double) ($formData['potongan_cash_bon'] ?? 0) +
            (double) ($formData['potongan_obat'] ?? 0) +
            (double) ($formData['potongan_lain'] ?? 0) +
            (double) $calcBpjsKes +
            (double) $calcBpjsTk;

        $calcTotalGaji = $totalEarnings;

        $ytdFields = [
            'ytd_total_bruto' => 0.0,
            'ytd_total_bpjs_tk' => 0.0,
            'ytd_biaya_jabatan' => 0.0,
            'ytd_neto' => 0.0,
            'ytd_ptkp' => 0.0,
            'ytd_pkp' => 0.0,
            'ytd_tax_annual' => 0.0,
            'ytd_paid_jan_nov' => 0.0,
        ];

        if ($isDecember && $karyawan) {
            $currentBruto = $totalEarnings;
            $currentBpjsTk = $calcBpjsTk;
            
            $ytdTotalBruto = $ytdPriorBruto + $currentBruto;
            $ytdTotalBpjsTk = $ytdPriorBpjsTk + $currentBpjsTk;
            $ytdBiayaJabatan = min(0.05 * $ytdTotalBruto, 6000000.00);
            $ytdNeto = $ytdTotalBruto - $ytdBiayaJabatan - $ytdTotalBpjsTk;
            
            $year = (int) substr($periode, 0, 4);
            $ptkpStatus = $karyawan->ptkp_status ?: 'TK0';
            $ytdPtkp = DB::table('sdm_payroll_ptkp')
                ->where('status', $ptkpStatus)
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('berlaku_mulai_tahun', 'desc')
                ->value('nominal_setahun') ?: 54000000.00;
                
            $ytdPkp = max(0, $ytdNeto - $ytdPtkp);
            $ytdPkp = floor($ytdPkp / 1000) * 1000;
            
            $pasal17Brackets = DB::table('sdm_payroll_pasal17')
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('pkp_bawah', 'asc')
                ->get();

            $pajakSetahun = 0;
            $remainingPkp = $ytdPkp;

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

            $ytdFields = [
                'ytd_total_bruto' => $ytdTotalBruto,
                'ytd_total_bpjs_tk' => $ytdTotalBpjsTk,
                'ytd_biaya_jabatan' => $ytdBiayaJabatan,
                'ytd_neto' => $ytdNeto,
                'ytd_ptkp' => $ytdPtkp,
                'ytd_pkp' => $ytdPkp,
                'ytd_tax_annual' => $pajakSetahun,
                'ytd_paid_jan_nov' => $ytdPriorPph21,
            ];
        }

        $calcGajiBersih = $calcTotalGaji - $calcTotalPotongan - (double) $calcPph21 - (double) ($formData['potongan_bank'] ?? 0);

        return array_merge([
            'tunjangan_tetap' => $formTunjanganTetap,
            'tunjangan_absensi' => $formTunjanganAbsensi,
            'tunjangan_lain' => $formTunjanganLain,
            'calc_golongan' => $calcGolongan,
            'calc_masa_kerja' => $calcMasaKerja,
            'calc_total_gaji' => $calcTotalGaji,
            'calc_bpjs_kes' => $calcBpjsKes,
            'calc_bpjs_tk' => $calcBpjsTk,
            'pph21_calculated' => $pph21Calculated,
            'calc_pph21' => $calcPph21,
            'calc_total_potongan' => $calcTotalPotongan,
            'calc_gaji_bersih' => $calcGajiBersih,
        ], $ytdFields);
    }

    public function saveSlip(
        int $karyawanId,
        string $periode,
        array $formData,
        array $umkAllocations,
        array $tunjanganLainItems,
        ?string $calcGolongan,
        float $calcMasaKerja,
        ?int $userId = null
    ): void {
        $oldSlip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();

        DB::beginTransaction();
        try {
            $totalGaji = (double) $formData['gaji_pokok'] +
                (double) $formData['tunjangan_tetap'] +
                (double) $formData['tunjangan_absensi'] +
                (double) $formData['tunjangan_jabatan'] +
                (double) $formData['tunjangan_shift'] +
                (double) $formData['tunjangan_radiologi'] +
                (double) $formData['tunjangan_lain'] +
                (double) $formData['uang_lembur'] +
                (double) $formData['tunjangan_hari_raya'];

            $totalPotongan = (double) $formData['potongan_absensi'] +
                (double) $formData['potongan_cash_bon'] +
                (double) $formData['potongan_obat'] +
                (double) $formData['potongan_lain'] +
                (double) $formData['potongan_bpjs_kes'] +
                (double) $formData['potongan_bpjs_tk'];

            $gajiBersih = $totalGaji - $totalPotongan - (double) $formData['potongan_pph21'] - (double) $formData['potongan_bank'];

            DB::table('sdm_payroll_slips')->updateOrInsert(
                [
                    'karyawan_id' => $karyawanId,
                    'periode' => $periode
                ],
                [
                    'golongan' => $calcGolongan,
                    'masa_kerja_tahun' => $calcMasaKerja,
                    'gaji_pokok' => $formData['gaji_pokok'],
                    'tunjangan_tetap' => $formData['tunjangan_tetap'],
                    'tunjangan_absensi' => $formData['tunjangan_absensi'],
                    'tunjangan_jabatan' => $formData['tunjangan_jabatan'],
                    'tunjangan_shift' => $formData['tunjangan_shift'],
                    'tunjangan_radiologi' => $formData['tunjangan_radiologi'],
                    'tunjangan_lain' => $formData['tunjangan_lain'],
                    'uang_lembur' => $formData['uang_lembur'],
                    'tunjangan_hari_raya' => $formData['tunjangan_hari_raya'],
                    'potongan_absensi' => $formData['potongan_absensi'],
                    'potongan_cash_bon' => $formData['potongan_cash_bon'],
                    'potongan_obat' => $formData['potongan_obat'],
                    'potongan_lain' => $formData['potongan_lain'],
                    'potongan_bank' => $formData['potongan_bank'],
                    'potongan_bpjs_kes' => $formData['potongan_bpjs_kes'],
                    'potongan_bpjs_tk' => $formData['potongan_bpjs_tk'],
                    'bpjs_keluarga_tambahan' => $formData['bpjs_keluarga_tambahan'],
                    'potongan_pph21' => $formData['potongan_pph21'],
                    'pph21_calculated' => $formData['pph21_calculated'] ?? $formData['potongan_pph21'],
                    'pph21_is_overridden' => $formData['pph21_is_overridden'] ?? false,
                    'pph21_override_reason' => $formData['pph21_override_reason'] ?? null,
                    'total_gaji' => $totalGaji,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'created_by' => $userId ?: auth()->id(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $slip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawanId)
                ->where('periode', $periode)
                ->first();

            DB::table('sdm_payroll_slip_allocations')->where('payroll_slip_id', $slip->id)->delete();
            foreach ($umkAllocations as $alloc) {
                if (!empty($alloc['allowance_allocation_id'])) {
                    DB::table('sdm_payroll_slip_allocations')->insert([
                        'payroll_slip_id' => $slip->id,
                        'allowance_allocation_id' => $alloc['allowance_allocation_id'],
                        'nominal' => $alloc['nominal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('sdm_payroll_slip_allowances')->where('payroll_slip_id', $slip->id)->delete();
            foreach ($tunjanganLainItems as $item) {
                DB::table('sdm_payroll_slip_allowances')->insert([
                    'payroll_slip_id' => $slip->id,
                    'allowance_type_id' => $item['allowance_type_id'],
                    'nominal' => $item['nominal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($oldSlip) {
                $changedFields = [];
                $fieldsToCheck = [
                    'gaji_pokok', 'tunjangan_tetap', 'tunjangan_absensi', 'tunjangan_jabatan',
                    'tunjangan_shift', 'tunjangan_radiologi', 'tunjangan_lain', 'uang_lembur',
                    'tunjangan_hari_raya', 'potongan_absensi', 'potongan_cash_bon', 'potongan_obat',
                    'potongan_lain', 'potongan_bank', 'potongan_bpjs_kes', 'potongan_bpjs_tk',
                    'potongan_pph21'
                ];

                foreach ($fieldsToCheck as $f) {
                    $newVal = (double) ($formData[$f] ?? 0);
                    $oldVal = (double) $oldSlip->{$f};
                    if ($oldVal != $newVal) {
                        $changedFields[$f] = [
                            'before' => $oldVal,
                            'after' => $newVal,
                        ];
                    }
                }

                app(PayrollAuditLogService::class)->logEdit($slip->id, $karyawanId, $periode, $changedFields, $userId);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSlipViewData(int $karyawanId, string $periode): ?array
    {
        $karyawan = Karyawan::with(['jabatan.bagian'])->find($karyawanId);
        if (!$karyawan) return null;

        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $periode)
            ->first();

        $latestJab = $karyawan->jabatan->first();
        $jabName = $latestJab ? $latestJab->nama : '-';
        $bagName = $latestJab && $latestJab->bagian ? $latestJab->bagian->nama : '-';

        if ($slip) {
            $breakdown = DB::table('sdm_payroll_slip_allowances')
                ->join('sdm_payroll_allowance_types', 'sdm_payroll_slip_allowances.allowance_type_id', '=', 'sdm_payroll_allowance_types.id')
                ->where('sdm_payroll_slip_allowances.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_allowance_types.nama', 'sdm_payroll_slip_allowances.nominal')
                ->get()
                ->toArray();

            $allocsList = DB::table('sdm_payroll_slip_allocations')
                ->join('sdm_payroll_allowance_allocations', 'sdm_payroll_slip_allocations.allowance_allocation_id', '=', 'sdm_payroll_allowance_allocations.id')
                ->where('sdm_payroll_slip_allocations.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_allowance_allocations.nama', 'sdm_payroll_allowance_allocations.is_absensi', 'sdm_payroll_slip_allocations.nominal')
                ->get();

            $editLogs = app(PayrollAuditLogService::class)->getSlipLogs($slip->id);

            return [
                'id' => $karyawan->id,
                'nama' => $karyawan->full_nama,
                'nip' => $karyawan->nip,
                'status' => $karyawan->status->nama(),
                'jabatan' => $jabName,
                'bagian' => $bagName,
                'nama_bank' => $karyawan->nama_bank,
                'no_rekening' => $karyawan->no_rekening,
                'gaji_pokok' => $slip->gaji_pokok,
                'tunjangan_tetap' => $slip->tunjangan_tetap,
                'tunjangan_absensi' => $slip->tunjangan_absensi,
                'tunjangan_jabatan' => $slip->tunjangan_jabatan,
                'tunjangan_shift' => $slip->tunjangan_shift,
                'tunjangan_radiologi' => $slip->tunjangan_radiologi,
                'tunjangan_lain' => $slip->tunjangan_lain,
                'tunjangan_lain_items' => $breakdown,
                'allocations_list' => $allocsList,
                'uang_lembur' => $slip->uang_lembur,
                'tunjangan_hari_raya' => $slip->tunjangan_hari_raya,
                'potongan_absensi' => $slip->potongan_absensi,
                'potongan_cash_bon' => $slip->potongan_cash_bon,
                'potongan_obat' => $slip->potongan_obat,
                'potongan_bpjs_kes' => $slip->potongan_bpjs_kes,
                'bpjs_kes' => $slip->potongan_bpjs_kes,
                'potongan_bpjs_tk' => $slip->potongan_bpjs_tk,
                'bpjs_ket' => $slip->potongan_bpjs_tk,
                'potongan_lain' => $slip->potongan_lain,
                'potongan_pph21' => $slip->potongan_pph21,
                'pajak' => $slip->potongan_pph21,
                'potongan_bank' => $slip->potongan_bank,
                'gaji_bersih' => $slip->gaji_bersih,
                'total_gaji' => $slip->total_gaji,
                'total_potongan' => $slip->total_potongan,
                'periode' => Carbon::parse($periode . '-01')->translatedFormat('F Y'),
                'edit_logs' => $editLogs,
            ];
        }

        $base = PayrollCalculator::calculate($karyawan);
        $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
        $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $periode);
        $totalPotongan = $deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk'];
        $gajiBersih = $totalPendapatan - $totalPotongan - $deductions['potongan_pph21'];

        return [
            'id' => $karyawan->id,
            'nama' => $karyawan->full_nama,
            'nip' => $karyawan->nip,
            'status' => $karyawan->status->nama(),
            'jabatan' => $jabName,
            'bagian' => $bagName,
            'nama_bank' => $karyawan->nama_bank,
            'no_rekening' => $karyawan->no_rekening,
            'gaji_pokok' => $base['gaji_pokok'],
            'tunjangan_tetap' => $base['tunjangan_tetap'],
            'tunjangan_absensi' => $base['tunjangan_absensi'],
            'tunjangan_jabatan' => $base['tunjangan_jabatan'],
            'tunjangan_shift' => 0.0,
            'tunjangan_radiologi' => 0.0,
            'tunjangan_lain' => 0.0,
            'tunjangan_lain_items' => [],
            'allocations_list' => $base['allocations_breakdown'],
            'uang_lembur' => 0.0,
            'tunjangan_hari_raya' => 0.0,
            'potongan_absensi' => 0.0,
            'potongan_cash_bon' => 0.0,
            'potongan_obat' => 0.0,
            'potongan_bpjs_kes' => $deductions['potongan_bpjs_kes'],
            'bpjs_kes' => $deductions['potongan_bpjs_kes'],
            'potongan_bpjs_tk' => $deductions['potongan_bpjs_tk'],
            'bpjs_ket' => $deductions['potongan_bpjs_tk'],
            'potongan_lain' => 0.0,
            'potongan_pph21' => $deductions['potongan_pph21'],
            'pajak' => $deductions['potongan_pph21'],
            'potongan_bank' => 0.0,
            'gaji_bersih' => $gajiBersih,
            'total_gaji' => $totalPendapatan,
            'total_potongan' => $totalPotongan,
            'periode' => Carbon::parse($periode . '-01')->translatedFormat('F Y') . ' (DRAFT)',
            'edit_logs' => [],
        ];
    }

    public function getPeriodEditLogs(string $periode, string $search = ''): array
    {
        return app(PayrollAuditLogService::class)->getPeriodEditLogs($periode, $search);
    }

    public function generateBulkDraftSlips(string $periode, ?int $userId = null): int
    {
        $lockStatus = app(PayrollPeriodService::class)->getLockStatus($periode);
        if ($lockStatus['is_locked']) {
            throw new \Exception('Periode ini telah dikunci dan tidak dapat diubah.');
        }

        $employees = Karyawan::whereNull('resign_at')->get();
        $previousPeriode = Carbon::parse($periode . '-01')->subMonth()->format('Y-m');
        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $karyawan) {
                $existing = DB::table('sdm_payroll_slips')
                    ->where('karyawan_id', $karyawan->id)
                    ->where('periode', $periode)
                    ->first();

                if ($existing) {
                    continue;
                }

                $prevSlip = DB::table('sdm_payroll_slips')
                    ->where('karyawan_id', $karyawan->id)
                    ->where('periode', $previousPeriode)
                    ->first();

                if ($prevSlip) {
                    $gajiPokok = (double) $prevSlip->gaji_pokok;
                    $tunjanganTetap = (double) $prevSlip->tunjangan_tetap;
                    $tunjanganAbsensi = (double) $prevSlip->tunjangan_absensi;
                    $tunjanganJabatan = (double) $prevSlip->tunjangan_jabatan;
                    $tunjanganShift = (double) $prevSlip->tunjangan_shift;
                    $tunjanganRadiologi = (double) $prevSlip->tunjangan_radiologi;
                    $tunjanganLain = (double) $prevSlip->tunjangan_lain;
                    $uangLembur = 0.0;
                    $thr = 0.0;
                    $bpjsKeluargaTambahan = (int) $prevSlip->bpjs_keluarga_tambahan;
                    $golongan = $prevSlip->golongan;
                    $masaKerja = (double) $prevSlip->masa_kerja_tahun;
                } else {
                    $base = PayrollCalculator::calculate($karyawan);
                    $gajiPokok = (double) $base['gaji_pokok'];
                    $tunjanganTetap = (double) $base['tunjangan_tetap'];
                    $tunjanganAbsensi = (double) $base['tunjangan_absensi'];
                    $tunjanganJabatan = (double) $base['tunjangan_jabatan'];
                    $tunjanganShift = 0.0;
                    $tunjanganRadiologi = 0.0;
                    $tunjanganLain = 0.0;
                    $uangLembur = 0.0;
                    $thr = 0.0;
                    $bpjsKeluargaTambahan = 0;
                    $golongan = $base['golongan'];
                    $masaKerja = (double) $base['masa_kerja_tahun'];
                }

                $totalBruto = $gajiPokok + $tunjanganTetap + $tunjanganAbsensi + $tunjanganJabatan + $tunjanganShift + $tunjanganRadiologi + $tunjanganLain + $uangLembur + $thr;
                $deductions = PayrollCalculator::calculateDeductions($gajiPokok, $tunjanganTetap, $totalBruto, $bpjsKeluargaTambahan, $karyawan, $periode);

                $totalPotongan = $deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk'];
                $pph21 = (double) $deductions['potongan_pph21'];
                $gajiBersih = $totalBruto - $totalPotongan - $pph21;

                $slipId = DB::table('sdm_payroll_slips')->insertGetId([
                    'karyawan_id' => $karyawan->id,
                    'periode' => $periode,
                    'golongan' => $golongan,
                    'masa_kerja_tahun' => $masaKerja,
                    'gaji_pokok' => $gajiPokok,
                    'tunjangan_tetap' => $tunjanganTetap,
                    'tunjangan_absensi' => $tunjanganAbsensi,
                    'tunjangan_jabatan' => $tunjanganJabatan,
                    'tunjangan_shift' => $tunjanganShift,
                    'tunjangan_radiologi' => $tunjanganRadiologi,
                    'tunjangan_lain' => $tunjanganLain,
                    'uang_lembur' => $uangLembur,
                    'tunjangan_hari_raya' => $thr,
                    'potongan_absensi' => 0.0,
                    'potongan_cash_bon' => 0.0,
                    'potongan_obat' => 0.0,
                    'potongan_lain' => 0.0,
                    'potongan_bank' => 0.0,
                    'potongan_bpjs_kes' => $deductions['potongan_bpjs_kes'],
                    'potongan_bpjs_tk' => $deductions['potongan_bpjs_tk'],
                    'bpjs_keluarga_tambahan' => $bpjsKeluargaTambahan,
                    'potongan_pph21' => $pph21,
                    'pph21_calculated' => $pph21,
                    'pph21_is_overridden' => false,
                    'total_gaji' => $totalBruto,
                    'total_potongan' => $totalPotongan,
                    'gaji_bersih' => $gajiBersih,
                    'created_by' => $userId ?: auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($prevSlip) {
                    $prevAllocs = DB::table('sdm_payroll_slip_allocations')
                        ->where('payroll_slip_id', $prevSlip->id)
                        ->get();
                    foreach ($prevAllocs as $pa) {
                        DB::table('sdm_payroll_slip_allocations')->insert([
                            'payroll_slip_id' => $slipId,
                            'allowance_allocation_id' => $pa->allowance_allocation_id,
                            'nominal' => $pa->nominal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $prevAllows = DB::table('sdm_payroll_slip_allowances')
                        ->where('payroll_slip_id', $prevSlip->id)
                        ->get();
                    foreach ($prevAllows as $pal) {
                        DB::table('sdm_payroll_slip_allowances')->insert([
                            'payroll_slip_id' => $slipId,
                            'allowance_type_id' => $pal->allowance_type_id,
                            'nominal' => $pal->nominal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $createdCount++;
            }

            DB::commit();
            return $createdCount;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
