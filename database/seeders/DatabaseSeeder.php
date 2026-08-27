<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for Production / Clean Master Data.
     */
    public function run(): void
    {
        // ── Production Master & Essential Seeders ──────────────────────────
        $mainSeeders = [
            MenuSeeder::class,                     // 1. System Navigation Menus
            PermissionSeeder::class,               // 2. Spatie CRUD Permissions
            SpecialPermissionSeeder::class,        // 3. Special & Functional Permissions
            UserSeeder::class,                     // 4. Super-Admin User & Role
            SuperAdminSignatureSeeder::class,      // 5. Super-Admin Digital Signature Certificate
            PerusahaanSeeder::class,               // 6. RS Bintang Amin Profile & Identity
            WilayahSeeder::class,                  // 7. Master Wilayah Indonesia (Provinsi, Kab, Kec, Desa)
            CutiJenisSeeder::class,                // 8. Master 4 Jenis Cuti Resmi
            SuratKategoriArsipSeeder::class,       // 9. Master Kategori Arsip Surat Dinas
            JadwalSeeder::class,                   // 10. Master 4 Shift Kerja & Aturan Dasar
        ];

        foreach ($mainSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}
