<?php

namespace App\Imports;

use App\Models\JmPasien;
use App\Models\JmRincian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RincianImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {


        if (!empty($row['sep'])) {
            $pasien = JmPasien::where('sep', $row['sep'])->first();
        } else {
            $pasien = JmPasien::where('no_rekmedis', $row['no_rekmedis'])
                ->where('tgl_checkout', $row['tgl_checkout'])
                ->first();
        }

        return
            DB::transaction(function () use ($row, $pasien) {
                JmRincian::updateOrCreate(
                    [
                        'jm_pasien_id' => $pasien->id
                    ],
                    [
                        'chosaring' => $row['chosaring'] ?? 0,
                        'prosedur_non_bedah' => $row['prosedur_non_bedah'] ?? 0,
                        'prosedur_bedah' => $row['prosedur_bedah'] ?? 0,
                        'konsultasi' => $row['konsultasi'] ?? 0,
                        'tenaga_ahli' => $row['tenaga_ahli'] ?? 0,
                        'keperawatan' => $row['keperawatan'] ?? 0,
                        'penunjang' => $row['penunjang'] ?? 0,
                        'radiologi' => $row['radiologi'] ?? 0,
                        'laboratorium' => $row['laboratorium'] ?? 0,
                        'pelayanan_darah' => $row['pelayanan_darah'] ?? 0,
                        'rehabilitasi' => $row['rehabilitasi'] ?? 0,
                        'kamar_akomodasi' => $row['kamar_akomodasi'] ?? 0,
                        'rawat_intensif' => $row['rawat_intensif'] ?? 0,
                        'obat' => $row['obat'] ?? 0,
                        'alkes' => $row['alkes'] ?? 0,
                        'bmhp' => $row['bmhp'] ?? 0,
                        'sewa_alat' => $row['sewa_alat'] ?? 0,
                        'obat_kronis' => $row['obat_kronis'] ?? 0,
                        'obat_kemo' => $row['obat_kemo'] ?? 0,
                        'real_billing_jasa' => $row['real_billing_jasa'] ?? 0
                    ]
                );
            });

        // return new JmRincian([]);
    }
}
