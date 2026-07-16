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

#[Title('Penggajian')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesFromRoute;
    use Interactions;

    public string $search = '';
    public string $bagianFilter = '';
    
    #[Url]
    public string $periode = ''; // YYYY-MM
    public ?string $carriedOverFromPeriode = null;
    public int $calculatedLateMinutes = 0;
    public int $calculatedOvertimeMinutes = 0;
    public int $perPage = 10;

    // Modal state for Slip View
    public bool $isOpenModal = false;
    public ?array $selectedSlip = null;

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
    
    public bool $isLocked = false;

    public function mount()
    {
        if (empty($this->periode)) {
            $this->periode = now()->format('Y-m');
        }

        $this->isLocked = DB::table('sdm_payroll_period_locks')
            ->where('periode', $this->periode)
            ->where('is_approved', true)
            ->exists();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBagianFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPeriode(): void
    {
        $this->resetPage();
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

        // 6. Net Salary
        $this->calc_gaji_bersih = $this->calc_total_gaji - 
            $this->calc_total_potongan - 
            (double) $this->calc_pph21 - 
            (double) $this->form_potongan_bank;
    }

    private function calculateAttendanceStats(int $karyawanId): array
    {
        if (empty($this->periode)) {
            return ['late_minutes' => 0, 'overtime_minutes' => 0];
        }

        try {
            $parsedDate = Carbon::parse($this->periode . '-01');
            $bulan = $parsedDate->month;
            $tahun = $parsedDate->year;
        } catch (\Exception $e) {
            return ['late_minutes' => 0, 'overtime_minutes' => 0];
        }

        $details = DB::table('sdm_jadwal_kerja_detail')
            ->where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $toleransiTelat = (int) (DB::table('sdm_payroll_settings')->where('key', 'toleransi_telat_menit')->value('value') ?: 0);
        $lateMinutes = 0;
        $overtimeMinutes = 0;

        foreach ($details as $d) {
            // Lateness
            if ($d->status_kehadiran && strtolower($d->status_kehadiran) === 'terlambat' && $d->catatan) {
                if (preg_match('/Terlambat (-?\d+) menit/i', $d->catatan, $matches)) {
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
        $this->calculatedOvertimeMinutes = $stats['overtime_minutes'];

        $rateLate = (double) DB::table('sdm_payroll_settings')->where('key', 'potongan_telat_per_menit')->value('value') ?: 0;
        $rateOvertime = (double) DB::table('sdm_payroll_settings')->where('key', 'tarif_lembur_per_menit')->value('value') ?: 0;

        $autoPotonganAbsensi = (int) ($this->calculatedLateMinutes * $rateLate);
        $autoUangLembur = (int) ($this->calculatedOvertimeMinutes * $rateOvertime);

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

            $this->selectedSlip = [
                'id' => $karyawan->id,
                'nama' => $karyawan->full_nama,
                'nip' => $karyawan->nip,
                'status' => $karyawan->status->nama(),
                'jabatan' => $jabName,
                'bagian' => $bagName,
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
                'periode' => \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y'),
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
                'periode' => \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y') . ' (DRAFT)',
            ];
        }
        $this->isOpenModal = true;
    }

    public function closeModal(): void
    {
        $this->isOpenModal = false;
        $this->selectedSlip = null;
    }

    public function sendEmail(int $karyawanId): void
    {
        $karyawan = Karyawan::with(['jabatan.bagian', 'user'])->find($karyawanId);
        if (!$karyawan) {
            $this->toast()->error('Gagal !', 'Karyawan tidak ditemukan.')->send();
            return;
        }

        $email = optional($karyawan->user)->email;
        if (!$email) {
            $this->toast()->warning('Peringatan !', 'Karyawan ini tidak memiliki akun user atau alamat email terdaftar.')->send();
            return;
        }

        // Get saved slip or calculated draft
        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $karyawanId)
            ->where('periode', $this->periode)
            ->first();

        if ($slip) {
            $calc = [
                'gaji_pokok' => $slip->gaji_pokok,
                'tunjangan' => $slip->tunjangan_tetap + $slip->tunjangan_absensi + $slip->tunjangan_jabatan + $slip->tunjangan_shift + $slip->tunjangan_radiologi + $slip->tunjangan_lain + $slip->uang_lembur + $slip->tunjangan_hari_raya,
                'bpjs_kes' => $slip->potongan_bpjs_kes,
                'bpjs_ket' => $slip->potongan_bpjs_tk,
                'pajak' => $slip->potongan_pph21 + $slip->potongan_bank,
                'gaji_bersih' => $slip->gaji_bersih,
                'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
            ];
        } else {
            $base = PayrollCalculator::calculate($karyawan);
            $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
            $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);

            $calc = [
                'gaji_pokok' => $base['gaji_pokok'],
                'tunjangan' => $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'],
                'bpjs_kes' => $deductions['potongan_bpjs_kes'],
                'bpjs_ket' => $deductions['potongan_bpjs_tk'],
                'pajak' => $deductions['potongan_pph21'],
                'gaji_bersih' => $totalPendapatan - ($deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk']) - $deductions['potongan_pph21'],
                'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
            ];
        }

        $slipData = [
            'nama' => $karyawan->full_nama,
            'nip' => $karyawan->nip,
            'status' => $karyawan->status->nama(),
            'jabatan' => $calc['jabatan_nama'],
            'bagian' => $calc['bagian_nama'],
            'periode' => \Carbon\Carbon::parse($this->periode . '-01')->translatedFormat('F Y'),
            'gaji_pokok' => $calc['gaji_pokok'],
            'tunjangan' => $calc['tunjangan'],
            'bpjs_kes' => $calc['bpjs_kes'],
            'bpjs_ket' => $calc['bpjs_ket'],
            'pajak' => $calc['pajak'],
            'gaji_bersih' => $calc['gaji_bersih'],
        ];

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SlipGajiMail($slipData));
            $this->toast()->success('Berhasil !', 'Slip gaji berhasil dikirim ke email: ' . $email)->send();
        } catch (\Throwable $e) {
            $this->toast()->error('Gagal !', 'Error saat mengirim email: ' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $query = Karyawan::query()
            ->with(['jabatan.bagian']);

        if (!empty($this->search)) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->bagianFilter)) {
            $query->whereHas('jabatan.bagian', function ($q) {
                $q->where('id', $this->bagianFilter);
            });
        }

        if ($this->perPage === -1) {
            $karyawans = $query->paginate($query->count() ?: 1);
        } else {
            $karyawans = $query->paginate($this->perPage);
        }

        // Transform collection to append calculated salary or database record
        $karyawans->getCollection()->transform(function ($karyawan) {
            // Check if slip is already inputted in DB
            $slip = DB::table('sdm_payroll_slips')
                ->where('karyawan_id', $karyawan->id)
                ->where('periode', $this->periode)
                ->first();

            if ($slip) {
                $karyawan->payroll_status = 'generated';
                $karyawan->calculated_salary = [
                    'gaji_pokok' => $slip->gaji_pokok,
                    'tunjangan' => $slip->tunjangan_tetap + $slip->tunjangan_absensi + $slip->tunjangan_jabatan + $slip->tunjangan_shift + $slip->tunjangan_radiologi + $slip->tunjangan_lain + $slip->uang_lembur + $slip->tunjangan_hari_raya,
                    'gaji_bersih' => $slip->gaji_bersih,
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                ];
            } else {
                $karyawan->payroll_status = 'pending';
                $base = PayrollCalculator::calculate($karyawan);
                $totalPendapatan = $base['gaji_pokok'] + $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'];
                $deductions = PayrollCalculator::calculateDeductions($base['gaji_pokok'], $base['tunjangan_tetap'], $totalPendapatan, 0, $karyawan, $this->periode);
                $totalPotongan = $deductions['potongan_bpjs_kes'] + $deductions['potongan_bpjs_tk'];
                $gajiBersih = $totalPendapatan - $totalPotongan - $deductions['potongan_pph21'];

                $karyawan->calculated_salary = [
                    'gaji_pokok' => $base['gaji_pokok'],
                    'tunjangan' => $base['tunjangan_tetap'] + $base['tunjangan_absensi'] + $base['tunjangan_jabatan'],
                    'gaji_bersih' => $gajiBersih,
                    'bagian_nama' => $karyawan->jabatan->first() && $karyawan->jabatan->first()->bagian ? $karyawan->jabatan->first()->bagian->nama : 'Umum',
                    'jabatan_nama' => $karyawan->jabatan->first() ? $karyawan->jabatan->first()->nama : 'Staff',
                ];
            }
            return $karyawan;
        });

        // Get allowance types for dropdown
        $allowanceTypes = DB::table('sdm_payroll_allowance_types')->orderBy('nama', 'asc')->get();

        return view('livewire.gaji.index', [
            'karyawans' => $karyawans,
            'bagians' => Bagian::all(),
            'allowanceTypes' => $allowanceTypes,
        ]);
    }
}
