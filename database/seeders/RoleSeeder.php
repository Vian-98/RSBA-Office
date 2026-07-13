<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super-Admin']);
        $staffSdm = Role::firstOrCreate(['name' => 'Staff-SDM']);
        $bagianUmum = Role::firstOrCreate(['name' => 'Bagian-Umum']);
        $keuangan = Role::firstOrCreate(['name' => 'Keuangan']);
        $administrasi = Role::firstOrCreate(['name' => 'Administrasi']);
        $guest = Role::firstOrCreate(['name' => 'Guest']);
        // Role 'Koordinator' dihapus — koordinator kini merupakan tugas tambahan
        // yang di-assign via tabel sdm_ruangan_koordinator, bukan role Spatie

        // Fetch all permissions currently in database
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $commonPermissions = ['view-dashboard', 'view-dashboard-kamar', 'view-profile-jadwal-tugas-saya'];
        
        // (Koordinator tidak lagi memerlukan permission khusus via Role)


        // 1. SDM permissions
        $sdmKeywords = ['kepegawaian', 'karyawan', 'dokter', 'cuti', 'sp3', 'jasmed', 'akreditasi', 'verifikasi', 'tanda-tangan-digital', 'export-karyawan', 'bagian', 'jabatan', 'ruangan', 'spesialis', 'surat', 'gaji', 'view-master'];
        $sdmPermissions = array_filter($allPermissions, function ($permission) use ($sdmKeywords) {
            foreach ($sdmKeywords as $keyword) {
                if (str_contains(strtolower($permission), strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
        $staffSdm->syncPermissions(array_unique(array_merge($sdmPermissions, $commonPermissions)));

        // 2. Umum permissions
        $umumKeywords = ['umum', 'supplier', 'kategori', 'satuan', 'penyimpanan', 'barang', 'pembelian', 'distribusi', 'gudang', 'asset', 'opname', 'maintenance', 'pengajuan', 'laporang'];
        $umumPermissions = array_filter($allPermissions, function ($permission) use ($umumKeywords) {
            foreach ($umumKeywords as $keyword) {
                if (str_contains(strtolower($permission), strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
        $bagianUmum->syncPermissions(array_unique(array_merge($umumPermissions, $commonPermissions)));

        // 3. Keuangan permissions
        $keuanganKeywords = ['keuangan', 'hutang', 'piutang', 'rekanan', 'coa', 'jurnal', 'akuntansi'];
        $keuanganPermissions = array_filter($allPermissions, function ($permission) use ($keuanganKeywords) {
            foreach ($keuanganKeywords as $keyword) {
                if (str_contains(strtolower($permission), strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
        $keuangan->syncPermissions(array_unique(array_merge($keuanganPermissions, $commonPermissions)));

        // 4. Administrasi permissions
        $admKeywords = ['administrasi', 'pasien', 'registrasi'];
        $admPermissions = array_filter($allPermissions, function ($permission) use ($admKeywords) {
            foreach ($admKeywords as $keyword) {
                if (str_contains(strtolower($permission), strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
        $administrasi->syncPermissions(array_unique(array_merge($admPermissions, $commonPermissions)));

        // 5. Guest permissions
        $guest->syncPermissions($commonPermissions);
    }
}
