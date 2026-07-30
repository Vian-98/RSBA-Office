<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use App\Models\Sdm\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;

class KaryawanImport implements ToModel, WithHeadingRow, WithChunkReading, WithSkipDuplicates
{
    public function model(array $row)
    {
        return new Karyawan([
            'nip' => $row['nip'],
            'nik' => $row['nik'],
            'nama' => $row['nama'],
            'tgl_lahir' => $this->transformDate($row['tgl_lahir']),
            'hp' => $row['hp'],
            'agama' => $row['agama'],
            'status' => $row['status'],
            'tgl_masuk' => $this->transformDate($row['tgl_masuk']),
        ]);
    }


    private function transformDate($value, $format = 'Y-m-d')
    {
        // Case 1: If it's a Carbon-parsable date string
        if (!is_numeric($value)) {
            return Carbon::parse($value)->format($format);
        }

        // Case 2: If it's an Excel serial number
        return Carbon::instance(Date::excelToDateTimeObject($value))->format($format);
    }


    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2;
    }
}
