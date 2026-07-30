<?php

namespace App\Imports;

use App\Models\JmDokter;
use App\Models\JmPasien;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RanapImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        // each rows
        foreach ($rows as $row) {

            // get pasien by sep
            $pasien = JmPasien::where('sep', $row['sep'])->first();

            if ($pasien) {
                // update kelompok jasa
                $pasien->update(['kelompok' => $row['kelompok']]);

                // update umum sertifikat
                if (isset($row['umum_sertifikat']) && !empty($row['umum_sertifikat'])) {
                    JmDokter::updateOrCreate(
                        [
                            'jm_pasien_id' => $pasien->id,
                            'status' => 'um_s',
                        ],
                        [
                            'dokter' => $row['umum_sertifikat'],
                            'jumlah' => 1,
                            'status' => 'um_s'
                        ]
                    );
                }

                // update sppdkgh
                if (isset($row['sppdkgh']) && !empty($row['sppdkgh'])) {
                    JmDokter::updateOrCreate(
                        [
                            'jm_pasien_id' => $pasien->id,
                            'status' => 'sppdkgh',
                        ],
                        [
                            'dokter' => $row['sppdkgh'],
                            'jumlah' => 1,
                            'status' => 'sppdkgh'
                        ]
                    );
                }

                // Update DPJP HD
                if (isset($row['dpjp_hd']) && !empty($row['dpjp_hd'])) {
                    JmDokter::updateOrCreate(
                        [
                            'jm_pasien_id' => $pasien->id,
                            'status' => 'dpjp_hd',
                        ],
                        [
                            'dokter' => $row['dpjp_hd'],
                            'jumlah' => 1,
                            'status' => 'dpjp_hd'
                        ]
                    );
                }
            }
        }
    }
}
