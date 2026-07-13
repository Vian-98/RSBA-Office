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
            DummyDataSeeder::class,
            JadwalSeeder::class, // Master shift, aturan jadwal, bagian-shift
            KaryawanExcelSeeder::class, // Import 299 karyawan & buat tugas koordinator sdm
            SkenarioTriRahayuSeeder::class, // Skenario absensi tes Tri Rahayu
            JadwalDummyJuniSeeder::class, // Generate draf jadwal kerja Juni 2026
        ]);
    }
}
