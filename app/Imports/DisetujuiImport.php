<?php

namespace App\Imports;

use App\Models\JmPasien;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class DisetujuiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // find data
        $jm_pasien = JmPasien::where('sep', $row['sep'])->first();

        // if null
        if (!$jm_pasien) {
            return null;
        }

        // update disetujui
        $jm_pasien->disetujui = $row['disetujui'];
        $jm_pasien->batch = $row['batch'];

        // simpan updated
        $jm_pasien->save();

        // return if true
        return $jm_pasien;
    }
}
