<?php

namespace App\Livewire\Jasmed;

use Throwable;
use App\Models\JmJasa;
use Livewire\Component;
use App\Models\JmDokter;

use App\Models\JmPasien;

use App\Models\JmRincian;
use App\Exports\RajalExport;
use App\Exports\RanapExport;
use App\Imports\RajalImport;
use App\Imports\RanapImport;
use App\Models\JmProsentase;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Exports\TemplateImportJasa;
use Livewire\Attributes\Lazy;
use Maatwebsite\Excel\Facades\Excel;
use TallStackUi\Traits\Interactions;

#[Title('Jasmed BPJS')]
#[Lazy()]
class Bpjs extends Component
{
    use WithFileUploads;
    use Interactions;

    // public $title;
    public $excelImportJasaRanap;

    function importJasaRanap()
    {
        $this->validateOnly('excelImportJasaRanap', ['excelImportJasaRanap' => 'required|mimes:xlsx,xls']);

        $file = $this->excelImportJasaRanap->store('excelJasaRanap');
        try {
            Excel::import(new RanapImport(), $file);

            $this->toast()
                ->success(
                    'Success!',
                    'Upload data sukses.!'
                )->send();
        } catch (Throwable $e) {
            $errors = $e->getMessage();
            $this->toast()
                ->error(
                    'Failed!',
                    "Errror : $errors"
                )->send();
        }
    }


    private function getPasien($tahun, $bulan, $pelayanan, $batch)
    {
        return JmPasien::whereYear('tgl_checkout', $tahun)
            ->whereMonth('tgl_checkout', $bulan)
            ->where('disetujui', '>', 0)
            ->where('layanan', $pelayanan)
            ->where('cabar', 'bpjs')
            ->where('batch', $batch)
            ->whereNotNull('kelompok')
            ->get();
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

    // get total riil rumah sakit
    function getRincian($pasien)
    {
        $rincian = JmRincian::where('jm_pasien_id', $pasien->id)->first();

        $total =
            $rincian->prosedur_non_bedah +
            // $rincian->prosedur_bedah +
            ceil(($rincian->konsultasi * 70) / 100) +
            ceil(($rincian->tenaga_ahli * 80) / 100) +  // rincian rawat jalan, 20% nya adalah dokter, dengan assumsi max : spesialis 3x99000 dan umum 2x33000
            $rincian->keperawatan +
            $rincian->penunjang +
            $rincian->radiologi +
            $rincian->laboratorium +
            $rincian->pelayanan_darah +
            $rincian->rehabilitasi +
            $rincian->kamar_akomodasi +
            $rincian->rawat_intensif +
            $rincian->obat +
            $rincian->alkes +
            $rincian->bmhp +
            $rincian->sewa_alat +
            $rincian->obat_kronis +
            $rincian->obat_kemo;

        $data = [
            'riil_rs' =>  $total,
            'chosaring' => $rincian->chosaring

        ];
        return json_decode(json_encode($data));
    }


    /** 
     * HITUNG JASA RAWAT INAP BPJS
     */
    public $bulan_ri;
    public $batch_ri;
    function sumbitProcessRanap()
    {
        $this->validate(['bulan_ri' => 'required', 'batch_ri' => 'required']);

        // split tahun bulan
        [$tahun, $bulan] = explode('-', $this->bulan_ri);

        // get pasien data
        $pasiens = $this->getPasien($tahun, $bulan, $pelayanan = 'ranap', $this->batch_ri);

        // each by pasien
        $hasError = false;
        foreach ($pasiens as $pasien) {
            // try hitung dan rekap
            try {
                $dokter = $this->getDokter($pasien);
                // prosentase Calc
                $prosentase = $this->calcProsentaseRanap($pasien);

                // kelompok jasa
                $kelompok = $pasien->kelompok;
                $this->calcJasaDokterRanap($prosentase, $dokter, $kelompok);
            } catch (Throwable $e) {
                $errors = $e->getMessage();

                // toast
                $this->toast()->error('Failed!', "Error : " . $errors)->send();
                $hasError = true;
                continue;
            }
        }

        // toast
        if (!$hasError) {
            # code...
            $this->toast()->success('Success!', 'Proses hitung jasa selesai.!')->send();
            $this->downloadableRanap = true;
        }
    }


    // kalkulasi perhitungan jasa ranap sesuai prosentase
    private function calcProsentaseRanap($pasien)
    {
        $rincian = $this->getRincian($pasien);

        // get total rincian
        $total_billing = $rincian->riil_rs;
        $chosaring = $rincian->chosaring;

        $klaim = $pasien->disetujui;
        $jasa = ceil((($klaim + $chosaring) * 38) / 100);
        $rincian = $total_billing + $jasa;
        $klaim_rincian = $klaim - $rincian;

        // jasa pelayanan 38%
        $j_38 = $jasa;
        if ($klaim_rincian < 0) {
            $j_38 = $jasa + $klaim_rincian;
        }

        // jasa rs
        $j_rs = ceil(($j_38 * 10) / 100);
        $j_medis = 0;
        $j_anastesi = 0;
        $j_penata = 0;
        $j_medis = 0;
        $j_resus = 0;
        $j_pekerja = 0;
        $j_sppdkgh = 0;
        $j_umum_s = 0;

        switch ($pasien->kelompok) {
            case 'ri_no':
                $j_medis = ceil(($j_38 * 36.21) / 100);
                $j_pekerja = ceil(($j_38 * 53.79) / 100);
                break;

            case 'ri_op':
                $j_medis = ceil(($j_38 * 59) / 100);
                $j_anastesi = ceil(($j_38 * 19.29) / 100);
                $j_penata = ceil(($j_38 * 5.4) / 100);
                $j_pekerja = ceil(($j_38 * 6.31) / 100);
                break;

            case 'ri_mata':
                $j_medis = ceil(($j_38 * 59.29) / 100);
                $j_anastesi = ceil(($j_38 * 18) / 100);
                $j_penata = ceil(($j_38 * 5.87) / 100);
                $j_pekerja = ceil(($j_38 * 6.85) / 100);
                break;

            case 'ri_partus':
                $j_medis  = ceil(($j_38 * 50) / 100);
                $j_pekerja  = ceil(($j_38 * 40) / 100);
                break;


            case 'ri_sc':
                $j_medis  = ceil(($j_38 * 55.38) / 100);
                $j_anastesi = ceil(($j_38 * 18) / 100);
                $j_penata  = ceil(($j_38 * 5.87) / 100);
                $j_resus  = ceil(($j_38 * 3.91) / 100);
                $j_pekerja  = ceil(($j_38 * 6.85) / 100);
                break;

            case 'ri_curet':
                $j_medis  = ceil(($j_38 * 59.7) / 100);
                $j_penata  = ceil(($j_38 * 5.9) / 100);
                $j_pekerja  = ceil(($j_38 * 24.4) / 100);
                break;

            case 'ri_hd':
                $jasa_hd = 884000;

                // per 1 februari 2024
                $pelayanan_hd = ceil(($jasa_hd * 11) / 100);
                $j_umum_s = ceil(($pelayanan_hd * 15) / 100);
                $j_sppdkgh = ceil(($pelayanan_hd * 15) / 100);

                $sisa = $j_38 - ($j_umum_s + $j_sppdkgh);

                $j_rs = 0;
                if ($sisa > 0) {
                    $j_rs = ceil(($sisa * 10) / 100);
                    $j_medis = ceil(($sisa * 36.21) / 100);
                    $j_pekerja = ceil(($sisa * 53.79) / 100);
                }
                break;

            default:
                # code...
                break;
        }
        $data = [
            'total_billing' => $total_billing,
            'chosaring' => $chosaring,
            'jasa_p' => $jasa,
            'klaim_min_rincian' => $klaim_rincian,
            'jasa_pelayanan' => $j_38,
            'jasa_rs' => $j_rs,
            'jasa_medis' => $j_medis,
            'jasa_operator' => $j_medis,
            'jasa_anastesi' => $j_anastesi,
            'jasa_penata' => $j_penata,
            'jasa_resus' => $j_resus,
            'jasa_pekerja' => $j_pekerja,
            'jasa_sppdkgh' => $j_sppdkgh,
            'jasa_um_sertifikat' => $j_umum_s
        ];
        $prosentase = JmProsentase::updateOrCreate(
            ['jm_pasien_id' => $pasien->id],
            $data
        );

        return $prosentase;
    }

    // kalkulasi jasa per dokter sesuai status
    private function calcJasaDokterRanap($prosentase, $dokter, $kelompok)
    {
        // [01] delete jasa per dokter
        JmJasa::where('jm_prosentase_id', $prosentase->id)->delete();

        // [02] Get total visit spesialis & umum
        if ($kelompok === 'ri_no' || $kelompok === 'ri_hd') {
            // [02.01] kelompok Non Operatif & HD, get total visite spesialis seluruhnya,
            $totalSp = $dokter->where('status', 'sp')->sum('jumlah');
        } else {
            // [02.02] Selain kelompok diatas, remove data visite spesialis (sp) yang memiliki nama sama dengan dpjp
            $dokterFiltered = $dokter->reject(function ($dok) use ($dokter) {
                return $dok->status === 'sp' && $dokter->contains(function ($d) use ($dok) {
                    return $d->dokter === $dok->dokter && $d->status === 'dpjp';
                });
            });

            $totalSp = $dokterFiltered
                ->where('status', 'sp')
                ->sum('jumlah');
        }
        // [02.03] total visit dokter umum
        $totalUm = $dokter->where('status', 'um')->sum('jumlah');

        // [03] default jasa per dokter
        $status = null;
        $jasaSp = 0;
        $jasaUm = 0;
        $jasaAn = 0;
        $jasaDpjp = 0;
        $jasaSppdkgh = 0;
        $jasaUmSertifikat = 0;

        //[04] Hitung per kelompok jasa
        switch ($kelompok) {
            case 'ri_no':
                $status = 'RANAP NON OPERATIF';

                $jasa_per_dokter = 0;
                if ($prosentase->jasa_medis > 0) {
                    $jasa_per_dokter = $prosentase->jasa_medis / ((($totalSp * 2) + $totalUm) ?: 1);
                }
                $jasaSp = $jasa_per_dokter * 2;
                $jasaUm = $jasa_per_dokter;

                // remove dokter status == dpjp {Karna pembagian jasa by Visit}
                $dokter = $dokter->reject(function ($dok) {
                    return $dok->status === 'dpjp';
                });
                break;

            case 'ri_op':
                $status = 'RANAP OPERATIF';

                $totalJasaVisit = ($totalSp * 60000) + ($totalUm * 30000);
                $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);
                //  Jika $jasa_medis > 0  dan total jasa visit tidak lebih dari 100% jasa dokter
                if ($prosentase->jasa_medis > 0) {
                    if ($visitPercentage  < 100) {
                        $jasaSp = 60000;
                        $jasaUm = 30000;
                        $jasaAn = $prosentase->jasa_anastesi - ceil(($totalJasaVisit * 50) / 100);
                        $jasaDpjp = $prosentase->jasa_medis - ceil(($totalJasaVisit * 50) / 100);
                    } else {
                        $jasaSp = 0;
                        $jasaUm = 0;
                        $jasaAn = $prosentase->jasa_anastesi;
                        $jasaDpjp = $prosentase->jasa_medis;
                    }
                }
                break;

            case 'ri_mata':
                $status = 'OPERATIF MATA';

                $totalJasaVisit = ($totalSp * 60000) + ($totalUm * 15000);
                $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);

                if ($prosentase->jasa_medis > 0) {
                    if ($visitPercentage  < 100) {
                        $jasaSp = 60000;
                        $jasaUm = 15000;
                        $jasaAn = $prosentase->jasa_anastesi - ceil(($totalJasaVisit * 50) / 100);
                        $jasaDpjp = $prosentase->jasa_medis - ceil(($totalJasaVisit * 50) / 100);
                    } else {
                        $jasaSp = 0;
                        $jasaUm = 0;
                        $jasaAn = $prosentase->jasa_anastesi;
                        $jasaDpjp = $prosentase->jasa_medis;
                    }
                }
                break;

            case 'ri_partus':
                $status = 'PARTUS';
                $totalJasaVisit = ($totalSp * 60000) + ($totalUm * 15000);
                $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);

                if ($prosentase->jasa_medis > 0) {
                    if ($visitPercentage < 100) {
                        $jasaSp = 60000;
                        $jasaUm = 15000;
                        $jasaDpjp = $prosentase->jasa_medis - $totalJasaVisit;
                    } else {
                        $jasaSp = 0;
                        $jasaUm = 0;
                        $jasaDpjp = $prosentase->jasa_medis;
                    }
                }
                break;

            case 'ri_sc':
                $status = 'SC';
                $totalJasaVisit = ($totalSp * 60000) + ($totalUm * 15000);
                $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);

                if ($prosentase->jasa_medis > 0) {
                    if ($visitPercentage < 100) {
                        $jasaSp = 60000;
                        $jasaUm = 15000;
                        $jasaAn = $prosentase->jasa_anastesi - ceil(($totalJasaVisit * 50) / 100);
                        $jasaDpjp = $prosentase->jasa_medis - ceil(($totalJasaVisit * 50) / 100);
                    } else {
                        $jasaSp = 0;
                        $jasaUm = 0;
                        $jasaAn = $prosentase->jasa_anastesi;
                        $jasaDpjp = $prosentase->jasa_medis;
                    }
                }
                break;

            case 'ri_curet':
                $status = 'CURET';
                $totalJasaVisit = ($totalSp * 60000) + ($totalUm * 15000);
                $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);

                if ($prosentase->jasa_medis > 0) {
                    if ($visitPercentage < 100) {
                        $jasaSp = 60000;
                        $jasaUm = 15000;
                        $jasaDpjp = $prosentase->jasa_medis - $totalJasaVisit;
                    } else {
                        $jasaSp = 0;
                        $jasaUm = 0;
                        $jasaDpjp = $prosentase->jasa_medis;
                    }
                }

                break;

            case 'ri_hd':
                $status = 'RANAP HD';

                $jasa_per_dokter = 0;
                if ($prosentase->jasa_medis > 0) {
                    $jasa_per_dokter = $prosentase->jasa_medis / (($totalSp * 2 + $totalUm) ?: 1);
                }
                if ($prosentase->jasa_medis > 0) {
                    $jasaSp = $jasa_per_dokter * 2;
                    $jasaUm = $jasa_per_dokter;
                    $jasaUmSertifikat = $prosentase->jasa_um_sertifikat;
                    $jasaSppdkgh = $prosentase->jasa_sppdkgh;
                }

                // remove dokter status == dpjp {Karna pembagian jasa by Visit}
                $dokter = $dokter->reject(function ($dok) {
                    return $dok->status === 'dpjp';
                });
                break;


            default:
                # code...
                break;
        }

        // [05] each data visit dokter
        foreach ($dokter as $key => $dok) {
            $jasaDokter = 0;
            $visit = $dok->jumlah;
            $SpIsDpjp = false;


            // [05.01] decide masing-masing dokter mendpatkan jasa yang mana.
            if ($dok->status === 'sp') {
                // hapus nama dokter yg memiliki nama sama dengan dpjp
                foreach ($dokter as $entry) {
                    if ($entry->status === 'dpjp' &&  $entry->dokter === $dok->dokter) {
                        $SpIsDpjp = true;
                        unset($dokter[$key]);
                        break;
                    }
                }

                if (!$SpIsDpjp) {
                    $jasaDokter = $jasaSp * $visit;
                }
            } else if ($dok->status === 'um') {
                $jasaDokter = $jasaUm * $visit;
            } else if ($dok->status === 'an') {
                $jasaDokter = $jasaAn;
            } else if ($dok->status === 'dpjp') {
                $jasaDokter = $jasaDpjp;
            } else if ($dok->status === 'um_s') {
                $jasaDokter = $jasaUmSertifikat;
            } else if ($dok->status === 'sppdkgh') {
                $jasaDokter = $jasaSppdkgh;
            }

            // [06] insert data jasa dokter
            if (!$SpIsDpjp) {
                $data = [
                    'jm_prosentase_id' => $prosentase->id,
                    'dokter' => $dok->dokter,
                    'status' => $status,
                    'jasa' => $jasaDokter,
                ];
                JmJasa::create($data);
            }
        }
    }

    // Download hasil rekap ranap
    public $downloadableRanap = true;
    public $pilih_download_ranap;
    public function downloadRanap()
    {
        $this->validate(['bulan_ri' => 'required', 'batch_ri' => 'required']);

        $periode = $this->bulan_ri;
        $cabar = 'bpjs';
        $kelompok = $this->pilih_download_ranap;
        $batch = $this->batch_ri;

        return Excel::download(
            new RanapExport($periode, $cabar, $kelompok, $batch),
            'Rekap Jasa Ranap ' . $periode . '.xlsx'
        );

        $this->toast()->success('Success!', 'Download berhasil.')->send();
    }

    function downloadTemplate($template)
    {
        $templateName = [
            'ranap' => 'Rawat Inap',
            'rajal' => 'Rawat Jalan'
        ];

        return Excel::download(
            new TemplateImportJasa($template),
            'Template' . $templateName[$template] . '.xlsx'
        );
    }


    /**
     * HITUNG JASA RAWAT JALAN
     */

    public $excelImportRajal;
    function importRajal()
    {
        $this->validate(['excelImportRajal' => 'required|mimes:xlsx,xls']);

        try {
            $file = $this->excelImportRajal->store('excelImportRajal');
            Excel::import(new RajalImport(), $file);

            // toast 
            $this->toast()->success('Success!', 'Import data berhasil.!')->send();
        } catch (Throwable $e) {
            $errors = $e->getMessage();

            // toast
            $this->toast()->error('Failed!', "Error : $errors")->send();
        }
    }


    public $bulan_rj;
    public $batch_rj;
    public $downloadableRajal = true;

    function submitProsesRajal()
    {
        $this->validate(['bulan_rj' => 'required', 'batch_rj' => 'required']);

        // split tahun bulan
        [$tahun, $bulan] = explode('-', $this->bulan_rj);

        // get pasien data
        $pasiens = $this->getPasien($tahun, $bulan, $pelayanan = 'rajal', $this->batch_rj);

        $hasError = false;
        foreach ($pasiens as $pasien) {
            try {
                $dokter = $this->getDokter($pasien);

                $this->calcProsentaseRajal($pasien, $dokter);
            } catch (Throwable $e) {
                $errors = $e->getMessage();

                // toast
                $this->toast()->error('Failed!', "Error : $errors")->send();
                $hasError = true;
                continue;
            }
        }

        if (!$hasError) {
            $this->toast()->success('Success', 'Proses Hitung Jasa Rajal Selesai.!')->send();
            $this->downloadableRajal = true;
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

    public $pilih_download_rajal;
    function downloadRajal()
    {
        $this->validate(['bulan_rj' => 'required', 'batch_rj' => 'required']);

        $periode = $this->bulan_rj;
        $cabar = 'bpjs';
        $kelompok  = $this->pilih_download_rajal;
        $batch = $this->batch_rj;

        return Excel::download(
            new RajalExport($periode, $cabar, $kelompok,  $batch),
            'Rekap Jasa Rajal ' . $periode . '.xlsx'
        );
    }

    function placeholder()
    {
        return view('components.skeleton');
    }

    public function render()
    {
        return view('livewire.jasmed.bpjs');
    }
}
