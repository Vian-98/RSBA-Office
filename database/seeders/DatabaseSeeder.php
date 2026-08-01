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
            DokterSeeder::class,           // Spesialisasi, 3 Koor Dokter (IGD/Rawat Inap/HD), user login
            SuperAdminSignatureSeeder::class,
            MenuSeeder::class,
            PerusahaanSeeder::class,
            WilayahSeeder::class,
            DummyDataSeeder::class,
            JadwalSeeder::class, // Master shift, aturan jadwal, bagian-shift
            PayrollSeeder::class,
            SdmPayrollGolonganMatrixSeeder::class,
            PayrollPph21ReferenceSeeder::class, // PTKP, TER A/B/C, and Article 17 reference tables
            KaryawanExcelSeeder::class, // Import 299 karyawan & buat tugas koordinator sdm
            DummyPayrollSlipSeeder::class, // Generate mock salary slips (February - July 2026)
            JadwalDummyJuniSeeder::class, // Generate draf jadwal kerja Juni 2026
            SkenarioTriRahayuSeeder::class, // Skenario absensi tes Tri Rahayu
            JadwalDuaTahunSeeder::class, // Generate jadwal dan absensi 2023 - 2024
            CutiBersamaSeeder::class, // Event Cuti Bersama
        ]);
    }
}
