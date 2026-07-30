<?php

namespace App\Imports;

use App\Models\JmDokter;
use App\Models\JmPasien;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RajalImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $pasien = JmPasien::where('sep', $row['sep'])->first();

            if ($pasien) {
                $pasien->update(['kelompok' => $row['kelompok']]);
                // add or change umum sertifikat
                if (isset($row['umum_sertifikat']) && !empty($row['umum_sertifikat'])) {
                    JmDokter::updateOrCreate(
                        [
                            'jm_pasien_id' => $pasien->id,
                            'status' => 'um_s'
                        ],
                        [
                            'dokter' => $row['umum_sertifikat'],
                            'jumlah' => 1,
                            'status' => 'um_s'
                        ]
                    );
                }

                //add or change dpjp hd
                if (isset($row['sppdkgh']) && !empty($row['sppdkgh'])) {
                    JmDokter::updateOrCreate(
                        [
                            'jm_pasien_id' => $pasien->id,
                            'status' => 'sppdkgh'
                        ],
                        [
                            'dokter' => $row['sppdkgh'],
                            'jumlah' => 1,
                            'status' => 'sppdkgh'
                        ]
                    );
                }

                // update dpjp hd
                if (isset($row['dpjp_hd']) && !empty($row['dpjp_hd'])) {
                    $pasien->update(['dpjp' => $row['dpjp_hd']]);
                }
            }
        }
    }
}
