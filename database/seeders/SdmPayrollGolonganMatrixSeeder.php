<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SdmPayrollGolonganMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grid = [
            'SMA/SMK' => [
                'urutan' => 1,
                'data' => [0 => 15, 3 => 15, 6 => 14, 9 => 13, 12 => 12, 15 => 11, 18 => 10, 21 => 9, 24 => 8, 27 => 7, 30 => 6, 33 => 5, 36 => 4, 39 => 3]
            ],
            'DIII/DIV' => [
                'urutan' => 2,
                'data' => [0 => 15, 3 => 14, 6 => 13, 9 => 12, 12 => 11, 15 => 10, 18 => 9, 21 => 8, 24 => 7, 27 => 6, 30 => 5, 33 => 4, 36 => 3, 39 => 2]
            ],
            'SI/Profesi' => [
                'urutan' => 3,
                'data' => [0 => 14, 3 => 13, 6 => 12, 9 => 11, 12 => 10, 15 => 9, 18 => 8, 21 => 7, 24 => 6, 27 => 5, 30 => 4, 33 => 3, 36 => 2, 39 => 1]
            ],
            'SII' => [
                'urutan' => 4,
                'data' => [0 => 13, 3 => 12, 6 => 11, 9 => 10, 12 => 9, 15 => 8, 18 => 7, 21 => 6, 24 => 5, 27 => 4, 30 => 3, 33 => 2, 36 => 1, 39 => 1]
            ],
        ];

        DB::table('sdm_payroll_golongan_matrix')->truncate();

        foreach ($grid as $kelompok => $row) {
            $inserts = [];
            foreach ($row['data'] as $masaKerja => $golongan) {
                $inserts[] = [
                    'kelompok_pendidikan' => $kelompok,
                    'masa_kerja_min' => $masaKerja,
                    'golongan' => $golongan,
                    'urutan_kelompok' => $row['urutan'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            DB::table('sdm_payroll_golongan_matrix')->insert($inserts);
        }
    }
}
