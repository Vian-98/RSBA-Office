<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SpecialPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for special / non-menu permissions.
     */
    public function run(): void
    {
        $specialPermissions = [
            // ── Global & Keamanan ──
            'super-admin-bypass',

            // ── Karyawan & Profil ──
            'tanda-tangan-digital',
            'export-karyawan',
            'edit-tgl-masuk-karyawan',
            'view-dokter',

            // ── Penggajian & Pajak ──
            'approve-kepegawaian-gaji',
            'approve-kepegawaian-gaji-pajak',
            'unlock-payroll-approved',

            // ── Surat & Cuti ──
            'approve-kepegawaian-cuti',
            'create-cuti-other-karyawan',
            'view-kepegawaian-cuti-bersama',
            'approve-kepegawaian-sp3',

            // ── Jadwal Kerja ──
            'approve-jadwal-kabid',
            'approve-jadwal-wadir',
            'manage-kepegawaian-master-aturan',

            // ── Jasa Medis & Akreditasi ──
            'jasmed-bpjs',
            'jasmed-jkmd',
            'jasmed-tunai',
            'verify-jasa',
            'assesor-akreditasi',
            'sekretariat-akreditasi',

            // ── Umum & Logistik ──
            'manage-umum-asset',
            'approve-umum-pengajuan',
            'approval-maintenance',
            'finish-opname-gudang',
            'terima-pembelian',
            'view-umum-maintenance-ticket-detail',

            // ── Monitoring ──
            'view-server-room-monitoring',
        ];

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
