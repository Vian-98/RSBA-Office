<?php

namespace App\Livewire\Gaji\Concerns;

use App\Models\Sdm\Karyawan;
use App\Services\PayrollService;
use App\Services\PayrollCalculator;
use Illuminate\Support\Facades\DB;

trait HasPayrollInputForm
{
    public bool $isInputModalOpen = false;
    public ?int $selectedKaryawanId = null;
    public ?Karyawan $selectedKaryawan = null;

    // Form fields
    public $form_gaji_pokok = 0;
    public $form_tunjangan_tetap = 0;
    public $form_tunjangan_absensi = 0;
    public $form_tunjangan_jabatan = 0;
    public $form_tunjangan_shift = 0;
    public $form_tunjangan_radiologi = 0;
    public $form_tunjangan_lain = 0;
    public $form_uang_lembur = 0;
    public $form_tunjangan_hari_raya = 0;
    
    public $form_potongan_absensi = 0;
    public $form_potongan_cash_bon = 0;
    public $form_potongan_obat = 0;
    public $form_potongan_lain = 0;
    public $form_potongan_bank = 0;
    public $form_potongan_bpjs_kes = 0;
    public $form_potongan_bpjs_tk = 0;
    public int $form_bpjs_keluarga_tambahan = 0;

    // PPh 21 Override Form Fields
    public $form_potongan_pph21 = 0;
    public $form_pph21_calculated = 0;
    public bool $form_pph21_is_overridden = false;
    public string $form_pph21_override_reason = '';

    // December YTD reconciliation fields
    public bool $is_december = false;
    public $ytd_prior_bruto = 0.0;
    public $ytd_prior_pph21 = 0.0;
    public $ytd_prior_bpjs_tk = 0.0;
    public $ytd_total_bruto = 0.0;
    public $ytd_total_bpjs_tk = 0.0;
    public $ytd_biaya_jabatan = 0.0;
    public $ytd_neto = 0.0;
    public $ytd_ptkp = 0.0;
    public $ytd_pkp = 0.0;
    public $ytd_tax_annual = 0.0;
    public $ytd_paid_jan_nov = 0.0;

    public array $form_umk_allocations = [];
    public array $form_tunjangan_lain_items = [];
    
    public ?int $temp_allowance_type_id = null;
    public $temp_allowance_nominal = 0;

    // Calculated fields
    public $calc_bpjs_kes = 0;
    public $calc_bpjs_tk = 0;
    public $calc_pph21 = 0;
    public $calc_total_gaji = 0;
    public $calc_total_potongan = 0;
    public $calc_gaji_bersih = 0;
    public $calc_golongan = null;
    public $calc_masa_kerja = 0.0;

    public function updatedHasPayrollInputForm($name)
    {
        if (str_starts_with($name, 'form_')) {
            $value = $this->{$name};
            if (is_string($value)) {
                $cleaned = str_replace('.', '', $value);
                $this->{$name} = is_numeric($cleaned) ? (double) $cleaned : 0;
            }

            if ($name === 'form_potongan_pph21') {
                if ((double) $this->form_potongan_pph21 !== (double) $this->form_pph21_calculated) {
                    $this->form_pph21_is_overridden = true;
                } else {
                    $this->form_pph21_is_overridden = false;
                    $this->form_pph21_override_reason = '';
                }
            }
            $this->recalculate();
        }
    }

    public function resetPph21ToAuto()
    {
        $this->form_pph21_is_overridden = false;
        $this->form_pph21_override_reason = '';
        $this->form_potongan_pph21 = $this->form_pph21_calculated;
        $this->recalculate();
        $this->toast()->info('Info', 'Nilai PPh 21 dikembalikan ke otomatis (sistem).')->send();
    }

    public function addTunjanganLain()
    {
        if ($this->isLocked) {
            $this->toast()->error('Gagal !', 'Periode ini telah disetujui dan terkunci. Data tidak dapat diubah.')->send();
            return;
        }

        $this->validate([
            'temp_allowance_type_id' => 'required|exists:sdm_payroll_allowance_types,id',
            'temp_allowance_nominal' => 'required|numeric|min:1',
        ], [
            'temp_allowance_type_id.required' => 'Jenis tunjangan harus dipilih.',
            'temp_allowance_nominal.required' => 'Nominal harus diisi.',
            'temp_allowance_nominal.min' => 'Nominal harus lebih dari 0.',
        ]);

        foreach ($this->form_tunjangan_lain_items as $item) {
            if ($item['allowance_type_id'] === (int) $this->temp_allowance_type_id) {
                $this->toast()->error('Gagal !', 'Jenis tunjangan ini sudah ditambahkan. Silakan edit atau hapus item yang ada.')->send();
                return;
            }
        }

        $typeName = DB::table('sdm_payroll_allowance_types')
            ->where('id', $this->temp_allowance_type_id)
            ->value('nama');

        $this->form_tunjangan_lain_items[] = [
            'allowance_type_id' => (int) $this->temp_allowance_type_id,
            'nama' => $typeName,
            'nominal' => (double) $this->temp_allowance_nominal,
        ];

        $this->temp_allowance_type_id = null;
        $this->temp_allowance_nominal = 0;
        $this->recalculate();
        $this->toast()->success('Berhasil !', 'Tunjangan lain-lain berhasil ditambahkan ke daftar.')->send();
    }

    public function removeTunjanganLain(int $index)
    {
        if ($this->isLocked) {
            $this->toast()->error('Gagal !', 'Periode ini telah disetujui dan terkunci. Data tidak dapat diubah.')->send();
            return;
        }

        if (isset($this->form_tunjangan_lain_items[$index])) {
            unset($this->form_tunjangan_lain_items[$index]);
            $this->form_tunjangan_lain_items = array_values($this->form_tunjangan_lain_items);
            $this->recalculate();
            $this->toast()->success('Berhasil !', 'Tunjangan lain-lain dihapus dari daftar.')->send();
        }
    }

    protected function recalculate()
    {
        $res = app(PayrollService::class)->recalculateFormData(
            [
                'gaji_pokok' => $this->form_gaji_pokok,
                'tunjangan_jabatan' => $this->form_tunjangan_jabatan,
                'tunjangan_shift' => $this->form_tunjangan_shift,
                'tunjangan_radiologi' => $this->form_tunjangan_radiologi,
                'uang_lembur' => $this->form_uang_lembur,
                'tunjangan_hari_raya' => $this->form_tunjangan_hari_raya,
                'potongan_absensi' => $this->form_potongan_absensi,
                'potongan_cash_bon' => $this->form_potongan_cash_bon,
                'potongan_obat' => $this->form_potongan_obat,
                'potongan_lain' => $this->form_potongan_lain,
                'potongan_bpjs_kes' => $this->form_potongan_bpjs_kes,
                'potongan_bpjs_tk' => $this->form_potongan_bpjs_tk,
                'potongan_bank' => $this->form_potongan_bank,
                'bpjs_keluarga_tambahan' => $this->form_bpjs_keluarga_tambahan,
                'potongan_pph21' => $this->form_potongan_pph21,
                'pph21_is_overridden' => $this->form_pph21_is_overridden,
            ],
            $this->form_umk_allocations,
            $this->form_tunjangan_lain_items,
            $this->selectedKaryawan,
            $this->periode,
            $this->is_december,
            (float) $this->ytd_prior_bruto,
            (float) $this->ytd_prior_bpjs_tk,
            (float) $this->ytd_prior_pph21
        );

        $this->form_tunjangan_tetap = $res['tunjangan_tetap'];
        $this->form_tunjangan_absensi = $res['tunjangan_absensi'];
        $this->form_tunjangan_lain = $res['tunjangan_lain'];
        $this->calc_golongan = $res['calc_golongan'];
        $this->calc_masa_kerja = $res['calc_masa_kerja'];
        $this->calc_total_gaji = $res['calc_total_gaji'];
        $this->calc_bpjs_kes = $res['calc_bpjs_kes'];
        $this->calc_bpjs_tk = $res['calc_bpjs_tk'];
        $this->form_pph21_calculated = $res['pph21_calculated'];
        
        if ($this->form_pph21_is_overridden) {
            $this->calc_pph21 = (double) $this->form_potongan_pph21;
        } else {
            $this->calc_pph21 = $this->form_pph21_calculated;
            $this->form_potongan_pph21 = $this->calc_pph21;
        }

        $this->calc_total_potongan = $res['calc_total_potongan'];
        $this->calc_gaji_bersih = $res['calc_gaji_bersih'];

        if ($this->is_december) {
            $this->ytd_total_bruto = $res['ytd_total_bruto'];
            $this->ytd_total_bpjs_tk = $res['ytd_total_bpjs_tk'];
            $this->ytd_biaya_jabatan = $res['ytd_biaya_jabatan'];
            $this->ytd_neto = $res['ytd_neto'];
            $this->ytd_ptkp = $res['ytd_ptkp'];
            $this->ytd_pkp = $res['ytd_pkp'];
            $this->ytd_tax_annual = $res['ytd_tax_annual'];
            $this->ytd_paid_jan_nov = $res['ytd_paid_jan_nov'];
        }
    }

    public function openInputModal(int $karyawanId, PayrollService $payrollService)
    {
        $karyawan = Karyawan::with(['jabatan.bagian'])->findOrFail($karyawanId);
        $this->selectedKaryawanId = $karyawanId;
        $this->selectedKaryawan = $karyawan;

        $this->form_tunjangan_lain_items = [];
        $this->form_umk_allocations = [];
        $this->temp_allowance_type_id = null;
        $this->temp_allowance_nominal = 0;

        $stats = $payrollService->calculateAttendanceStats($karyawanId, $this->periode);
        $this->calculatedLateMinutes = $stats['late_minutes'];
        $this->calculatedLateCount = $stats['late_count'];
        $this->calculatedOvertimeMinutes = $stats['overtime_minutes'];

        $rateLateDeduction = (double) (DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_kejadian')->value('value')
            ?: DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_menit')->value('value')
            ?: 50000);

        $autoPotonganAbsensi = (int) ($this->calculatedLateCount * $rateLateDeduction);

        $this->is_december = str_ends_with($this->periode, '-12');
        $priorYtd = $payrollService->getDecemberPriorYtd($karyawanId, $this->periode);
        $this->ytd_prior_bruto = $priorYtd['prior_bruto'];
        $this->ytd_prior_pph21 = $priorYtd['prior_pph21'];
        $this->ytd_prior_bpjs_tk = $priorYtd['prior_bpjs_tk'];

        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $this->periode)
            ->first();

        if ($slip) {
            $this->carriedOverFromPeriode = null;

            $this->form_gaji_pokok = (int) $slip->gaji_pokok;
            $this->form_tunjangan_tetap = (int) $slip->tunjangan_tetap;
            $this->form_tunjangan_absensi = (int) $slip->tunjangan_absensi;
            $this->form_tunjangan_jabatan = (int) $slip->tunjangan_jabatan;
            $this->form_tunjangan_shift = (int) $slip->tunjangan_shift;
            $this->form_tunjangan_radiologi = (int) $slip->tunjangan_radiologi;
            $this->form_tunjangan_lain = (int) $slip->tunjangan_lain;
            $this->form_uang_lembur = (int) $slip->uang_lembur;
            $this->form_tunjangan_hari_raya = (int) $slip->tunjangan_hari_raya;
            
            $this->form_potongan_absensi = (int) $slip->potongan_absensi;
            $this->form_potongan_cash_bon = (int) $slip->potongan_cash_bon;
            $this->form_potongan_obat = (int) $slip->potongan_obat;
            $this->form_potongan_lain = (int) $slip->potongan_lain;
            $this->form_potongan_bank = (int) $slip->potongan_bank;
            $this->form_potongan_bpjs_kes = (int) $slip->potongan_bpjs_kes;
            $this->form_potongan_bpjs_tk = (int) $slip->potongan_bpjs_tk;
            $this->form_potongan_pph21 = (int) $slip->potongan_pph21;
            $this->form_pph21_calculated = (int) ($slip->pph21_calculated ?? $slip->potongan_pph21);
            $this->form_pph21_is_overridden = (bool) ($slip->pph21_is_overridden ?? false);
            $this->form_pph21_override_reason = $slip->pph21_override_reason ?? '';
            $this->form_bpjs_keluarga_tambahan = (int) $slip->bpjs_keluarga_tambahan;

            $dbSlipAllocs = DB::table('sdm_payroll_slip_allocations')
                ->join('sdm_payroll_allowance_allocations', 'sdm_payroll_slip_allocations.allowance_allocation_id', '=', 'sdm_payroll_allowance_allocations.id')
                ->where('sdm_payroll_slip_allocations.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_slip_allocations.allowance_allocation_id', 'sdm_payroll_allowance_allocations.nama', 'sdm_payroll_allowance_allocations.persen', 'sdm_payroll_allowance_allocations.is_absensi', 'sdm_payroll_slip_allocations.nominal')
                ->get();

            foreach ($dbSlipAllocs as $item) {
                $this->form_umk_allocations[] = [
                    'allowance_allocation_id' => $item->allowance_allocation_id,
                    'nama' => $item->nama,
                    'persen' => (double) $item->persen,
                    'is_absensi' => (bool) $item->is_absensi,
                    'nominal' => (double) $item->nominal,
                ];
            }

            if (empty($this->form_umk_allocations)) {
                $base = PayrollCalculator::calculate($karyawan);
                $this->form_umk_allocations = $base['allocations_breakdown'];
            }

            $dbItems = DB::table('sdm_payroll_slip_allowances')
                ->join('sdm_payroll_allowance_types', 'sdm_payroll_slip_allowances.allowance_type_id', '=', 'sdm_payroll_allowance_types.id')
                ->where('sdm_payroll_slip_allowances.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_slip_allowances.allowance_type_id', 'sdm_payroll_allowance_types.nama', 'sdm_payroll_slip_allowances.nominal')
                ->get();

            foreach ($dbItems as $dbItem) {
                $this->form_tunjangan_lain_items[] = [
                    'allowance_type_id' => $dbItem->allowance_type_id,
                    'nama' => $dbItem->nama,
                    'nominal' => (double) $dbItem->nominal,
                ];
            }
        } else {
            $base = PayrollCalculator::calculate($karyawan);
            $this->form_gaji_pokok = $base['gaji_pokok'];
            $this->form_tunjangan_jabatan = $base['tunjangan_jabatan'];
            $this->form_umk_allocations = $base['allocations_breakdown'];

            $tTetap = 0.0;
            foreach ($this->form_umk_allocations as $alloc) {
                if (!$alloc['is_absensi']) {
                    $tTetap += (double) $alloc['nominal'];
                }
            }
            $tTetap += (double) $base['tunjangan_golongan_value'];
            $totalEarnings = (double) $this->form_gaji_pokok + $tTetap + (double) $this->form_tunjangan_jabatan;

            $deductions = PayrollCalculator::calculateDeductions(
                $this->form_gaji_pokok,
                $tTetap,
                $totalEarnings,
                0,
                $karyawan,
                $this->periode
            );

            $lastSlip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawanId)
                ->where('periode', '<', $this->periode)
                ->orderBy('periode', 'desc')
                ->first();

            if ($lastSlip) {
                $this->carriedOverFromPeriode = $lastSlip->periode;

                $this->form_tunjangan_shift = (int) $lastSlip->tunjangan_shift;
                $this->form_tunjangan_radiologi = (int) $lastSlip->tunjangan_radiologi;
                $this->form_bpjs_keluarga_tambahan = (int) $lastSlip->bpjs_keluarga_tambahan;

                $this->form_potongan_absensi = $autoPotonganAbsensi;
                $this->form_potongan_cash_bon = 0;
                $this->form_potongan_obat = 0;
                $this->form_potongan_lain = 0;
                $this->form_potongan_bank = 0;
                $this->form_potongan_bpjs_kes = (int) $lastSlip->potongan_bpjs_kes;
                $this->form_potongan_bpjs_tk = (int) $lastSlip->potongan_bpjs_tk;
                $this->form_uang_lembur = 0;
                $this->form_tunjangan_hari_raya = 0;

                $dbItems = DB::table('sdm_payroll_slip_allowances')
                    ->join('sdm_payroll_allowance_types', 'sdm_payroll_slip_allowances.allowance_type_id', '=', 'sdm_payroll_allowance_types.id')
                    ->where('sdm_payroll_slip_allowances.payroll_slip_id', $lastSlip->id)
                    ->select('sdm_payroll_slip_allowances.allowance_type_id', 'sdm_payroll_allowance_types.nama', 'sdm_payroll_slip_allowances.nominal')
                    ->get();

                foreach ($dbItems as $dbItem) {
                    $this->form_tunjangan_lain_items[] = [
                        'allowance_type_id' => $dbItem->allowance_type_id,
                        'nama' => $dbItem->nama,
                        'nominal' => (double) $dbItem->nominal,
                    ];
                }
            } else {
                $this->carriedOverFromPeriode = null;
                $this->form_tunjangan_shift = 0;
                $this->form_tunjangan_radiologi = 0;
                $this->form_tunjangan_lain = 0;
                $this->form_uang_lembur = 0;
                $this->form_tunjangan_hari_raya = 0;
                
                $this->form_potongan_absensi = $autoPotonganAbsensi;
                $this->form_potongan_cash_bon = 0;
                $this->form_potongan_obat = 0;
                $this->form_potongan_lain = 0;
                $this->form_potongan_bank = 0;
                
                $this->form_bpjs_keluarga_tambahan = 0;
                $this->form_potongan_bpjs_kes = (int) $deductions['potongan_bpjs_kes'];
                $this->form_potongan_bpjs_tk = (int) $deductions['potongan_bpjs_tk'];
                $this->form_pph21_is_overridden = false;
                $this->form_pph21_override_reason = '';
                $this->form_pph21_calculated = (int) $deductions['potongan_pph21'];
                $this->form_potongan_pph21 = $this->form_pph21_calculated;
            }
        }

        $this->recalculate();
        $this->isInputModalOpen = true;
    }

    public function closeInputModal()
    {
        $this->isInputModalOpen = false;
        $this->selectedKaryawanId = null;
        $this->selectedKaryawan = null;
        $this->form_tunjangan_lain_items = [];
        $this->form_umk_allocations = [];
        $this->carriedOverFromPeriode = null;
    }

    public function savePayroll(PayrollService $payrollService)
    {
        if ($this->isLocked || DB::table('sdm_payroll_period_locks')->where('periode', $this->periode)->where('is_approved', true)->exists()) {
            $this->toast()->error('Gagal !', 'Periode ini telah disetujui dan terkunci. Data tidak dapat diubah.')->send();
            return;
        }

        $rules = [
            'form_gaji_pokok' => 'required|numeric|min:0',
            'form_tunjangan_tetap' => 'required|numeric|min:0',
            'form_tunjangan_absensi' => 'required|numeric|min:0',
            'form_tunjangan_jabatan' => 'required|numeric|min:0',
            'form_tunjangan_shift' => 'required|numeric|min:0',
            'form_tunjangan_radiologi' => 'required|numeric|min:0',
            'form_tunjangan_lain' => 'required|numeric|min:0',
            'form_uang_lembur' => 'required|numeric|min:0',
            'form_tunjangan_hari_raya' => 'required|numeric|min:0',
            'form_potongan_absensi' => 'required|numeric|min:0',
            'form_potongan_cash_bon' => 'required|numeric|min:0',
            'form_potongan_obat' => 'required|numeric|min:0',
            'form_potongan_lain' => 'required|numeric|min:0',
            'form_potongan_bank' => 'required|numeric|min:0',
            'form_potongan_bpjs_kes' => 'required|numeric|min:0',
            'form_potongan_bpjs_tk' => 'required|numeric|min:0',
            'form_bpjs_keluarga_tambahan' => 'required|integer|min:0',
            'form_potongan_pph21' => 'required|numeric|min:0',
        ];

        if ($this->form_pph21_is_overridden) {
            $rules['form_pph21_override_reason'] = 'required|string|min:5';
        }

        $this->validate($rules, [
            'form_pph21_override_reason.required' => 'Alasan perubahan PPh 21 wajib diisi jika nilai pajaknya diubah manual.',
            'form_pph21_override_reason.min' => 'Alasan perubahan minimal 5 karakter.',
        ]);

        $this->recalculate();

        try {
            $payrollService->saveSlip(
                $this->selectedKaryawanId,
                $this->periode,
                [
                    'gaji_pokok' => $this->form_gaji_pokok,
                    'tunjangan_tetap' => $this->form_tunjangan_tetap,
                    'tunjangan_absensi' => $this->form_tunjangan_absensi,
                    'tunjangan_jabatan' => $this->form_tunjangan_jabatan,
                    'tunjangan_shift' => $this->form_tunjangan_shift,
                    'tunjangan_radiologi' => $this->form_tunjangan_radiologi,
                    'tunjangan_lain' => $this->form_tunjangan_lain,
                    'uang_lembur' => $this->form_uang_lembur,
                    'tunjangan_hari_raya' => $this->form_tunjangan_hari_raya,
                    'potongan_absensi' => $this->form_potongan_absensi,
                    'potongan_cash_bon' => $this->form_potongan_cash_bon,
                    'potongan_obat' => $this->form_potongan_obat,
                    'potongan_lain' => $this->form_potongan_lain,
                    'potongan_bank' => $this->form_potongan_bank,
                    'potongan_bpjs_kes' => $this->calc_bpjs_kes,
                    'potongan_bpjs_tk' => $this->calc_bpjs_tk,
                    'bpjs_keluarga_tambahan' => $this->form_bpjs_keluarga_tambahan,
                    'potongan_pph21' => $this->calc_pph21,
                    'pph21_calculated' => $this->form_pph21_calculated,
                    'pph21_is_overridden' => $this->form_pph21_is_overridden,
                    'pph21_override_reason' => $this->form_pph21_override_reason,
                ],
                $this->form_umk_allocations,
                $this->form_tunjangan_lain_items,
                $this->calc_golongan,
                (float) $this->calc_masa_kerja
            );

            $this->toast()->success('Berhasil !', 'Slip gaji karyawan berhasil disimpan.')->send();
            $this->closeInputModal();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', 'Error: ' . $e->getMessage())->send();
        }
    }
}
