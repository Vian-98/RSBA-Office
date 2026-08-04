<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            SuperAdminSignatureSeeder::class,
            MenuSeeder::class,
            PerusahaanSeeder::class,
            WilayahSeeder::class,
            UmDataSeeder::class,
            UmumSeeder::class,
            JadwalSeeder::class, // Master shift, aturan jadwal, bagian-shift
            PayrollSeeder::class,
            SdmPayrollGolonganMatrixSeeder::class,
            PayrollPph21ReferenceSeeder::class, // PTKP, TER A/B/C, and Article 17 reference tables
            CutiBersamaSeeder::class, // Event Cuti Bersama
        ]);

    }
}

