<?php

namespace App\Livewire\Jasmed\Jkmd;

use Throwable;
use Livewire\Component;
use App\Models\JmPasien;
use App\Exports\RanapExport;
use App\Imports\RanapImport;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use App\Exports\TemplateImportJasa;
use App\Services\JasaMedisBpjsService;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Ranap extends Component
{
    use WithFileUploads;
    use Interactions;

    public $pilih_download_ranap;

    public $bulan_ri;
    public $batch_ri;


    protected JasaMedisBpjsService $jasaMedisBpjsService;

    public function boot(JasaMedisBpjsService $jasaMedisBpjsService)
    {
        $this->jasaMedisBpjsService = $jasaMedisBpjsService;
    }

    public $excelImportKelompokRanap;

    function importKelompokRanap()
    {
        $this->validateOnly('excelImportKelompokRanap', ['excelImportKelompokRanap' => 'required|mimes:xlsx,xls']);

        $file = $this->excelImportKelompokRanap->store('excelImportKelompokRanap');
        try {
            Excel::import(new RanapImport(), $file);

            $this->toast()
                ->success(
                    'Sukses!',
                    'Upload data sukses.!'
                )->send();
        } catch (Throwable $e) {
            $errors = $e->getMessage();
            $this->toast()
                ->error(
                    'Gagal !',
                    "Errror : $errors"
                )->send();
        }
    }


    private function getPasien($tahun, $bulan, $batch)
    {
        return JmPasien::whereYear('tgl_checkout', $tahun)
            ->whereMonth('tgl_checkout', $bulan)
            ->where('disetujui', '>', 0)
            ->where('layanan', 'ranap')
            ->where('cabar', 'jkmd')
            ->where('batch', $batch)
            ->whereNotNull('kelompok')
            ->get();
    }

    /** 
     * HITUNG JASA RAWAT INAP JKMD
     */
    function sumbitProcessRanap()
    {
        $this->validate(['bulan_ri' => 'required', 'batch_ri' => 'required']);

        // split tahun bulan
        [$tahun, $bulan] = explode('-', $this->bulan_ri);

        // get pasien data
        $pasiens = $this->getPasien(tahun: $tahun, bulan: $bulan, batch: $this->batch_ri);

        // each by pasien
        $hasError = false;
        foreach ($pasiens as $pasien) {
            // try hitung dan rekap
            try {

                // proses dengan service
                $this->jasaMedisBpjsService->processPasien($pasien);
            } catch (Throwable $e) {
                $errors = $e->getMessage();

                // toast
                $this->toast()->error('Gagal !', "Error : " . $errors)->send();
                $hasError = true;
                continue;
            }
        }

        // toast
        if (!$hasError) {
            # code...
            $this->toast()->success('Berhasil !', 'Proses hitung jasa selesai.!')->send();
        }
    }

    // Download hasil rekap ranap
    public function downloadRanap()
    {
        $this->validate(['bulan_ri' => 'required', 'batch_ri' => 'required']);

        // $periode = $this->bulan_ri;
        // $kelompok = $this->pilih_download_ranap;
        // $batch = $this->batch_ri;

        return Excel::download(
            new RanapExport(
                periode: $this->bulan_ri,
                cabar: 'jkmd',
                kelompok: $this->pilih_download_ranap,
                batch: $this->batch_ri
            ),
            'Rekap Jasa Ranap JKMD ' . $this->bulan_ri . '.xlsx'
        );

        $this->toast()->success('Sukses !', 'Download berhasil.')->send();
    }

    function downloadTemplate($template)
    {
        return Excel::download(
            new TemplateImportJasa($template),
            'Template Import Kelompok Rawat Inap.xlsx'
        );
    }



    public function render()
    {
        return view('livewire.jasmed.jkmd.ranap');
    }
}
