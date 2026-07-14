<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed global settings
        $settings = [
            ['key' => 'umk', 'value' => '3000000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'persen_tunjangan_tetap', 'value' => '80', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'persen_tunjangan_absensi', 'value' => '20', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'potongan_telat_per_menit', 'value' => '1000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tarif_lembur_per_menit', 'value' => '2000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'toleransi_telat_menit', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($settings as $setting) {
            DB::table('sdm_payroll_settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'created_at' => $setting['created_at'], 'updated_at' => $setting['updated_at']]
            );
        }

        // 2. Seed default Golongan allowances (1 to 15)
        // Golongan 15 (lowest): Rp 0
        // Golongan 1 (highest): Rp 700,000 (increments by Rp 50,000 for each grade up)
        for ($gol = 1; $gol <= 15; $gol++) {
            $tunjangan = (15 - $gol) * 50000;
            DB::table('sdm_payroll_golongans')->updateOrInsert(
                ['golongan' => $gol],
                ['tunjangan_golongan' => $tunjangan, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Seed default allowance types
        $types = [
            ['nama' => 'Tunjangan Transport', 'keterangan' => 'Tunjangan biaya transportasi bulanan.'],
            ['nama' => 'Tunjangan Makan', 'keterangan' => 'Tunjangan biaya makan karyawan.'],
            ['nama' => 'Tunjangan Kinerja', 'keterangan' => 'Tunjangan berdasarkan kinerja bulanan.'],
        ];

        foreach ($types as $type) {
            DB::table('sdm_payroll_allowance_types')->updateOrInsert(
                ['nama' => $type['nama']],
                ['keterangan' => $type['keterangan'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Seed default alokasi tunjangan 25% UMK
        $allocations = [
            ['nama' => 'Tunjangan Tetap', 'persen' => 80.0, 'is_absensi' => 0],
            ['nama' => 'Tunjangan Absensi', 'persen' => 20.0, 'is_absensi' => 1],
        ];

        foreach ($allocations as $alloc) {
            DB::table('sdm_payroll_allowance_allocations')->updateOrInsert(
                ['nama' => $alloc['nama']],
                ['persen' => $alloc['persen'], 'is_absensi' => $alloc['is_absensi'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
