<?php

namespace App\Livewire\Karyawan;

use Throwable;
use App\Exports\TemplateImportKaryawan;
use App\Imports\KaryawanImport;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;

#[Lazy]
class ImportKaryawan extends Component
{
    use WithFileUploads;
    use Interactions;

    public $excelKaryawan;

    public $rules = [
        'excelKaryawan' => 'required|mimes:xlsx,xls'
    ];

    public function importKaryawan()
    {
        $this->validate();

        try {
            $file = $this->excelKaryawan->store('excelKaryawan');
            Excel::import(new KaryawanImport, $file);

            $this->dispatch('karyawan-imported');

            $this->toast()
                ->success('Import Karyawan Berhasil!', 'Data Karyawan berhasil diimport!')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Import Karyawan Gagal!', "Error : " . $e->getMessage())
                ->send();
        }
    }

    function downloadTemplate()
    {
        return Excel::download(
            new TemplateImportKaryawan(),
            'template_import_karyawan.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.karyawan.import-karyawan');
    }
}
