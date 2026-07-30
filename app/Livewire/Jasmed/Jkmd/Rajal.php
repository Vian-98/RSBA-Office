<?php

namespace App\Livewire\Jasmed\Jkmd;

use Throwable;
use App\Models\JmJasa;
use Livewire\Component;
use App\Models\JmDokter;
use App\Models\JmPasien;
use App\Exports\RajalExport;
use App\Imports\RajalImport;
use App\Models\JmProsentase;
use Livewire\Attributes\Lazy;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Exports\TemplateImportJasa;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Rajal extends Component
{
    use WithFileUploads;
    use Interactions;


    private function getPasien($tahun, $bulan, $batch)
    {
        return JmPasien::whereYear('tgl_checkout', $tahun)
            ->whereMonth('tgl_checkout', $bulan)
            ->where('disetujui', '>', 0)
            ->where('layanan', 'rajal')
            ->where('cabar', 'jkmd')
            ->where('batch', $batch)
            ->whereNotNull('kelompok');
    }

    private function getDokter($pasien)
    {
        $dokters = JmDokter::where('jm_pasien_id', $pasien->id)->get();

        // ada dpjp ?
        $hasDpjp = $dokters->contains('status', 'dpjp');

        // jika tidak ada
        if (!$hasDpjp) {
            // add pasien->dpjp ke colection JmDokter
            $defaultDokter = new JmDokter([
                'dokter' => $pasien->dpjp,
                'status' => 'dpjp',
                'jumlah' => 1,
            ]);
            // push data nya ke Collection JmDokter
            $dokters->push($defaultDokter);
        }

        return $dokters;
    }

    public $excelImportRajal;
    function importRajal()
    {
        $this->validate(['excelImportRajal' => 'required|mimes:xlsx,xls']);

        try {
            $file = $this->excelImportRajal->store('excelImportRajal');
            Excel::import(new RajalImport(), $file);

            // toast 
            $this->toast()->success('Berhasil !', 'Import data berhasil.!')->send();
        } catch (Throwable $e) {
            $errors = $e->getMessage();

            // toast
            $this->toast()->error('Gagal !', "Error : $errors")->send();
        }
    }


    /**
     * PROSES HITUNG JASA RAJAL
     */
    public $bulan_rj;
    public $batch_rj;
    // public $downloadableRajal = true;

    function submitProsesRajal()
    {
        $this->validate([
            'bulan_rj' => 'required',
            'batch_rj' => 'required'
        ]);

        // split tahun bulan
        [$tahun, $bulan] = explode('-', $this->bulan_rj);


        // Get total data count for progress display
        $totalData = $this->getPasien($tahun, $bulan, $this->batch_rj)->count();

        // Get pasien data in chunks
        $chunkSize = 100; // Process 500 rows at a time
        $totalProcessed = 0;

        try {
            // Chunking pasien data for better performance
            $this->getPasien($tahun, $bulan, $this->batch_rj)
                ->chunk(
                    $chunkSize,
                    function ($pasiens) use (&$totalProcessed, &$totalData) {
                        // BEGINING TRANSACTIONS
                        DB::beginTransaction();
                        // each data chunks
                        foreach ($pasiens as $pasien) {
                            try {
                                // DOING EXECUTE kalkulasi hitung rajal
                                $dokter = $this->getDokter($pasien);
                                $this->calcProsentaseRajal($pasien, $dokter);
                            } catch (Throwable $e) {
                                // toast error
                                $this->toast()
                                    ->error('Failed!', "Error: " . $e->getMessage())
                                    ->send();

                                // logs
                                Log::error("Error processing pasien {$pasien->id}: " . $e->getMessage());
                                continue; // Continue with the next pasien
                            }
                        }

                        // Commit the batch
                        DB::commit();

                        // Update progress
                        $totalProcessed += count($pasiens);

                        // info progress data pada toast info
                        Log::info("Proses Jasa Rajal {$totalProcessed} dari {$totalData} ");
                    }
                );


            // all execute, dan commit,and toast sukses
            $this->toast()
                ->success('Berhasil', "Proses Hitung Jasa Rajal Selesai, Total Data {$totalProcessed}!")
                ->send();
            // END TRANSACTIONS
        } catch (Throwable $e) {

            // ROLLBACK IF HAS ERROR
            DB::rollBack();

            $this->toast()->error('Failed!', "Critical Error: " . $e->getMessage())->send();
            Log::error('Critical Error: ' . $e->getMessage());
        }
    }


    private function calcProsentaseRajal($pasien, $dokter)
    {

        $klaim = $pasien->disetujui;
        $tarif_rs = $pasien->tarif_rs;
        $j_38 = ceil(($klaim * 38) / 100);

        $j_rs = ceil(($j_38 * 10) / 100);
        $j_dokter = 0;
        $j_pekerja = 0;
        $j_umum_s = 0;
        $j_sppdkgh = 0;
        $status = null;

        switch ($pasien->kelompok) {
            case 'rj_sp':
                // jika deskripsi contains(ct scan) => klaim - jasa radiologi
                if (preg_match('/scan/i', $pasien->deskripsi_inacbg)) {
                    // $j_38 = ceil((($klaim - 282840) * 38) / 100);
                    $j_38 = ceil((198100 * 38) / 100);
                }

                $status = 'RAWAT JALAN';
                $j_rs = ceil(($j_38 * 10) / 100);
                $j_dokter = ceil(($j_38 * 80.22) / 100);
                $j_pekerja = ceil(($j_38 * 9.78) / 100);
                break;

            case 'rj_um':

                if (preg_match('/scan/i', $pasien->deskripsi_inacbg)) {
                    // $j_38 = ceil((($klaim - 282840) * 38) / 100);
                    $j_38 = ceil((198100 * 38) / 100);
                } else {

                    // check klaim - rincian = minus?
                    $is_minus = $klaim - $tarif_rs;

                    // jika minus
                    if ($is_minus < 0) {

                        /**
                         * Sebelumnya : klaim - minus, kemudian 38%
                         * klaim di kurangi minus nya
                         * $sisa = $klaim + $is_minus;
                         * $j_38 = ceil(($sisa * 38) / 100);
                         */

                        //  Saat ini : jasa38% = 38% dikurang nilain minus
                        $j_38 = ceil(($klaim * 38) / 100) + $is_minus;

                        // j38 dikuragi minus, kurang dari 0?
                        if ($j_38 < 0) {
                            $j_38 = 0;
                        }
                    }
                }

                $status  = 'RAWAT JALAN';
                $j_rs = ceil(($j_38 * 10) / 100);
                $j_dokter = ceil(($j_38 * 41.10) / 100);
                $j_pekerja = ceil(($j_38 * 48.90) / 100);
                break;

            case 'rj_mata':
                $status = 'RAWAT JALAN MATA';
                $j_dokter = ceil(($j_38 * 75) / 100);
                $j_pekerja = ceil(($j_38 * 15) / 100);
                break;

            case 'rj_hd':
                $status = 'HD';
                // $j_38 = ceil(($klaim * 14) / 100);
                // $j_dokter = ceil(($j_38 * 25) / 100);
                // $j_pekerja = ceil(($j_38 * 51) / 100);
                // $j_sppdkgh = ceil(($j_38 * 12) / 100);
                // $j_umum_s = ceil(($j_38 * 12) / 100);

                // per 1 februari 2024
                $j_38 = ceil(($klaim * 11) / 100);
                $j_dokter = ceil(($j_38 * 35) / 100);
                $j_pekerja = ceil(($j_38 * 35) / 100);
                $j_sppdkgh = ceil(($j_38 * 15) / 100);
                $j_umum_s = ceil(($j_38 * 15) / 100);
                break;
        }

        $data = [
            'jasa_pelayanan' => $j_38,
            'jasa_rs' => $j_rs,
            'jasa_medis' => $j_dokter,
            'jasa_pekerja' => $j_pekerja,
            'jasa_sppdkgh' => $j_sppdkgh,
            'jasa_um_sertifikat' => $j_umum_s
        ];

        // insert
        $prosentase = JmProsentase::updateOrCreate(
            ['jm_pasien_id' => $pasien->id],
            $data
        );
        // delete jasa dokter
        $prosentase->jasa()->delete();

        // jasa per dokter
        foreach ($dokter as $dok) {
            $jasaDokter = 0;
            $statusDokter = $dok->status;

            if ($statusDokter === 'dpjp') {
                $jasaDokter = $j_dokter;
            } else if ($statusDokter === 'um_s') {
                $jasaDokter = $j_umum_s;
            } else if ($statusDokter === 'sppdkgh') {
                $jasaDokter = $j_sppdkgh;
            }

            $dataDokter = [
                'jm_prosentase_id' => $prosentase->id,
                'dokter' => $dok->dokter,
                'status' => $status,
                'jasa' => $jasaDokter
            ];

            JmJasa::create($dataDokter);
        }
    }

    // Download hasil rekap jasa
    public $pilih_download_rajal;
    function downloadRajal()
    {
        $this->validate(['bulan_rj' => 'required', 'batch_rj' => 'required']);

        // $periode = $this->bulan_rj;
        // $kelompok  = $this->pilih_download_rajal;
        // $batch = $this->batch_rj;

        return Excel::download(
            new RajalExport(
                periode: $this->bulan_rj,
                cabar: 'jkmd',
                kelompok: $this->pilih_download_rajal,
                batch: $this->batch_rj
            ),
            'Rekap Jasa Rajal JKMD ' . $this->bulan_rj . '.xlsx'
        );

        $this->toast()->success('Sukses!', 'Download berhasil.')->send();
    }


    // Download template excel
    function downloadTemplate($template)
    {
        return Excel::download(
            new TemplateImportJasa($template),
            'Template Import Kelompok Rawat Jalan.xlsx'
        );
    }


    public function render()
    {
        return view('livewire.jasmed.jkmd.rajal');
    }
}
