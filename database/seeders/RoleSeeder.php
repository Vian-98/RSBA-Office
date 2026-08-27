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

        // Explicitly sync all permissions + bypass to Super-Admin
        $safeSync($superAdmin, array_unique(array_merge($allPermissions, ['super-admin-bypass', 'unlock-payroll-approved'])));

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

        $wadirMedis = Role::firstOrCreate(['name' => 'Wadir-Medis-Keperawatan']);
        $wadirSdm   = Role::firstOrCreate(['name' => 'Wadir-SDM-Umum']);
        $wadirKeu   = Role::firstOrCreate(['name' => 'Wadir-Keuangan']);
        $direktur   = Role::firstOrCreate(['name' => 'Direktur']);

        $safeSync($kabid, $executivePermissions);
        $safeSync($wadir, $executivePermissions);
        $safeSync($wadirMedis, $executivePermissions);
        $safeSync($wadirSdm, $executivePermissions);
        $safeSync($wadirKeu, $executivePermissions);
        $safeSync($direktur, $executivePermissions);



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
        $safeSync($staffSdm, $sdmSyncedPermissions);

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
        $safeSync($bagianUmum, array_unique(array_merge($umumPermissions, $commonPermissions)));

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
        $safeSync($keuangan, array_unique(array_merge(
            $keuanganPermissions,
            $commonPermissions,
            [
                'view-kepegawaian-surat-sp3',
                'view-keuangan-kuitansi',
                'create-keuangan-kuitansi',
                'print-keuangan-kuitansi',
                'void-keuangan-kuitansi',
            ]
        )));

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
        $safeSync($administrasi, array_unique(array_merge($admPermissions, $commonPermissions)));

        // 5. Perencanaan & Evaluasi basic permissions
        $safeSync($perencanaan, $commonPermissions);

        // 6. IT permissions (Full Access like Super-Admin)
        $safeSync($staffIt, $allPermissions);

        // Common Guest / Staff Basic permissions
        $safeSync($guest, array_unique(array_merge(
            $commonPermissions,
            ['view-kepegawaian-jadwal-kerja']
        )));
        
        // 7. Bedah & UGD basic permissions
        $safeSync($staffBedah, $commonPermissions);
        $safeSync($staffUgd, $commonPermissions);

        // 8. Pajak permissions
        $pajakPermissions = [
            'view-dashboard',
            'view-dashboard-kamar',
            'view-dashboard-poli',
            'view-profile-jadwal-tugas-saya',
            'view-kepegawaian-gaji',
            'view-kepegawaian-gaji-index',
            'view-kepegawaian-gaji-detail',
            'approve-kepegawaian-gaji-pajak',
            'view-kepegawaian-master-aturan-pajak',
            'add-kepegawaian-master-aturan-pajak',
            'edit-kepegawaian-master-aturan-pajak',
            'delete-kepegawaian-master-aturan-pajak',
            'view-kepegawaian-karyawan',
            'edit-kepegawaian-karyawan',
            'export-karyawan',
            'view-dokter',
        ];
        $pajakRole = Role::firstOrCreate(['name' => 'Pajak']);
        $safeSync($pajakRole, $pajakPermissions);

        $safeSync($dokterRole, array_unique(array_merge($commonPermissions, ['view-kepegawaian-jadwal-kerja', 'view-dokter'])));

        $koordinatorPermissions = array_unique(array_merge($commonPermissions, [
            'view-kepegawaian-jadwal-kerja',
            'add-kepegawaian-jadwal-kerja',
            'edit-kepegawaian-jadwal-kerja',
            'delete-kepegawaian-jadwal-kerja',
            'view-kepegawaian-absensi',
            'view-kepegawaian-konfigurasi-jadwal',
            'view-kepegawaian-surat-cuti',
            'view-kepegawaian-surat-sp3',
            'view-kepegawaian-master-jadwal-shift',
            'view-kepegawaian-master-jadwal-aturan',
            'view-kepegawaian-master-ruangan-shift',
            'view-kepegawaian-master-bagian-koordinator',
        ]));

        $koordinatorRole = Role::firstOrCreate(['name' => 'Koordinator']);
        $safeSync($koordinatorRole, $koordinatorPermissions);

        $koorDokterRole = Role::firstOrCreate(['name' => 'Koordinator-Dokter']);
        $safeSync($koorDokterRole, array_unique(array_merge($koordinatorPermissions, ['view-dokter'])));
    }
}
