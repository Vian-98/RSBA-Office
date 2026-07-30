<?php

namespace App\Services;

use App\Models\JmDokter;
use App\Models\JmJasa;
use App\Models\JmProsentase;
use App\Models\JmRincian;
use App\Models\JmPasien;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class JasaMedisBpjsService
{
    // Visit fee constants
    protected int $visitFeeSpesialis = 60000;
    protected int $visitFeeUmum = 30000;
    protected int $visitFeeUmumForMataPartusSc = 15000;

    /**
     * Process jasa medis for a single patient.
     * All operations wrapped in a transaction.
     */
    public function processPasien(JmPasien $pasien): void
    {
        DB::transaction(function () use ($pasien) {
            $dokters = $this->getDokter($pasien);
            $prosentase = $this->calcProsentaseRanap($pasien);
            $this->calcJasaDokterRanap($prosentase, $dokters, $pasien->kelompok);
        });
    }

    // ------------------------------------------------------------------------
    // Dokter Collection
    // ------------------------------------------------------------------------

    public function getDokter(JmPasien $pasien): Collection
    {
        $dokters = $pasien->relationLoaded('jmDokter')
            ? $pasien->jmDokter
            : JmDokter::where('jm_pasien_id', $pasien->id)->get();

        // skip push dpjp pada kelompok [ri_no, ri_hd]
        $skipPushDpjp = in_array($pasien->kelompok, ['ri_no', 'ri_hd']);

        if (!$skipPushDpjp && !$dokters->contains('status', 'dpjp')) {
            $dokters->push(new JmDokter([
                'dokter' => $pasien->dpjp,
                'status' => 'dpjp',
                'jumlah' => 1,
            ]));
        }

        return $dokters;
    }

    // ------------------------------------------------------------------------
    // Prosentase Calculation
    // ------------------------------------------------------------------------

    public function calcProsentaseRanap(JmPasien $pasien): JmProsentase
    {
        $rincian = $this->getRincian($pasien);
        $j38 = $this->calculateJasaPelayanan38($pasien, $rincian);

        $components = $this->calculateJasaComponents($pasien->kelompok, $j38, $rincian);

        return JmProsentase::updateOrCreate(
            ['jm_pasien_id' => $pasien->id],
            array_merge([
                'total_billing'      => $rincian->riil_rs,
                'chosaring'          => $rincian->chosaring,
                'jasa_p'             => $this->calculateJasaP($pasien, $rincian),
                'klaim_min_rincian'  => $this->calculateKlaimMinRincian($pasien, $rincian),
                'jasa_pelayanan'     => $j38,
            ], $components)
        );
    }

    /**
     * Calculate 38% jasa pelayanan (j_38).
     */
    private function calculateJasaPelayanan38(JmPasien $pasien, stdClass $rincian): int
    {
        // $klaim = $pasien->disetujui + $rincian->chosaring;
        // $jasa = (int) ceil(($klaim * 38) / 100);
        // $rincianTotal = $rincian->riil_rs + $jasa;
        // $klaimRincian = $klaim - $rincianTotal;

        $jasa = $this->calculateJasaP($pasien, $rincian);
        $klaimRincian = $this->calculateKlaimMinRincian($pasien, $rincian);

        return ($klaimRincian < 0) ? $jasa + $klaimRincian : $jasa;
    }

    /**
     * Calculate jasa P (jasa_p).
     */
    private function calculateJasaP(JmPasien $pasien, stdClass $rincian): int
    {
        return (int) ceil((($pasien->disetujui + $rincian->chosaring) * 38) / 100);
    }

    /**
     * Calculate klaim minus rincian.
     */
    private function calculateKlaimMinRincian(JmPasien $pasien, stdClass $rincian): int
    {
        $klaim = $pasien->disetujui + $rincian->chosaring;
        $jasa = $this->calculateJasaP($pasien, $rincian);
        return $klaim - ($rincian->riil_rs + $jasa);
    }

    /**
     * Distribute j_38 into various components based on kelompok.
     */
    private function calculateJasaComponents(string $kelompok, int $j38): array
    {
        // Default values
        $jRs       = (int) ceil(($j38 * 10) / 100);
        $jMedis    = 0;
        $jAnastesi = 0;
        $jPenata   = 0;
        $jResus    = 0;
        $jPekerja  = 0;
        $jSppdkgh  = 0;
        $jUmumS    = 0;
        $jDpjpHD   = 0;

        switch ($kelompok) {
            case 'ri_no':
                $jMedis   = (int) ceil(($j38 * 36.21) / 100);
                $jPekerja = (int) ceil(($j38 * 53.79) / 100);
                break;

            case 'ri_op':
                $jMedis    = (int) ceil(($j38 * 59)    / 100);
                $jAnastesi = (int) ceil(($j38 * 19.29) / 100);
                $jPenata   = (int) ceil(($j38 * 5.4)   / 100);
                $jPekerja  = (int) ceil(($j38 * 6.31)  / 100);
                break;

            case 'ri_mata':
                $jMedis    = (int) ceil(($j38 * 59.29) / 100);
                $jAnastesi = (int) ceil(($j38 * 18)    / 100);
                $jPenata   = (int) ceil(($j38 * 5.87)  / 100);
                $jPekerja  = (int) ceil(($j38 * 6.85)  / 100);
                break;

            case 'ri_partus':
                $jMedis   = (int) ceil(($j38 * 50) / 100);
                $jPekerja = (int) ceil(($j38 * 40) / 100);
                break;

            case 'ri_sc':
                $jMedis    = (int) ceil(($j38 * 55.38) / 100);
                $jAnastesi = (int) ceil(($j38 * 18)    / 100);
                $jPenata   = (int) ceil(($j38 * 5.87)  / 100);
                $jResus    = (int) ceil(($j38 * 3.91)  / 100);
                $jPekerja  = (int) ceil(($j38 * 6.85)  / 100);
                break;

            case 'ri_curet':
                $jMedis   = (int) ceil(($j38 * 59.7) / 100);
                $jPenata  = (int) ceil(($j38 * 5.9)  / 100);
                $jPekerja = (int) ceil(($j38 * 24.4) / 100);
                break;

            case 'ri_hd':
                // HD
                return $this->calculateHdComponents($j38);
        }

        return [
            'jasa_rs'            => $jRs,
            'jasa_medis'         => $jMedis,
            'jasa_operator'      => $jMedis,
            'jasa_anastesi'      => $jAnastesi,
            'jasa_penata'        => $jPenata,
            'jasa_resus'         => $jResus,
            'jasa_pekerja'       => $jPekerja,
            'jasa_sppdkgh'       => $jSppdkgh,
            'jasa_um_sertifikat' => $jUmumS,
            'jasa_dpjp_hd'       => $jDpjpHD,
        ];
    }

    /**
     * calculation HD group.
     */
    private function calculateHdComponents(int $j38): array
    {
        $jasaHd = 884000;
        // per 1 februari 2024
        $pelayananHd = (int) ceil(($jasaHd * 11) / 100);

        $jUmumS  = (int) ceil(($pelayananHd * 15) / 100);
        $jSppdkgh = (int) ceil(($pelayananHd * 15) / 100);
        $jDpjpHD = (int) ceil(($pelayananHd * 35) / 100);

        $jRs = 0;
        $jMedis = 0;
        $jPekerja = 0;

        if ($j38 > 0) {
            $sisa = $j38 - ($jUmumS + $jSppdkgh + $jDpjpHD);
            if ($sisa > 0) {
                $jRs      = (int) ceil(($sisa * 10)    / 100);
                $jMedis   = (int) ceil(($sisa * 36.21) / 100);
                $jPekerja = (int) ceil(($sisa * 53.79) / 100);
            }
        }

        return [
            'jasa_rs'            => $jRs,
            'jasa_medis'         => $jMedis,
            'jasa_operator'      => $jMedis,
            'jasa_anastesi'      => 0,
            'jasa_penata'        => 0,
            'jasa_resus'         => 0,
            'jasa_pekerja'       => $jPekerja,
            'jasa_sppdkgh'       => $jSppdkgh,
            'jasa_um_sertifikat' => $jUmumS,
            'jasa_dpjp_hd'       => $jDpjpHD,
        ];
    }

    // ------------------------------------------------------------------------
    // PEMBAGIAN JASA KE DOKTER
    // ------------------------------------------------------------------------

    public function calcJasaDokterRanap(JmProsentase $prosentase, Collection $dokter, string $kelompok): void
    {
        // Delete old records
        JmJasa::where('jm_prosentase_id', $prosentase->id)->delete();

        // Calculate total dokter visit 
        $totalVisit = $this->calculateVisitTotals($dokter, $kelompok);

        // Determine fee values and status based on kelompok
        $calculation = $this->prepareDoctorCalculation($prosentase, $totalVisit, $kelompok);

        // Build insert data
        $insertData = $this->buildJasaDokterData($prosentase->id, $dokter, $calculation);

        // Bulk insert
        if (!empty($insertData)) {
            $insertData = array_map(fn($item) => array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $insertData);

            JmJasa::insert($insertData);
        }
    }

    /**
     * Calculate total visit Spesialis and Umum .
     */
    private function calculateVisitTotals(Collection $dokter, string $kelompok): object
    {
        // 01. Total Visit Dokter Spesialis
        // Kelompok Non Operatif & HD, get total visite spesialis seluruhnya,
        if (in_array($kelompok, ['ri_no', 'ri_hd'])) {
            $totalSp = $dokter->where('status', 'sp')->sum('jumlah');
        } else {
            // Selain kelompok HD dan Non OP, remove data visite spesialis (sp) yang memiliki nama sama dengan dpjp
            $filtered = $dokter->reject(function ($dok) use ($dokter) {
                return $dok->status === 'sp' && $dokter->contains(function ($d) use ($dok) {
                    return $d->dokter === $dok->dokter && $d->status === 'dpjp';
                });
            });

            $totalSp = $filtered->where('status', 'sp')->sum('jumlah');
        }

        // 02. Total Visit Dokter Umum
        $totalUm = $dokter->where('status', 'um')->sum('jumlah');

        return (object) compact('totalSp', 'totalUm');
    }

    private function prepareDoctorCalculation(JmProsentase $prosentase, object $totalVisit, string $kelompok): array
    {
        $data = [
            'status'           => '',
            'jasaSp'           => 0,
            'jasaUm'           => 0,
            'jasaAn'           => 0,
            'jasaDpjp'         => 0,
            'jasaSppdkgh'      => 0,
            'jasaUmSertifikat' => 0,
            'jasaDpjpHd'       => 0,
        ];

        match ($kelompok) {
            'ri_no'     => $data = $this->pembagianNonOperatif($prosentase, $totalVisit, $data),
            'ri_op'     => $data = $this->pembagianOperatif($prosentase, $totalVisit, $data),
            'ri_mata'   => $data = $this->pembagianMata($prosentase, $totalVisit, $data),
            'ri_partus' => $data = $this->pembagianPartus($prosentase, $totalVisit, $data),
            'ri_sc'     => $data = $this->pembagianSc($prosentase, $totalVisit, $data),
            'ri_curet'  => $data = $this->pembagianCuret($prosentase, $totalVisit, $data),
            'ri_hd'     => $data = $this->pembagianHd($prosentase, $totalVisit, $data),
            default     => null
        };

        return $data;
    }

    // Pembagian Jasa Dokter Operatif (50 : 50) (Operator : Anastesi)
    private function calcPembagianOperatifBase(JmProsentase $prosentase, object $totalVisit, array $data, float $feeSpesialis, float $feeUmum): array
    {
        if ($prosentase->jasa_medis <= 0) {
            return $data;
        }

        $totalJasaVisit = ($totalVisit->totalSp * $feeSpesialis) + ($totalVisit->totalUm * $feeUmum);

        $visitPercentage  = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);
        $jasaVisit50  = (int) ceil($totalJasaVisit * 50 / 100);

        if ($visitPercentage < 100) {
            $data['jasaSp'] = $feeSpesialis;
            $data['jasaUm'] = $feeUmum;
            $data['jasaAn'] = $prosentase->jasa_anastesi - $jasaVisit50;
            $data['jasaDpjp'] = $prosentase->jasa_medis - $jasaVisit50;
        } else {
            $data['jasaAn']  = $prosentase->jasa_anastesi;
            $data['jasaDpjp']  = $prosentase->jasa_medis;
        }

        return $data;
    }

    private function calcPembagianPartusCuretBase(JmProsentase $prosentase, object $totalVisit, array $data, float $feeSpesialis, float $feeUmum): array
    {
        if ($prosentase->jasa_medis <= 0) {
            return $data;
        }

        $totalJasaVisit = ($totalVisit->totalSp * $feeSpesialis) + ($totalVisit->totalUm * $feeUmum);
        $visitPercentage = ($totalJasaVisit * 100) / ($prosentase->jasa_medis ?: 1);

        if ($visitPercentage < 100) {
            $data['jasaSp'] = $feeSpesialis;
            $data['jasaUm'] = $feeUmum;
            $data['jasaDpjp'] = $prosentase->jasa_medis - $totalJasaVisit;
        } else {
            $data['jasaDpjp'] = $prosentase->jasa_medis;
        }

        return $data;
    }

    private function pembagianNonOperatif(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status']  = 'RANAP NON OPERATIF';

        if ($prosentase->jasa_medis > 0) {
            $jasaPerVisit = $this->hitungPerVisit($prosentase->jasa_medis, $totalVisit->totalSp, $totalVisit->totalUm);

            $data['jasaSp'] = $jasaPerVisit * 2;
            $data['jasaUm'] = $jasaPerVisit;
        }

        return $data;
    }

    private function pembagianOperatif(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status'] = "RANAP OPERATIF";
        return $this->calcPembagianOperatifBase(
            $prosentase,
            $totalVisit,
            $data,
            $this->visitFeeSpesialis,
            $this->visitFeeUmum
        );
    }

    private function pembagianMata(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status'] = "OPERATIF MATA";
        return $this->calcPembagianOperatifBase(
            $prosentase,
            $totalVisit,
            $data,
            $this->visitFeeSpesialis,
            $this->visitFeeUmumForMataPartusSc
        );
    }

    private function pembagianPartus(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status'] = "PARTUS";
        return $this->calcPembagianPartusCuretBase(
            $prosentase,
            $totalVisit,
            $data,
            $this->visitFeeSpesialis,
            $this->visitFeeUmumForMataPartusSc,
        );
    }

    private function pembagianSc(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status']  = "SC";
        return $this->calcPembagianOperatifBase(
            $prosentase,
            $totalVisit,
            $data,
            $this->visitFeeSpesialis,
            $this->visitFeeUmumForMataPartusSc
        );
    }

    private function pembagianCuret(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status'] = "CURET";
        return $this->calcPembagianPartusCuretBase(
            $prosentase,
            $totalVisit,
            $data,
            $this->visitFeeSpesialis,
            $this->visitFeeUmumForMataPartusSc
        );
    }

    private function pembagianHd(JmProsentase $prosentase, object $totalVisit, array $data): array
    {
        $data['status'] = "RANAP HD";
        if ($prosentase->jasa_medis > 0) {
            $jasaPerVisit              = $this->hitungPerVisit($prosentase->jasa_medis, $totalVisit->totalSp, $totalVisit->totalUm);
            $data['jasaSp']            = $jasaPerVisit * 2;
            $data['jasaUm']            = $jasaPerVisit;
            $data['jasaUmSertifikat']  = $prosentase->jasa_um_sertifikat;
            $data['jasaSppdkgh']       = $prosentase->jasa_sppdkgh;
            $data['jasaDpjpHd']        = $prosentase->jasa_dpjp_hd;
        }

        return $data;
    }


    // Hitung Nilai per visit
    private function hitungPerVisit(float $jasaMedis, int $totalSp, int $totalUm): float
    {
        $divisor = (($totalSp * 2) + $totalUm) ?: 1;
        return (int) ceil($jasaMedis / $divisor);
    }


    // Total biaya yang dihabiskan untuk bayar visit
    private function hitungTotalJasaVisit(int $totalSp, int $totalUm, float $umFee): float
    {
        return ($totalSp * $this->visitFeeSpesialis) + ($totalUm * $umFee);
    }

    /**
     * Build the array of data to be inserted into JmJasa.
     */
    private function buildJasaDokterData(int $prosentaseId, Collection $dokter, array $calc): array
    {
        $insertData = [];

        foreach ($dokter as $key => $dok) {
            $jasaDokter = 0;
            $spIsDpjp = false;

            // hapus nama dokter yg memiliki nama sama dengan dpjp
            if ($dok->status === 'sp') {
                foreach ($dokter as $entry) {
                    if ($entry->status === 'dpjp' && $entry->dokter === $dok->dokter) {
                        $spIsDpjp = true;
                        unset($dokter[$key]);
                        break;
                    }
                }
            }

            //:: Calculate Jasa
            match ($dok->status) {
                'sp'        => $jasaDokter = !$spIsDpjp ? $calc['jasaSp'] * $dok->jumlah : 0,
                'um'        => $jasaDokter = $calc['jasaUm'] * $dok->jumlah,
                'an'        => $jasaDokter = $calc['jasaAn'],
                'dpjp'      => $jasaDokter = $calc['jasaDpjp'],
                'um_s'      => $jasaDokter = $calc['jasaUmSertifikat'],
                'sppdkgh'   => $jasaDokter = $calc['jasaSppdkgh'],
                'dpjp_hd'   => $jasaDokter = $calc['jasaDpjpHd'],
                default     => $jasaDokter = 0
            };


            if (!$spIsDpjp) {
                $insertData[] = [
                    'jm_prosentase_id' => $prosentaseId,
                    'dokter'           => $dok->dokter,
                    'status'           => $calc['status'],
                    'jasa'             => $jasaDokter,
                ];
            }
        }

        return $insertData;
    }

    // ------------------------------------------------------------------------
    // Rincian Helper
    // ------------------------------------------------------------------------

    private function getRincian(JmPasien $pasien): stdClass
    {
        $rincian = $pasien->relationLoaded('jmRincian')
            ? $pasien->jmRincian
            : JmRincian::where('jm_pasien_id', $pasien->id)->firstOrFail();

        $total = $rincian->prosedur_non_bedah
            // $rincian->prosedur_bedah +
            // ceil(($rincian->konsultasi * 70) / 100) +
            // $rincian->tenaga_ahli +
            // $rincian->keperawatan +
            + $rincian->penunjang
            + $rincian->radiologi
            + $rincian->laboratorium
            + $rincian->pelayanan_darah
            + $rincian->rehabilitasi
            + $rincian->kamar_akomodasi
            + $rincian->rawat_intensif
            + $rincian->obat
            + $rincian->alkes
            + $rincian->bmhp
            + $rincian->sewa_alat
            + $rincian->obat_kronis
            + $rincian->obat_kemo;



        return (object) [
            'riil_rs'   => $rincian->real_billing_jasa > 0 ? $rincian->real_billing_jasa : $total,
            'chosaring' => $rincian->chosaring,
        ];
    }
}
