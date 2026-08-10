<?php

namespace App\Livewire\Gaji\Concerns;

use App\Models\Sdm\Karyawan;
use App\Livewire\Gaji\Services\PayrollRekapService;
use App\Imports\PayrollImport;
use App\Exports\PayrollTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

trait HasPayrollImportExport
{
    public bool $isImportModalOpen = false;
    public $excelFile = null;

    public function downloadTemplate()
    {
        $this->authorizeFromRoute();
        return Excel::download(new PayrollTemplateExport($this->periode), 'template_penggajian_' . $this->periode . '.xlsx');
    }

    public function openImportModal(): void { $this->excelFile = null; $this->isImportModalOpen = true; }
    public function closeImportModal(): void { $this->isImportModalOpen = false; $this->excelFile = null; }

    public function importExcel(): void
    {
        $this->authorizeFromRoute();

        if ($this->isLocked) {
            $this->toast()->error('Gagal !', 'Periode ini telah disetujui dan terkunci. Data tidak dapat diubah.')->send();
            return;
        }

        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
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

    public function exportToExcel(PayrollRekapService $rekapService)
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

        $karyawans = $query->get();
        return $rekapService->exportExcelResponse($karyawans, $this->periode);
    }
}
