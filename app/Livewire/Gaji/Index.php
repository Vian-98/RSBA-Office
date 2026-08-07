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
use App\Livewire\Gaji\Services\PayrollCalculator;
use App\Livewire\Gaji\Services\PayrollService;
use App\Livewire\Gaji\Services\PayrollPeriodService;
use App\Livewire\Gaji\Concerns\HasPayrollInputForm;
use App\Livewire\Gaji\Concerns\HasPayrollBatchNotifications;
use App\Livewire\Gaji\Concerns\HasPayrollImportExport;
use Livewire\WithFileUploads;

#[Title('Penggajian')]
class Index extends Component
{
    use WithPagination;
    use AuthorizesFromRoute;
    use Interactions;
    use WithFileUploads;

    use HasPayrollInputForm;
    use HasPayrollBatchNotifications;
    use HasPayrollImportExport;

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
        $lockInfo = app(PayrollPeriodService::class)->getLockStatus($this->periode);
        $this->periodStatus = $lockInfo['status'];
        $this->isLocked = $lockInfo['is_locked'];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingBagianFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPayrollStatusFilter(): void { $this->resetPage(); }
    public function updatingPeriode(): void { $this->resetPage(); }
    public function updatedPeriode(): void { $this->refreshLockStatus(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function updated($name)
    {
        $this->updatedHasPayrollInputForm($name);
    }

    public function viewSlip(int $karyawanId, PayrollService $payrollService): void
    {
        $this->selectedSlip = $payrollService->getSlipViewData($karyawanId, $this->periode);
        if ($this->selectedSlip) {
            $this->isOpenModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->isOpenModal = false;
        $this->selectedSlip = null;
    }

    public function updatedPeriodLogSearch() { $this->loadPeriodLogs(); }

    public function openPeriodLogModal()
    {
        $this->periodLogSearch = '';
        $this->loadPeriodLogs();
        $this->isPeriodLogModalOpen = true;
    }

    public function loadPeriodLogs()
    {
        $this->periodLogs = app(PayrollService::class)->getPeriodEditLogs($this->periode, $this->periodLogSearch);
    }

    public function closePeriodLogModal()
    {
        $this->isPeriodLogModalOpen = false;
        $this->periodLogs = [];
        $this->periodLogSearch = '';
    }

    public function render()
    {
        $this->authorizeFromRoute();

        $query = Karyawan::query()->with(['jabatan.bagian']);

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

        $karyawans = ($this->perPage === -1)
            ? $query->paginate($query->count() ?: 1)
            : $query->paginate($this->perPage);

        $isDecember = str_ends_with($this->periode, '-12');
        $year = (int) substr($this->periode, 0, 4);

        $karyawans->getCollection()->transform(function ($karyawan) use ($isDecember, $year) {
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

        $allowanceTypes = DB::table('sdm_payroll_allowance_types')->orderBy('nama', 'asc')->get();
        $user = auth()->user();
        $isOnlyPajak = $user && $user->hasRole('Pajak') && !$user->hasRole('Staff-SDM') && !$user->hasRole('Super-Admin');

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
