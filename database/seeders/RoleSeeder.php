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
        $kabid = Role::firstOrCreate(['name' => 'Kepala-Bidang']);
        $wadir = Role::firstOrCreate(['name' => 'Wakil-Direktur']);
        $bagianUmum = Role::firstOrCreate(['name' => 'Bagian-Umum']);
        $keuangan = Role::firstOrCreate(['name' => 'Keuangan']);
        $administrasi = Role::firstOrCreate(['name' => 'Administrasi']);
        $perencanaan = Role::firstOrCreate(['name' => 'Perencanaan']);
        $staffIt = Role::firstOrCreate(['name' => 'IT']);
        $guest = Role::firstOrCreate(['name' => 'Guest']);
        $staffBedah = Role::firstOrCreate(['name' => 'Staff-Bedah']);
        $staffUgd = Role::firstOrCreate(['name' => 'Staff-UGD']);
        $pajak = Role::firstOrCreate(['name' => 'Pajak']);

        // Fetch all permissions currently in database
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $commonPermissions = ['view-dashboard', 'view-dashboard-kamar', 'view-profile-jadwal-tugas-saya', 'view-dashboard-poli'];

        // Helper to safely sync permissions ensuring permissions exist in DB
        $safeSync = function (Role $role, array $perms) {
            foreach ($perms as $p) {
                Permission::firstOrCreate(['name' => $p]);
            }
            $role->syncPermissions($perms);
        };

        // Assign comprehensive executive permissions to Kepala-Bidang & Wakil-Direktur
        $systemSettingsOnly = [
            'view-admin-settings-menu',
            'view-admin-settings-perusahaan',
            'view-admin-settings-role',
            'view-admin-settings-permission',
            'view-role-permission',
            'view-roles',
            'view-permissions',
            'view-menus',
            'add-menu',
            'view-settings'
        ];
        $executivePermissions = array_values(array_filter($allPermissions, fn($p) => !in_array($p, $systemSettingsOnly)));

        $safeSync($kabid, array_unique(array_merge($executivePermissions, ['approve-jadwal-kabid'])));
        $safeSync($wadir, array_unique(array_merge($executivePermissions, ['approve-jadwal-wadir'])));

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
        $sdmSyncedPermissions = collect(array_unique(array_merge($sdmPermissions, $commonPermissions)))
            ->toArray();
        $staffSdm->syncPermissions($sdmSyncedPermissions);

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
        $keuanganKeywords = ['keuangan', 'kas', 'rekening', 'transaksi', 'jurnal', 'coa', 'piutang', 'hutang', 'rekanan', 'akuntansi'];
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

        // 5. Perencanaan & Evaluasi basic permissions
        $perencanaan->syncPermissions($commonPermissions);

        // 6. IT permissions (Full Access like Super-Admin)
        $staffIt->syncPermissions($allPermissions);

        // Common Guest / Staff Basic permissions
        $guest->syncPermissions($commonPermissions);
        
        // 7. Bedah & UGD basic permissions
        $staffBedah->syncPermissions($commonPermissions);
        $staffUgd->syncPermissions($commonPermissions);

        // 8. Pajak permissions
        $pajakPermissions = [
            'view-dashboard',
            'view-dashboard-kamar',
            'view-dashboard-poli',
            'view-profile-jadwal-tugas-saya',
            'view-kepegawaian-gaji',
            'view-kepegawaian-gaji-index',
            'view-kepegawaian-gaji-detail',
            'view-kepegawaian-master-aturan-pajak',
            'view-kepegawaian-karyawan',
            'edit-kepegawaian-karyawan',
            'export-karyawan',
            'view-dokter',
        ];
        $pajakRole = Role::firstOrCreate(['name' => 'Pajak']);
        $pajakRole->syncPermissions($pajakPermissions);

        // 9. Dokter & Koordinator Dokter permissions
        $dokterRole = Role::firstOrCreate(['name' => 'Dokter']);
        $dokterRole->syncPermissions(array_unique(array_merge($commonPermissions, ['view-kepegawaian-jadwal-kerja'])));

        $koorDokterRole = Role::firstOrCreate(['name' => 'Koordinator-Dokter']);
        $koorDokterRole->syncPermissions(array_unique(array_merge($commonPermissions, ['view-kepegawaian-jadwal-kerja', 'view-kepegawaian-konfigurasi-jadwal'])));
    }
}
