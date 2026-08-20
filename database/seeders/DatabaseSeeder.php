<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core / Master Seeders (Essential System & Reference Data)
        $mainSeeders = [
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            SuperAdminSignatureSeeder::class,
            MenuSeeder::class,
            PerusahaanSeeder::class,
            WilayahSeeder::class,
            CutiJenisSeeder::class,
            CutiBersamaSeeder::class,
            SuratKategoriArsipSeeder::class,
            UmDataSeeder::class,
            RuanganDummySeeder::class,
            JadwalSeeder::class,
            PayrollSeeder::class,
            SdmPayrollGolonganMatrixSeeder::class,
            PayrollPph21ReferenceSeeder::class,
            KaryawanExcelSeeder::class,
            StrukturOrganisasiSeeder::class,
        ];

        foreach ($mainSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}

