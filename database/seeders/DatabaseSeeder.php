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
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            MenuSeeder::class,
            PerusahaanSeeder::class,
            WilayahSeeder::class,
            UmDataSeeder::class,
            UmumSeeder::class,
            PayrollSeeder::class,
            SdmPayrollGolonganMatrixSeeder::class,
            PayrollPph21ReferenceSeeder::class,
            CutiBersamaSeeder::class,
            StrukturOrganisasiSeeder::class,
        ];

        foreach ($mainSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }

        // Optional / Dummy Seeders (Only executed if present locally)
        $optionalDummySeeders = [
            'Database\Seeders\DokterSeeder',
            'Database\Seeders\SuperAdminSignatureSeeder',
            'Database\Seeders\DummyDataSeeder',
            'Database\Seeders\JadwalSeeder',
            'Database\Seeders\KaryawanExcelSeeder',
            'Database\Seeders\DummyPayrollSlipSeeder',
            'Database\Seeders\JadwalDummyJuniSeeder',
            'Database\Seeders\SkenarioTriRahayuSeeder',
            'Database\Seeders\JadwalDuaTahunSeeder',
            'Database\Seeders\DummySdmSeeder',
            'Database\Seeders\RuanganDummySeeder',
            'Database\Seeders\JadwalAbsensiJuli2026Seeder',
            'Database\Seeders\InpatientSeeder',
            'Database\Seeders\DokterPoliSeeder',
            'Database\Seeders\DokterAllPoliSeeder',
        ];

        foreach ($optionalDummySeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}

