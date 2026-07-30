<?php

namespace App\Livewire\Jasmed;

use Throwable;
use Livewire\Component;
use App\Imports\VisiteImport;
use Livewire\WithFileUploads;
use App\Imports\PasiensImport;
use App\Imports\RincianImport;
use App\Imports\DisetujuiImport;
use App\Exports\TemplateImportJasa;
use Livewire\Attributes\Lazy;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;

#[Lazy()]
class BpjsImport extends Component
{
    use WithFileUploads;
    use Interactions;

    public $excelPasien;
    public $excelDisetujui;
    public $excelDokter;
    public $excelRincian;
    public string $cabar;


    public function mount(String $content): void
    {
        $this->cabar = $content;
    }

    function importData($file, $importClass, $successMessage)
    {
        $this->validateOnly($file, [$file => 'required|mimes:xlsx,xls']);

        try {
            $importedFile = $this->$file->store($file);
            Excel::import(new $importClass, $importedFile);

            $this->toast()->success('Berhasil !', $successMessage)->send();
        } catch (Throwable $e) {
            $this->toast()->error('Gagal !', "Error : " . $e->getMessage())->send();
        }
    }

    function importPasien()
    {

        $this->importData('excelPasien', PasiensImport::class, 'Upload data pasien sukses.!');
    }

    function importDisetujui()
    {

        $this->importData('excelDisetujui', DisetujuiImport::class, 'Upload data disetujui sukses.!');
    }

    function importDokter()
    {
        $this->importData('excelDokter', VisiteImport::class, 'Upload data dokter sukses.!');
        $this->cekDokter = true;
    }

    function importRincian()
    {
        $this->importData('excelRincian', RincianImport::class, 'Upload data rincian sukses.!');
    }


    function downloadTemplate($template)
    {
        $templateName = [
            'txt' => 'Pasien Inacbg',
            'disetujui' => 'Disetujui',
            'visit' => 'Dokter',
            'rincian_inacbg' => 'Rincian Inacbg',
            'ranap' => 'Rawat Inap',
            'rajal' => 'Rawat Jalan'
        ];

        return Excel::download(
            new TemplateImportJasa($template),
            'Template ' . $templateName[$template] . '.xlsx'
        );
    }

    function resetFileInput($file)
    {
        $this->$file = null;
    }

    public function render()
    {
        return view('livewire.jasmed.bpjs-import');
    }
}
