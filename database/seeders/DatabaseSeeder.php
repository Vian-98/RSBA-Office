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
        // Core / Master Seeders (Always executed)
        $mainSeeders = [
            MenuSeeder::class,
            SpecialPermissionSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            SuperAdminSignatureSeeder::class,
            PerusahaanSeeder::class,
            WilayahSeeder::class,
            UmDataSeeder::class,
            UmumSeeder::class,
            JadwalSeeder::class,
            PayrollSeeder::class,
            SdmPayrollGolonganMatrixSeeder::class,
            PayrollPph21ReferenceSeeder::class,
            KaryawanExcelSeeder::class,
            DummyPayrollSlipSeeder::class,
            JadwalDummyJuniSeeder::class,
            CutiJenisSeeder::class,
            CutiBersamaSeeder::class,
            SkenarioTriRahayuSeeder::class,
            JadwalDuaTahunSeeder::class,
            StrukturOrganisasiSeeder::class,
        ];

        foreach ($mainSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}

