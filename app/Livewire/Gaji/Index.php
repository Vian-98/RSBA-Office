<?php

namespace App\Livewire\Gaji;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\Bagian;
use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\DB;
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollTemplateExport;
use App\Imports\PayrollImport;
use App\Models\Sdm\PayrollSendLog;
use App\Mail\SlipGajiMail;
use Illuminate\Support\Facades\Mail;


#[Title('Penggajian')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesFromRoute;
    use Interactions;
    use WithFileUploads;

    public string $search = '';
    public string $bagianFilter = '';
    public string $statusFilter = '';
    public string $payrollStatusFilter = '';
    
    #[Url]
    public string $periode = ''; // YYYY-MM
    public ?string $carriedOverFromPeriode = null;
    public int $calculatedLateMinutes = 0;
    public int $calculatedLateCount = 0;
    public int $calculatedOvertimeMinutes = 0;
    public int $perPage = 10;

    // Modal state for Slip View
    public bool $isOpenModal = false;
    public ?array $selectedSlip = null;

    // Modal state for Period-wide Edit Log View
    public bool $isPeriodLogModalOpen = false;
    public array $periodLogs = [];
    public string $periodLogSearch = '';

    // Modal state for Bulk Excel Import
    public bool $isImportModalOpen = false;
    public $excelFile = null;

    // Modal state for Auto-Send Configuration
    public bool $isAutoSendModalOpen = false;
    public bool $autoSendEnabled = false;
    public string $autoSendDay = '25';
    public string $autoSendTime = '08:00';
    public int $autoSendChunkSize = 10;
    public int $autoSendDelaySeconds = 3;
    public ?array $autoSendLastRun = null;

    // Modal & Progress state for Instant Batch Sending
    public bool $isBatchSendModalOpen = false;
    public bool $isBatchSending = false;
    public int $batchTotalCount = 0;
    public int $batchProcessedCount = 0;
    public int $batchSuccessCount = 0;
    public int $batchFailedCount = 0;
    public string $currentSendingStatus = '';


    // Modal state for Payroll Input

    public bool $isInputModalOpen = false;
    public ?int $selectedKaryawanId = null;
    public ?Karyawan $selectedKaryawan = null;

    // Payroll Input Form Fields
    public $form_gaji_pokok = 0;
    public $form_tunjangan_tetap = 0;
    public $form_tunjangan_absensi = 0; // Tj. Kehadiran
    public $form_tunjangan_jabatan = 0;
    public $form_tunjangan_shift = 0;
    public $form_tunjangan_radiologi = 0;
    public $form_tunjangan_lain = 0; // Automatically calculated sum of other allowances
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

    // Dynamic 25% UMK allocations
    public array $form_umk_allocations = [];

    // Dynamic "Tunjangan Lain-Lain" items list (for Jabatan, THR overrides, etc. bulanan)
    public array $form_tunjangan_lain_items = [];
    
    // Add item form state
    public ?int $temp_allowance_type_id = null;
    public $temp_allowance_nominal = 0;

    // Calculated fields (Live Preview)
    public $calc_bpjs_kes = 0;
    public $calc_bpjs_tk = 0;
    public $calc_pph21 = 0;
    public $calc_total_gaji = 0;
    public $calc_total_potongan = 0;
    public $calc_gaji_bersih = 0;
    public $calc_golongan = null;
    public $calc_masa_kerja = 0.0;
    
    public bool $isLocked = false;
    public string $periodStatus = 'draft';

    public function mount()
    {
        if (empty($this->periode)) {
            $this->periode = now()->format('Y-m');
        }

        $this->refreshLockStatus();
    }

    private function refreshLockStatus(): void
    {
        $lock = DB::table('sdm_payroll_period_locks')
            ->where('periode', $this->periode)
            ->first();

        $this->periodStatus = $lock->status ?? 'draft';

        $user = auth()->user();
        $isOnlyPajak = $user->hasRole('Pajak') && !$user->hasRole('Staff-SDM') && !$user->hasRole('Super-Admin');
        $isSDM = $user->hasRole('Staff-SDM') || $user->hasRole('Super-Admin');

        if ($this->periodStatus === 'approved') {
            $this->isLocked = true;
        } elseif ($this->periodStatus === 'review_pajak') {
            // Pajak can edit during their review, SDM cannot
            $this->isLocked = !$isOnlyPajak;
        } elseif ($this->periodStatus === 'review_sdm') {
            // SDM can view but cannot edit (waiting for finalisasi via rekap)
            $this->isLocked = true;
        } else {
            // draft: SDM can edit, Pajak cannot
            $this->isLocked = $isOnlyPajak;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBagianFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPayrollStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriode(): void
    {
        $this->resetPage();
    }

    public function updatedPeriode(): void
    {
        $this->refreshLockStatus();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // Runs automatically whenever a form property is updated
    public function updated($name)
    {
        if (str_starts_with($name, 'form_')) {
            $value = $this->{$name};
            if (is_string($value)) {
                $cleaned = str_replace('.', '', $value);
                $this->{$name} = is_numeric($cleaned) ? (double)$cleaned : 0;
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

    // Dynamic allowances actions
    public function addTunjanganLain()
    {
        $this->validate([
            'temp_allowance_type_id' => 'required|exists:sdm_payroll_allowance_types,id',
            'temp_allowance_nominal' => 'required|numeric|min:1',
        ], [
            'temp_allowance_type_id.required' => 'Jenis tunjangan harus dipilih.',
            'temp_allowance_nominal.required' => 'Nominal harus diisi.',
            'temp_allowance_nominal.min' => 'Nominal harus lebih dari 0.',
        ]);

        // Check if already added
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

        // Reset temporary input
        $this->temp_allowance_type_id = null;
        $this->temp_allowance_nominal = 0;

        $this->recalculate();
        
        $this->toast()->success('Berhasil !', 'Tunjangan lain-lain berhasil ditambahkan ke daftar.')->send();
    }

    public function removeTunjanganLain(int $index)
    {
        if (isset($this->form_tunjangan_lain_items[$index])) {
            unset($this->form_tunjangan_lain_items[$index]);
            $this->form_tunjangan_lain_items = array_values($this->form_tunjangan_lain_items);
            $this->recalculate();
            $this->toast()->success('Berhasil !', 'Tunjangan lain-lain dihapus dari daftar.')->send();
        }
    }

    private function recalculate()
    {
        // 1. Sum dynamic 25% UMK allocations
        $tTetap = 0.0;
        $tAbsen = 0.0;
        foreach ($this->form_umk_allocations as $alloc) {
            if ($alloc['is_absensi']) {
                $tAbsen += (double) $alloc['nominal'];
            } else {
                $tTetap += (double) $alloc['nominal'];
            }
        }
        
        $baseCalculator = PayrollCalculator::calculate($this->selectedKaryawan);
        $this->form_tunjangan_tetap = $tTetap + $baseCalculator['tunjangan_golongan_value'];
        $this->form_tunjangan_absensi = $tAbsen;
        $this->calc_golongan = $baseCalculator['golongan'];
        $this->calc_masa_kerja = $baseCalculator['masa_kerja_tahun'];

        // 2. Sum dynamic other allowances
        $sumTunjanganLain = 0.0;
        foreach ($this->form_tunjangan_lain_items as $item) {
            $sumTunjanganLain += (double) $item['nominal'];
        }
        $this->form_tunjangan_lain = $sumTunjanganLain;

        // 3. Total Earnings
        $totalEarnings = (double) $this->form_gaji_pokok +
            (double) $this->form_tunjangan_tetap +
            (double) $this->form_tunjangan_absensi +
            (double) $this->form_tunjangan_jabatan +
            (double) $this->form_tunjangan_shift +
            (double) $this->form_tunjangan_radiologi +
            (double) $this->form_tunjangan_lain +
            (double) $this->form_uang_lembur +
            (double) $this->form_tunjangan_hari_raya;

        // 4. BPJS & PPh21 calculations
        $this->calc_bpjs_kes = (double) $this->form_potongan_bpjs_kes;
        $this->calc_bpjs_tk = (double) $this->form_potongan_bpjs_tk;

        $calculatedDeductions = PayrollCalculator::calculateDeductions(
            (double) $this->form_gaji_pokok,
            (double) $this->form_tunjangan_tetap,
            (double) $totalEarnings,
            (int) $this->form_bpjs_keluarga_tambahan,
            $this->selectedKaryawan,
            $this->periode
        );

        $this->form_pph21_calculated = (double) $calculatedDeductions['potongan_pph21'];

        if ($this->form_pph21_is_overridden) {
            $this->calc_pph21 = (double) $this->form_potongan_pph21;
        } else {
            $this->calc_pph21 = $this->form_pph21_calculated;
            $this->form_potongan_pph21 = $this->calc_pph21;
        }
 
        // 5. Total Deductions
        $this->calc_total_potongan = (double) $this->form_potongan_absensi +
            (double) $this->form_potongan_cash_bon +
            (double) $this->form_potongan_obat +
            (double) $this->form_potongan_lain +
            (double) $this->calc_bpjs_kes +
            (double) $this->calc_bpjs_tk;

        $this->calc_total_gaji = $totalEarnings;

        if ($this->is_december && $this->selectedKaryawan) {
            $currentBruto = $totalEarnings;
            $currentBpjsTk = $this->calc_bpjs_tk;
            
            $this->ytd_total_bruto = $this->ytd_prior_bruto + $currentBruto;
            $this->ytd_total_bpjs_tk = $this->ytd_prior_bpjs_tk + $currentBpjsTk;
            
            // Biaya jabatan
            $this->ytd_biaya_jabatan = min(0.05 * $this->ytd_total_bruto, 6000000.00);
            
            // Neto setahun
            $this->ytd_neto = $this->ytd_total_bruto - $this->ytd_biaya_jabatan - $this->ytd_total_bpjs_tk;
            
            // PTKP
            $year = (int) substr($this->periode, 0, 4);
            $ptkpStatus = $this->selectedKaryawan->ptkp_status ?: 'TK0';
            $this->ytd_ptkp = DB::table('sdm_payroll_ptkp')
                ->where('status', $ptkpStatus)
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('berlaku_mulai_tahun', 'desc')
                ->value('nominal_setahun') ?: 54000000.00;
                
            // PKP
            $this->ytd_pkp = max(0, $this->ytd_neto - $this->ytd_ptkp);
            $this->ytd_pkp = floor($this->ytd_pkp / 1000) * 1000;
            
            // Progressive Pasal 17 total tax
            $pasal17Brackets = DB::table('sdm_payroll_pasal17')
                ->where('berlaku_mulai_tahun', '<=', $year)
                ->orderBy('pkp_bawah', 'asc')
                ->get();

            $pajakSetahun = 0;
            $remainingPkp = $this->ytd_pkp;

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
            
            $this->ytd_tax_annual = $pajakSetahun;
            $this->ytd_paid_jan_nov = $this->ytd_prior_pph21;
        }

        // 6. Net Salary
        $this->calc_gaji_bersih = $this->calc_total_gaji - 
            $this->calc_total_potongan - 
            (double) $this->calc_pph21 - 
            (double) $this->form_potongan_bank;
    }

    private function calculateAttendanceStats(int $karyawanId): array
    {
        if (empty($this->periode)) {
            return ['late_minutes' => 0, 'late_count' => 0, 'overtime_minutes' => 0];
        }

        try {
            $parsedDate = Carbon::parse($this->periode . '-01');
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
            // Lateness
            if ($d->status_kehadiran && strtolower($d->status_kehadiran) === 'terlambat') {
                $lateCount++;
                if ($d->catatan && preg_match('/Terlambat (-?\d+) menit/i', $d->catatan, $matches)) {
                    $mins = abs((int) $matches[1]);
                    if ($mins > $toleransiTelat) {
                        $lateMinutes += $mins;
                    }
                }
            }

            // Overtime (Lembur)
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

    public function openInputModal(int $karyawanId)
    {
        $karyawan = Karyawan::with(['jabatan.bagian'])->findOrFail($karyawanId);
        $this->selectedKaryawanId = $karyawanId;
        $this->selectedKaryawan = $karyawan;

        // Reset lists
        $this->form_tunjangan_lain_items = [];
        $this->form_umk_allocations = [];
        $this->temp_allowance_type_id = null;
        $this->temp_allowance_nominal = 0;

        // Calculate attendance stats & pre-fill auto-calculated parameters
        $stats = $this->calculateAttendanceStats($karyawanId);
        $this->calculatedLateMinutes = $stats['late_minutes'];
        $this->calculatedLateCount = $stats['late_count'];
        $this->calculatedOvertimeMinutes = $stats['overtime_minutes'];

        $rateLateDeduction = (double) (DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_kejadian')->value('value')
            ?: DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_menit')->value('value')
            ?: 50000);

        $autoPotonganAbsensi = (int) ($this->calculatedLateCount * $rateLateDeduction);
        $autoUangLembur = 0; // Uang Lembur is entered manually

        // Check if current month is December
        $this->is_december = str_ends_with($this->periode, '-12');
        $this->ytd_prior_bruto = 0.0;
        $this->ytd_prior_pph21 = 0.0;
        $this->ytd_prior_bpjs_tk = 0.0;
        
        if ($this->is_december) {
            $year = (int) substr($this->periode, 0, 4);
            $priorSlips = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawanId)
                ->where('periode', 'like', "$year-%")
                ->where('periode', '!=', $this->periode)
                ->get();

            foreach ($priorSlips as $ps) {
                $this->ytd_prior_bruto += (double) $ps->gaji_pokok + (double) $ps->tunjangan_tetap + (double) $ps->tunjangan_absensi + (double) $ps->tunjangan_jabatan + (double) $ps->tunjangan_shift + (double) $ps->tunjangan_radiologi + (double) $ps->tunjangan_lain + (double) $ps->uang_lembur + (double) $ps->tunjangan_hari_raya;
                $this->ytd_prior_pph21 += (double) $ps->potongan_pph21;
                $this->ytd_prior_bpjs_tk += (double) $ps->potongan_bpjs_tk;
            }
        }

        // Check if slip already exists for this period
        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $this->periode)
            ->first();

        if ($slip) {
            $this->carriedOverFromPeriode = null;

            // Load from database
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

            // Load UMK allocations from DB
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

            // Fallback load allocations if DB table was empty
            if (empty($this->form_umk_allocations)) {
                $base = PayrollCalculator::calculate($karyawan);
                $this->form_umk_allocations = $base['allocations_breakdown'];
            }

            // Load dynamic other allowances from DB
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
            // Calculate base defaults (recalculated automatically)
            $base = PayrollCalculator::calculate($karyawan);
            $this->form_gaji_pokok = $base['gaji_pokok'];
            $this->form_tunjangan_jabatan = $base['tunjangan_jabatan'];
            $this->form_umk_allocations = $base['allocations_breakdown'];

            // Sum allocations to calculate correct default deductions
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

            // Query employee's last slip from previous periods
            $lastSlip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawanId)
                ->where('periode', '<', $this->periode)
                ->orderBy('periode', 'desc')
                ->first();

            if ($lastSlip) {
                $this->carriedOverFromPeriode = $lastSlip->periode;

                // Copy routine variables
                $this->form_tunjangan_shift = (int) $lastSlip->tunjangan_shift;
                $this->form_tunjangan_radiologi = (int) $lastSlip->tunjangan_radiologi;
                $this->form_bpjs_keluarga_tambahan = (int) $lastSlip->bpjs_keluarga_tambahan;

                // Reset manual/variable deductions per month requirements
                $this->form_potongan_absensi = $autoPotonganAbsensi;
                $this->form_potongan_cash_bon = 0;
                $this->form_potongan_obat = 0;
                $this->form_potongan_lain = 0;
                $this->form_potongan_bank = 0;
                $this->form_potongan_bpjs_kes = (int) $lastSlip->potongan_bpjs_kes;
                $this->form_potongan_bpjs_tk = (int) $lastSlip->potongan_bpjs_tk;

                // Set auto-calculated variables and reset THR
                $this->form_uang_lembur = $autoUangLembur;
                $this->form_tunjangan_hari_raya = 0;

                // Load dynamic other allowances from that last slip
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

                // Reset inputs to 0 / Auto-calculations
                $this->form_tunjangan_shift = 0;
                $this->form_tunjangan_radiologi = 0;
                $this->form_tunjangan_lain = 0;
                $this->form_uang_lembur = $autoUangLembur;
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

    public function savePayroll()
    {
        if (DB::table('sdm_payroll_period_locks')->where('periode', $this->periode)->where('is_approved', true)->exists()) {
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

        $oldSlip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $this->selectedKaryawanId)
            ->where('periode', $this->periode)
            ->first();

        DB::beginTransaction();
        try {
            // 1. Insert/Update Gaji Slip
            DB::table('sdm_payroll_slips')->updateOrInsert(
                [
                    'karyawan_id' => $this->selectedKaryawanId,
                    'periode' => $this->periode
                ],
                [
                    'golongan' => $this->calc_golongan,
                    'masa_kerja_tahun' => $this->calc_masa_kerja,
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
                    'potongan_bpjs_kes' => $this->calc_bpjs_kes,
                    'potongan_bpjs_tk' => $this->calc_bpjs_tk,
                    'potongan_lain' => $this->form_potongan_lain,
                    'potongan_pph21' => $this->calc_pph21,
                    'pph21_bruto_bulan' => $this->calc_total_gaji,
                    'pph21_calculated' => $this->form_pph21_calculated,
                    'pph21_is_overridden' => $this->form_pph21_is_overridden ? 1 : 0,
                    'pph21_override_reason' => $this->form_pph21_is_overridden ? $this->form_pph21_override_reason : null,
                    'pph21_override_by' => $this->form_pph21_is_overridden ? auth()->id() : null,
                    'pph21_override_at' => $this->form_pph21_is_overridden ? now() : null,
                    'potongan_bank' => $this->form_potongan_bank,
                    'bpjs_keluarga_tambahan' => $this->form_bpjs_keluarga_tambahan,
                    'total_gaji' => $this->calc_total_gaji,
                    'total_potongan' => $this->calc_total_potongan,
                    'gaji_bersih' => $this->calc_gaji_bersih,
                    'created_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Get slip ID
            $insertedSlip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $this->selectedKaryawanId)
                ->where('periode', $this->periode)
                ->first();

            if ($insertedSlip) {
                // Save dynamic UMK allocations to DB
                DB::table('sdm_payroll_slip_allocations')
                    ->where('payroll_slip_id', $insertedSlip->id)
                    ->delete();

                foreach ($this->form_umk_allocations as $alloc) {
                    DB::table('sdm_payroll_slip_allocations')->insert([
                        'payroll_slip_id' => $insertedSlip->id,
                        'allowance_allocation_id' => $alloc['allowance_allocation_id'],
                        'nominal' => $alloc['nominal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Delete old other allowances
                DB::table('sdm_payroll_slip_allowances')
                    ->where('payroll_slip_id', $insertedSlip->id)
                    ->delete();

                // Save new other allowances
                foreach ($this->form_tunjangan_lain_items as $item) {
                    DB::table('sdm_payroll_slip_allowances')->insert([
                        'payroll_slip_id' => $insertedSlip->id,
                        'allowance_type_id' => $item['allowance_type_id'],
                        'nominal' => $item['nominal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Save override log if changed
                if ($this->form_pph21_is_overridden && $oldSlip && (double) $this->calc_pph21 !== (double) $oldSlip->potongan_pph21) {
                    DB::table('sdm_payroll_pph21_override_logs')->insert([
                        'payroll_slip_id' => $insertedSlip->id,
                        'nilai_lama' => $oldSlip->potongan_pph21,
                        'nilai_baru' => $this->calc_pph21,
                        'alasan' => $this->form_pph21_override_reason,
                        'diubah_oleh' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }

                // Save general edit log if changed
                if ($oldSlip) {
                    $fieldsToTrack = [
                        'gaji_pokok' => 'Gaji Pokok',
                        'tunjangan_tetap' => 'Tunjangan Tetap',
                        'tunjangan_absensi' => 'Tunjangan Kehadiran/Absensi',
                        'tunjangan_jabatan' => 'Tunjangan Jabatan',
                        'tunjangan_shift' => 'Tunjangan Shift',
                        'tunjangan_radiologi' => 'Tunjangan Radiologi',
                        'tunjangan_lain' => 'Tunjangan Lain-lain',
                        'uang_lembur' => 'Uang Lembur',
                        'tunjangan_hari_raya' => 'Tunjangan Hari Raya (THR)',
                        'potongan_absensi' => 'Potongan Kehadiran/Absensi',
                        'potongan_cash_bon' => 'Potongan Cash Bon',
                        'potongan_obat' => 'Potongan Obat',
                        'potongan_bpjs_kes' => 'Potongan BPJS Kesehatan',
                        'potongan_bpjs_tk' => 'Potongan BPJS Ketenagakerjaan',
                        'potongan_lain' => 'Potongan Lain-lain',
                        'potongan_pph21' => 'Potongan PPh 21',
                        'potongan_bank' => 'Potongan Bank',
                        'bpjs_keluarga_tambahan' => 'BPJS Keluarga Tambahan',
                    ];

                    $newValueMap = [
                        'gaji_pokok' => (double) $this->form_gaji_pokok,
                        'tunjangan_tetap' => (double) $this->form_tunjangan_tetap,
                        'tunjangan_absensi' => (double) $this->form_tunjangan_absensi,
                        'tunjangan_jabatan' => (double) $this->form_tunjangan_jabatan,
                        'tunjangan_shift' => (double) $this->form_tunjangan_shift,
                        'tunjangan_radiologi' => (double) $this->form_tunjangan_radiologi,
                        'tunjangan_lain' => (double) $this->form_tunjangan_lain,
                        'uang_lembur' => (double) $this->form_uang_lembur,
                        'tunjangan_hari_raya' => (double) $this->form_tunjangan_hari_raya,
                        'potongan_absensi' => (double) $this->form_potongan_absensi,
                        'potongan_cash_bon' => (double) $this->form_potongan_cash_bon,
                        'potongan_obat' => (double) $this->form_potongan_obat,
                        'potongan_bpjs_kes' => (double) $this->calc_bpjs_kes,
                        'potongan_bpjs_tk' => (double) $this->calc_bpjs_tk,
                        'potongan_lain' => (double) $this->form_potongan_lain,
                        'potongan_pph21' => (double) $this->calc_pph21,
                        'potongan_bank' => (double) $this->form_potongan_bank,
                        'bpjs_keluarga_tambahan' => (int) $this->form_bpjs_keluarga_tambahan,
                    ];

                    $changes = [];
                    foreach ($fieldsToTrack as $column => $label) {
                        $oldVal = (double) ($oldSlip->$column ?? 0);
                        $newVal = (double) ($newValueMap[$column] ?? 0);

                        if ($column === 'bpjs_keluarga_tambahan') {
                            $oldVal = (int) $oldVal;
                            $newVal = (int) $newVal;
                        }

                        if ($oldVal != $newVal) {
                            $changes[$column] = [
                                'label' => $label,
                                'old' => $oldVal,
                                'new' => $newVal,
                            ];
                        }
                    }

                    if (count($changes) > 0) {
                        DB::table('sdm_payroll_edit_logs')->insert([
                            'payroll_slip_id' => $insertedSlip->id,
                            'karyawan_id' => $this->selectedKaryawanId,
                            'periode' => $this->periode,
                            'perubahan' => json_encode($changes),
                            'diubah_oleh' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            $this->toast()
                ->success('Berhasil !', 'Slip gaji karyawan berhasil disimpan.')
                ->send();
            
            $this->closeInputModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal !', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function viewSlip(int $karyawanId): void
    {
        $karyawan = Karyawan::with(['jabatan.bagian'])->find($karyawanId);
        if (!$karyawan) {
            return;
        }

        // Get saved slip or build draft
        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $this->periode)
            ->first();

        if ($slip) {
            $latestJab = $karyawan->jabatan->first();
            $jabName = $latestJab ? $latestJab->nama : '-';
            $bagName = $latestJab && $latestJab->bagian ? $latestJab->bagian->nama : '-';

            // Get dynamic allowance details
            $breakdown = DB::table('sdm_payroll_slip_allowances')
                ->join('sdm_payroll_allowance_types', 'sdm_payroll_slip_allowances.allowance_type_id', '=', 'sdm_payroll_allowance_types.id')
                ->where('sdm_payroll_slip_allowances.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_allowance_types.nama', 'sdm_payroll_slip_allowances.nominal')
                ->get()
                ->toArray();

            // Get dynamic UMK allocation details to list them on the slip
            $allocsList = DB::table('sdm_payroll_slip_allocations')
                ->join('sdm_payroll_allowance_allocations', 'sdm_payroll_slip_allocations.allowance_allocation_id', '=', 'sdm_payroll_allowance_allocations.id')
                ->where('sdm_payroll_slip_allocations.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_allowance_allocations.nama', 'sdm_payroll_allowance_allocations.is_absensi', 'sdm_payroll_slip_allocations.nominal')
                ->get();

            // Get edit logs
            $editLogs = DB::table('sdm_payroll_edit_logs')
                ->join('users', 'sdm_payroll_edit_logs.diubah_oleh', '=', 'users.id')
                ->join('sdm_karyawan', 'users.karyawan_id', '=', 'sdm_karyawan.id')
                ->where('sdm_payroll_edit_logs.payroll_slip_id', $slip->id)
                ->select('sdm_payroll_edit_logs.*', 'sdm_karyawan.nama as editor_name')
                ->orderBy('sdm_payroll_edit_logs.created_at', 'desc')
                ->get()
                ->map(function ($log) {
                    $log->perubahan = json_decode($log->perubahan, true);
                    return $log;
                })
                ->toArray();

            $this->selectedSlip = [
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
                'bpjs_kes' => $slip->potongan_bpjs_kes,
                'bpjs_ket' => $slip->potongan_bpjs_tk,
                'potongan_lain' => $slip->potongan_lain,
                'pajak' => $slip->potongan_pph21,
                'potongan_bank' => $slip->potongan_bank,
                'gaji_bersih' => $slip->gaji_bersih,
                'total_gaji' => $slip->total_gaji,
                'total_potongan' => $slip->total_potongan,
                'periode' => Carbon::parse($this->periode . '-01')->translatedFormat('F Y'),
                'edit_logs' => $editLogs,
            ];
        } else {
            // Draft calculation
            $base = PayrollCalculator::calculate($karyawan);
            $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
            $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);

            $latestJab = $karyawan->jabatan->first();
            $jabName = $latestJab ? $latestJab->nama : '-';
            $bagName = $latestJab && $latestJab->bagian ? $latestJab->bagian->nama : '-';

            $totalPotongan = $deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk'];
            $gajiBersih = $totalPendapatan - $totalPotongan - $deductions['potongan_pph21'];

            $this->selectedSlip = [
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
                'bpjs_kes' => $deductions['potongan_bpjs_kes'],
                'bpjs_ket' => $deductions['potongan_bpjs_tk'],
                'potongan_lain' => 0.0,
                'pajak' => $deductions['potongan_pph21'],
                'potongan_bank' => 0.0,
                'gaji_bersih' => $gajiBersih,
                'total_gaji' => $totalPendapatan,
                'total_potongan' => $totalPotongan,
                'periode' => Carbon::parse($this->periode . '-01')->translatedFormat('F Y') . ' (DRAFT)',
                'edit_logs' => [],
            ];
        }
        $this->isOpenModal = true;
    }

    public function closeModal(): void
    {
        $this->isOpenModal = false;
        $this->selectedSlip = null;
    }

    public function updatedPeriodLogSearch()
    {
        $this->loadPeriodLogs();
    }

    public function openPeriodLogModal()
    {
        $this->periodLogSearch = '';
        $this->loadPeriodLogs();
        $this->isPeriodLogModalOpen = true;
    }

    public function loadPeriodLogs()
    {
        $query = DB::table('sdm_payroll_edit_logs')
            ->join('users', 'sdm_payroll_edit_logs.diubah_oleh', '=', 'users.id')
            ->join('sdm_karyawan as editor', 'users.karyawan_id', '=', 'editor.id')
            ->join('sdm_karyawan as target', 'sdm_payroll_edit_logs.karyawan_id', '=', 'target.id')
            ->leftJoin('sdm_kary_jabatan as target_kj', function ($join) {
                $join->on('target.id', '=', 'target_kj.karyawan_id')
                    ->whereRaw('target_kj.id = (select id from sdm_kary_jabatan where karyawan_id = target.id order by created_at desc limit 1)');
            })
            ->leftJoin('sdm_jabatan as target_j', 'target_kj.jabatan_id', '=', 'target_j.id')
            ->leftJoin('bagian as target_b', 'target_j.bagian_id', '=', 'target_b.id')
            ->where('sdm_payroll_edit_logs.periode', $this->periode);

        if (!empty($this->periodLogSearch)) {
            $search = '%' . $this->periodLogSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->where('editor.nama', 'like', $search)
                  ->orWhere('target.nama', 'like', $search)
                  ->orWhere('target.nip', 'like', $search)
                  ->orWhere('target_b.nama', 'like', $search)
                  ->orWhere('sdm_payroll_edit_logs.perubahan', 'like', $search);
            });
        }

        $this->periodLogs = $query->select(
                'sdm_payroll_edit_logs.*',
                'editor.nama as editor_name',
                'target.nama as employee_name',
                'target.nip as employee_nip',
                'target_b.nama as employee_bagian'
            )
            ->orderBy('sdm_payroll_edit_logs.created_at', 'desc')
            ->get()
            ->map(function ($log) {
                $log->perubahan = json_decode($log->perubahan, true);
                return (array) $log;
            })
            ->toArray();
    }

    public function closePeriodLogModal()
    {
        $this->isPeriodLogModalOpen = false;
        $this->periodLogs = [];
        $this->periodLogSearch = '';
    }

    public function triggerQueueWorker(): void
    {
        if (str_contains(PHP_OS_FAMILY, 'Windows')) {
            pclose(popen("start /B php artisan queue:work --stop-when-empty", "r"));
        } else {
            exec("php artisan queue:work --stop-when-empty > /dev/null 2>&1 &");
        }
    }

    public function sendEmail(int $karyawanId): void
    {
        $karyawan = Karyawan::with(['jabatan.bagian', 'user'])->find($karyawanId);

        if (!$karyawan) {
            $this->toast()->error('Gagal !', 'Karyawan tidak ditemukan.')->send();
            return;
        }

        $email = $karyawan->email ?: optional($karyawan->user)->email;
        if (!$email) {
            $this->toast()->warning('Peringatan !', 'Karyawan ini tidak memiliki alamat email terdaftar.')->send();
            return;
        }

        PayrollSendLog::updateOrCreate(
            ['periode' => $this->periode, 'karyawan_id' => $karyawanId],
            ['email' => $email, 'status' => 'pending', 'tipe_pengiriman' => 'manual', 'error_message' => null]
        );

        \App\Jobs\SendPayrollSlipJob::dispatch($karyawanId, $this->periode, 'manual');
        $this->triggerQueueWorker();

        $this->toast()->success('Diproses !', 'Pengiriman email slip gaji ke ' . $email . ' sedang berjalan di background.')->send();
    }

    public function sendSingleEmail(int $karyawanId): void
    {
        $this->sendEmail($karyawanId);
    }



    public function openAutoSendModal(): void
    {
        $this->authorizeFromRoute();
        $this->autoSendEnabled = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_enabled')->value('value') === '1';
        $this->autoSendDay = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_day')->value('value') ?: '25';
        $this->autoSendTime = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_time')->value('value') ?: '08:00';
        $this->autoSendChunkSize = (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_chunk_size')->value('value') ?: 10);
        $this->autoSendDelaySeconds = (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_delay_seconds')->value('value') ?: 3);
        
        $lastRunRaw = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_last_run')->value('value');
        $this->autoSendLastRun = $lastRunRaw ? json_decode($lastRunRaw, true) : null;
        
        $this->isAutoSendModalOpen = true;
    }

    public function closeAutoSendModal(): void
    {
        $this->isAutoSendModalOpen = false;
    }

    public function saveAutoSendSettings(): void
    {
        $this->authorizeFromRoute();

        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_enabled'], ['value' => $this->autoSendEnabled ? '1' : '0', 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_day'], ['value' => $this->autoSendDay, 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_time'], ['value' => $this->autoSendTime, 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_chunk_size'], ['value' => (string) $this->autoSendChunkSize, 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_delay_seconds'], ['value' => (string) $this->autoSendDelaySeconds, 'updated_at' => now()]);

        $this->isAutoSendModalOpen = false;
        $this->toast()->success('Berhasil !', 'Pengaturan jadwal pengiriman otomatis slip gaji berhasil disimpan.')->send();
    }



    public function openBatchSendModal(): void
    {
        $this->authorizeFromRoute();

        $employees = Karyawan::whereNull('resign_at')->get();
        $this->batchTotalCount = $employees->count();
        $this->refreshBatchProgress();

        $this->isBatchSendModalOpen = true;
    }

    public function closeBatchSendModal(): void
    {
        $this->isBatchSendModalOpen = false;
        $this->isBatchSending = false;
    }

    public function dispatchBulkQueue(): void
    {
        $this->authorizeFromRoute();

        $employees = Karyawan::whereNull('resign_at')->get();

        $dispatchedCount = 0;
        foreach ($employees as $karyawan) {
            $log = PayrollSendLog::where('periode', $this->periode)
                ->where('karyawan_id', $karyawan->id)
                ->first();

            if (!$log || $log->status !== 'sent') {
                $email = $karyawan->email ?: optional($karyawan->user)->email;
                if (!$email) {
                    PayrollSendLog::updateOrCreate(
                        [
                            'periode' => $this->periode,
                            'karyawan_id' => $karyawan->id,
                        ],
                        [
                            'email' => '-',
                            'status' => 'failed',
                            'tipe_pengiriman' => 'instant_batch',
                            'error_message' => 'Email karyawan belum terdaftar/kosong di sistem.',
                        ]
                    );
                } else {
                    PayrollSendLog::updateOrCreate(
                        [
                            'periode' => $this->periode,
                            'karyawan_id' => $karyawan->id,
                        ],
                        [
                            'email' => $email,
                            'status' => 'pending',
                            'tipe_pengiriman' => 'instant_batch',
                        ]
                    );
                    \App\Jobs\SendPayrollSlipJob::dispatch($karyawan->id, $this->periode, 'instant_batch');
                    $dispatchedCount++;
                }
            }
        }

        $this->isBatchSending = true;
        $this->triggerQueueWorker();
        $this->refreshBatchProgress();


        if ($dispatchedCount > 0) {
            $this->toast()->success('Antrean Dimulai !', "{$dispatchedCount} slip gaji telah dimasukkan ke dalam antrean pengiriman background.")->send();
        } else {
            $this->toast()->info('Selesai', 'Semua slip gaji karyawan periode ini telah terkirim.')->send();
        }
    }

    public function refreshBatchProgress(): void
    {
        $this->batchSuccessCount = PayrollSendLog::where('periode', $this->periode)->where('status', 'sent')->count();
        $this->batchFailedCount = PayrollSendLog::where('periode', $this->periode)->where('status', 'failed')->count();
        $this->batchProcessedCount = $this->batchSuccessCount + $this->batchFailedCount;

        if ($this->batchTotalCount > 0 && $this->batchProcessedCount >= $this->batchTotalCount) {
            $this->isBatchSending = false;
            $this->currentSendingStatus = 'Pengiriman antrean background selesai!';
        } else {
            $pendingCount = DB::table('jobs')->count();
            $this->currentSendingStatus = "Memproses antrean background... ({$this->batchProcessedCount}/{$this->batchTotalCount} selesai, {$pendingCount} dalam antrean)";
        }
    }

    public function getEmailSendStatus(int $karyawanId): ?array

    {
        $log = PayrollSendLog::where('periode', $this->periode)
            ->where('karyawan_id', $karyawanId)
            ->first();

        if (!$log) {
            return null;
        }

        return [
            'status' => $log->status,
            'sent_at' => $log->sent_at ? $log->sent_at->format('d/m/Y H:i') : null,
            'error' => $log->error_message,
            'tipe' => $log->tipe_pengiriman,
        ];
    }


    public function downloadTemplate()
    {
        $this->authorizeFromRoute();
        return Excel::download(
            new PayrollTemplateExport($this->periode),
            'template_penggajian_' . $this->periode . '.xlsx'
        );
    }

    public function openImportModal(): void
    {
        $this->excelFile = null;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal(): void
    {
        $this->isImportModalOpen = false;
        $this->excelFile = null;
    }

    public function importExcel(): void
    {
        $this->authorizeFromRoute();

        if ($this->isLocked) {
            $this->toast()->error('Gagal !', 'Periode ini telah disetujui dan terkunci. Data tidak dapat diubah.')->send();
            return;
        }

        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excelFile.required' => 'Pilih file Excel yang ingin diunggah.',
            'excelFile.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'excelFile.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $importer = new PayrollImport($this->periode);
            Excel::import($importer, $this->excelFile->getRealPath());

            $count = $importer->getImportedCount();
            $this->closeImportModal();
            $this->toast()->success('Berhasil !', "Berhasil mengimpor data penggajian untuk {$count} karyawan.")->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal Impor !', 'Terjadi kesalahan: ' . $e->getMessage())->send();
        }
    }

    public function exportToExcel()
    {
        $this->authorizeFromRoute();

        // Get the filtered list of employees (without pagination)
        $query = Karyawan::query()
            ->with(['jabatan.bagian']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nip', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->bagianFilter)) {
            $query->whereHas('jabatan.bagian', function ($q) {
                $q->where('id', $this->bagianFilter);
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->payrollStatusFilter)) {
            if ($this->payrollStatusFilter === 'generated') {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('sdm_payroll_slips')
                        ->whereColumn('sdm_payroll_slips.karyawan_id', 'sdm_karyawan.id')
                        ->where('sdm_payroll_slips.periode', $this->periode);
                });
            } elseif ($this->payrollStatusFilter === 'pending') {
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('sdm_payroll_slips')
                        ->whereColumn('sdm_payroll_slips.karyawan_id', 'sdm_karyawan.id')
                        ->where('sdm_payroll_slips.periode', $this->periode);
                });
            }
        }

        $karyawans = $query->get();
        $isDecember = str_ends_with($this->periode, '-12');
        $year = (int) substr($this->periode, 0, 4);

        $filename = "rekap_gaji_" . $this->periode . "_" . now()->format('Ymd_His') . ".xls";
        
        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($karyawans, $isDecember, $year) {
            $output = fopen('php://output', 'w');
            
            // Write standard Excel HTML headers
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
            
            // Add visual title
            $titlePeriode = Carbon::parse($this->periode . '-01')->translatedFormat('F Y');
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
                // Fetch slip details
                $slip = DB::table('sdm_payroll_slips')
                    ->where('karyawan_id', $karyawan->id)
                    ->where('periode', $this->periode)
                    ->first();

                $brutoYtd = 0.0;
                $pph21Ytd = 0.0;

                if ($isDecember) {
                    $priorSlips = DB::table('sdm_payroll_slips')
                        ->where('karyawan_id', $karyawan->id)
                        ->where('periode', 'like', "$year-%")
                        ->where('periode', '!=', $this->periode)
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
                    $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);
                    
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
                
                // Money cells
                fwrite($output, '<td class="money-cell">Rp ' . number_format($gajiPokok, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganTetap, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganAbsensi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganJabatan, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganShift, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganRadiologi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($tunjanganLain, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($uangLembur, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($thr, 0, ',', '.') . '</td>');
                
                // Total bruto
                fwrite($output, '<td class="bold-money-cell">Rp ' . number_format($totalBruto, 0, ',', '.') . '</td>');
                
                // Potongans
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potAbsensi, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potCashBon, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potObat, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potLain, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($potBank, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($bpjsKes, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($bpjsTk, 0, ',', '.') . '</td>');
                fwrite($output, '<td class="money-cell">Rp ' . number_format($pph21, 0, ',', '.') . '</td>');
                
                // Total potongan
                fwrite($output, '<td class="bold-money-cell">Rp ' . number_format($totalPotongan, 0, ',', '.') . '</td>');
                
                // Gaji bersih
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

    public function render()
    {
        $this->authorizeFromRoute();

        $query = Karyawan::query()
            ->with(['jabatan.bagian']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('nip', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->bagianFilter)) {
            $query->whereHas('jabatan.bagian', function ($q) {
                $q->where('id', $this->bagianFilter);
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->payrollStatusFilter)) {
            if ($this->payrollStatusFilter === 'generated') {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('sdm_payroll_slips')
                        ->whereColumn('sdm_payroll_slips.karyawan_id', 'sdm_karyawan.id')
                        ->where('sdm_payroll_slips.periode', $this->periode);
                });
            } elseif ($this->payrollStatusFilter === 'pending') {
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('sdm_payroll_slips')
                        ->whereColumn('sdm_payroll_slips.karyawan_id', 'sdm_karyawan.id')
                        ->where('sdm_payroll_slips.periode', $this->periode);
                });
            }
        }

        if ($this->perPage === -1) {
            $karyawans = $query->paginate($query->count() ?: 1);
        } else {
            $karyawans = $query->paginate($this->perPage);
        }

        $isDecember = str_ends_with($this->periode, '-12');
        $year = (int) substr($this->periode, 0, 4);

        // Transform collection to append calculated salary or database record
        $karyawans->getCollection()->transform(function ($karyawan) use ($isDecember, $year) {
            // Check if slip is already inputted in DB
            $slip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawan->id)
                ->where('periode', $this->periode)
                ->first();

            $brutoYtd = 0.0;
            $pph21Ytd = 0.0;

            if ($isDecember) {
                $priorSlips = DB::table('sdm_payroll_slips')
                    ->where('karyawan_id', $karyawan->id)
                    ->where('periode', 'like', "$year-%")
                    ->where('periode', '!=', $this->periode)
                    ->get();

                foreach ($priorSlips as $ps) {
                    $brutoYtd += (double) $ps->gaji_pokok + (double) $ps->tunjangan_tetap + (double) $ps->tunjangan_absensi + (double) $ps->tunjangan_jabatan + (double) $ps->tunjangan_shift + (double) $ps->tunjangan_radiologi + (double) $ps->tunjangan_lain + (double) $ps->uang_lembur + (double) $ps->tunjangan_hari_raya;
                    $pph21Ytd += (double) $ps->potongan_pph21;
                }
            }

            if ($slip) {
                $karyawan->payroll_status = 'generated';
                
                if ($isDecember) {
                    $currentBruto = (double) $slip->gaji_pokok + (double) $slip->tunjangan_tetap + (double) $slip->tunjangan_absensi + (double) $slip->tunjangan_jabatan + (double) $slip->tunjangan_shift + (double) $slip->tunjangan_radiologi + (double) $slip->tunjangan_lain + (double) $slip->uang_lembur + (double) $slip->tunjangan_hari_raya;
                    $brutoYtd += $currentBruto;
                    $pph21Ytd += (double) $slip->potongan_pph21;
                }

                $karyawan->calculated_salary = [
                    'gaji_pokok' => $slip->gaji_pokok,
                    'tunjangan' => $slip->tunjangan_tetap + $slip->tunjangan_absensi + $slip->tunjangan_jabatan + $slip->tunjangan_shift + $slip->tunjangan_radiologi + $slip->tunjangan_lain + $slip->uang_lembur + $slip->tunjangan_hari_raya,
                    'gaji_bersih' => $slip->gaji_bersih,
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                    'bruto_ytd' => $brutoYtd,
                    'pph21_ytd' => $pph21Ytd,
                ];
            } else {
                $karyawan->payroll_status = 'pending';
                $base = PayrollCalculator::calculate($karyawan);
                $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
                $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);
                $totalPotongan = $deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk'];
                $gajiBersih = $totalPendapatan - $totalPotongan - $deductions['potongan_pph21'];

                if ($isDecember) {
                    $brutoYtd += $totalPendapatan;
                    $pph21Ytd += $deductions['potongan_pph21'];
                }

                $karyawan->calculated_salary = [
                    'gaji_pokok' => $base['gaji_pokok'],
                    'tunjangan' => $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'],
                    'gaji_bersih' => $gajiBersih,
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                    'bruto_ytd' => $brutoYtd,
                    'pph21_ytd' => $pph21Ytd,
                ];
            }
            return $karyawan;
        });

        // Get allowance types for dropdown
        $allowanceTypes = DB::table('sdm_payroll_allowance_types')->orderBy('nama', 'asc')->get();

        $user = auth()->user();
        $isOnlyPajak = $user->hasRole('Pajak') && !$user->hasRole('Staff-SDM') && !$user->hasRole('Super-Admin');

        return view('livewire.gaji.index', [
            'karyawans' => $karyawans,
            'bagians' => Bagian::all(),
            'statusOptions' => \App\Enums\StatusKaryawan::options(),
            'allowanceTypes' => $allowanceTypes,
            'isOnlyPajak' => $isOnlyPajak,
            'periodStatus' => $this->periodStatus,
        ]);
    }
}
