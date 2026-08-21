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
        // Core / Master Seeders (Essential System, Super-Admin, Menus, Roles, & Reference Data)
        $mainSeeders = [
            PermissionSeeder::class,               // Spatie Permissions
            RoleSeeder::class,                     // System Roles
            UserSeeder::class,                     // Super-Admin Account
            SuperAdminSignatureSeeder::class,      // Super-Admin Digital Signature Certificate
            MenuSeeder::class,                     // System Navigation Menus
            PerusahaanSeeder::class,               // Profil RS Bintang Amin
            WilayahSeeder::class,                  // Master Wilayah Indonesia (Provinsi, Kab, Kec, Desa)
            CutiJenisSeeder::class,                // Master Jenis Cuti
            CutiBersamaSeeder::class,              // Master Cuti Bersama Nasional
            SuratKategoriArsipSeeder::class,       // Master Kategori Arsip Surat
            UmDataSeeder::class,                   // Master Logistik, Kategori & Satuan Barang
            JadwalSeeder::class,                   // Master Shift Dasar & Aturan Jadwal
            PayrollSeeder::class,                  // Master Komponen Tunjangan Dasar
            SdmPayrollGolonganMatrixSeeder::class, // Matriks Golongan Resmi
            PayrollPph21ReferenceSeeder::class,    // Master Aturan Pajak PPh 21 (TER & PTKP)
            StrukturOrganisasiSeeder::class,       // Master Bagan Jabatan & Bagian RSBA
        ];

        foreach ($mainSeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}
