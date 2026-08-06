<?php

namespace Database\Seeders;

use App\Models\Surat\CutiJenis;
use Illuminate\Database\Seeder;

class CutiJenisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisList = [
            [
                'id' => 1,
                'nama' => 'Cuti Tahunan',
                'lama' => 12,
                'periode' => 'Y',
            ],
            [
                'id' => 2,
                'nama' => 'Izin Sakit',
                'lama' => 0,
                'periode' => 'Y',
            ],
            [
                'id' => 3,
                'nama' => 'Cuti Melahirkan',
                'lama' => 90,
                'periode' => 'L',
            ],
        ];

        foreach ($jenisList as $jenis) {
            CutiJenis::updateOrCreate(
                ['id' => $jenis['id']],
                [
                    'nama' => $jenis['nama'],
                    'lama' => $jenis['lama'],
                    'periode' => $jenis['periode'],
                ]
            );
        }

        CutiJenis::whereNotIn('id', [1, 2, 3])->delete();
    }
}
