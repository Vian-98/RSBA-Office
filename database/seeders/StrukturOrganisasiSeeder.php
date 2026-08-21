<?php

namespace Database\Seeders;

use App\Models\Sdm\Bagian;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanJabatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StrukturOrganisasiSeeder extends Seeder
{
    /**
     * Seed roles, jabatans, and users based on RSBA Organizational Structure chart.
     * Includes full hierarchy down to Wadir and sub-units under each Wadir.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Ensure permission cache is cleared
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Executive Shared Base Permissions (Dashboard, Schedule, Attendance, Leave & Surat Verification)
        $sharedExecutivePermissions = [
            'view-dashboard',
            'view-dashboard-kamar',
            'view-dashboard-poli',
            'view-profile-jadwal-tugas-saya',
            'view-kepegawaian-jadwal-kerja',
            'add-kepegawaian-jadwal-kerja',
            'edit-kepegawaian-jadwal-kerja',
            'delete-kepegawaian-jadwal-kerja',
            'approve-jadwal-wadir',
            'approve-jadwal-kabid',
            'view-kepegawaian-absensi',
            'view-kepegawaian-konfigurasi-jadwal',
            'view-kepegawaian-master-jadwal-shift',
            'add-kepegawaian-master-jadwal-shift',
            'edit-kepegawaian-master-jadwal-shift',
            'delete-kepegawaian-master-jadwal-shift',
            'view-kepegawaian-master-jadwal-aturan',
            'add-kepegawaian-master-jadwal-aturan',
            'edit-kepegawaian-master-jadwal-aturan',
            'view-kepegawaian-master-ruangan-shift',
            'add-kepegawaian-master-ruangan-shift',
            'edit-kepegawaian-master-ruangan-shift',
            'delete-kepegawaian-master-ruangan-shift',
            'view-kepegawaian-master-bagian-koordinator',
            'add-kepegawaian-master-bagian-koordinator',
            'edit-kepegawaian-master-bagian-koordinator',
            'delete-kepegawaian-master-bagian-koordinator',
            'view-surat',
            'view-cuti',
            'view-kepegawaian-surat-cuti',
            'tanda-tangan-digital',
            'view-surat-verification',
            'view-laporan',
        ];
        $commonPermissions = $sharedExecutivePermissions;


        // 1. Wadir Medis & Keperawatan (Medis, Keperawatan, Dokter, Jasmed, Akreditasi, Pasien)
        $wadirMedisPermissions = array_unique(array_merge($sharedExecutivePermissions, [
            'view-dokter',
            'view-spesialis',
            'view-kepegawaian-karyawan',
            'view-kepegawaian-master-ruangan',
            'view-jasmed',
            'jasmed-bpjs',
            'jasmed-jkmd',
            'view-kepegawaian-akreditasi',
            'assesor-akreditasi',
            'view-administrasi-pasien-index',
            'view-administrasi-registrasi-index',
            'view-kepegawaian-gaji',
            'view-kepegawaian-gaji-rekap',
            'view-kepegawaian-laporan',
        ]));

        // 2. Wadir SDM & Umum (FULL ACCESS UNTUK SELURUH MODUL SDM & KEPEGAWAIAN + LOGISTIK/UMUM)
        $wadirSdmPermissions = array_unique(array_merge($sharedExecutivePermissions, [
            // Full SDM & Kepegawaian Permissions
            'view-karyawan', 'view-kepegawaian-karyawan', 'edit-kepegawaian-karyawan', 'export-karyawan', 'edit-karyawan',
            'view-bagian', 'view-jabatan', 'view-ruangan', 'view-spesialis', 'view-master',
            'view-kepegawaian-master-bagian', 'view-kepegawaian-master-jabatan', 'view-kepegawaian-master-ruangan', 'view-kepegawaian-master-spesialisasi',
            'view-kepegawaian-master-cuti', 'view-pengaturan-cuti', 'view-kepegawaian-master-cuti-index',
            'add-kepegawaian-master-cuti-index', 'edit-kepegawaian-master-cuti-index', 'delete-kepegawaian-master-cuti-index',
            'view-kepegawaian-master-tunjangan-golongan', 'view-kepegawaian-master-tunjangan-jabatan', 'view-kepegawaian-master-tunjangan-lain', 'view-kepegawaian-master-aturan-pajak',
            'create-cuti-other-karyawan', 'view-sp3', 'view-kepegawaian-surat-sp3',
            'add-kepegawaian-surat-cuti', 'edit-kepegawaian-surat-cuti', 'delete-kepegawaian-surat-cuti',
            'view-kepegawaian-gaji', 'view-kepegawaian-gaji-index', 'add-kepegawaian-gaji-index', 'edit-kepegawaian-gaji-index', 'delete-kepegawaian-gaji-index', 'view-kepegawaian-gaji-rekap', 'view-kepegawaian-gaji-detail', 'approve-kepegawaian-gaji',
            'view-jasmed', 'view-kepegawaian-jasmed', 'jasmed-bpjs', 'jasmed-jkmd',
            'view-akreditasi', 'view-kepegawaian-akreditasi', 'assesor-akreditasi',
            'view-kepegawaian-laporan',
            // Full Logistik, Gudang, Asset & Umum Permissions
            'view-supplier', 'view-kategori-barang', 'view-satuan-barang', 'view-penyimpanan', 'view-barang', 'view-pembelian', 'view-distribusi', 'view-gudang', 'view-asset', 'view-maintenance', 'approval-maintenance', 'view-pengajuan', 'view-stok-opname', 'finish-opname-gudang',
            'view-umum-master-supplier', 'view-umum-master-kategori', 'view-umum-master-satuan', 'view-umum-master-penyimpanan', 'view-umum-master-barang', 'view-umum-pembelian', 'view-umum-distribusi', 'view-umum-gudang', 'view-umum-asset', 'view-umum-maintenance', 'view-umum-pengajuan', 'view-umum-opname', 'view-umum-laporan',
        ]));


        // 3. Wadir Keuangan (Full Keuangan, COA, Jurnal Umum, Hutang, Piutang, Rekanan, Payroll & Gaji, Aturan Pajak)
        $wadirKeuanganPermissions = array_unique(array_merge($sharedExecutivePermissions, [
            'view-hutang',
            'view-piutang',
            'view-keuangan-hutang',
            'view-keuangan-piutang',
            'view-keuangan-master-rekanan',
            'add-keuangan-master-rekanan',
            'edit-keuangan-master-rekanan',
            'delete-keuangan-master-rekanan',
            'view-keuangan-akuntansi-coa',
            'view-keuangan-akuntansi-jurnal-umum',
            'view-keuangan-laporan',
            'view-kepegawaian-gaji',
            'view-kepegawaian-gaji-index',
            'add-kepegawaian-gaji-index',
            'edit-kepegawaian-gaji-index',
            'delete-kepegawaian-gaji-index',
            'view-kepegawaian-gaji-rekap',
            'view-kepegawaian-gaji-detail',
            'approve-kepegawaian-gaji',
            'view-kepegawaian-master-aturan-pajak',
            'view-jasmed',
            'jasmed-bpjs',
            'jasmed-jkmd',
            'view-pembelian',
            'view-umum-pembelian',
            'view-umum-pengajuan',
            'view-kepegawaian-karyawan',
            'view-kepegawaian-laporan',
        ]));

        // 4. Direktur Utama & Dewan Pengawas (Comprehensive Leadership Overview across all units)
        $direkturPermissions = array_unique(array_merge(
            $wadirMedisPermissions,
            $wadirSdmPermissions,
            $wadirKeuanganPermissions
        ));
        $dewasPermissions = $direkturPermissions;
        $kabidPermissions = array_unique(array_merge($sharedExecutivePermissions, [
            'view-kepegawaian-karyawan',
            'view-kepegawaian-laporan',
            'view-kepegawaian-gaji',
            'view-kepegawaian-gaji-rekap',
        ]));





        // 1. Create or Find Bagians (Max 25 chars)
        $b_direksi = Bagian::firstOrCreate(['nama' => 'Direksi & Dewas'], ['is_active' => true, 'group' => 'manajemen'])->id;
        $b_komite = Bagian::firstOrCreate(['nama' => 'Komite & Tim'], ['is_active' => true, 'group' => 'manajemen'])->id;
        $b_medis = Bagian::firstOrCreate(['nama' => 'Pelayanan Medis'], ['is_active' => true, 'group' => 'medis'])->id;
        $b_keperawatan = Bagian::firstOrCreate(['nama' => 'Keperawatan'], ['is_active' => true, 'group' => 'medis'])->id;
        $b_sdm = Bagian::firstOrCreate(['nama' => 'SDM & Umum'], ['is_active' => true, 'group' => 'manajemen'])->id;
        $b_keuangan = Bagian::firstOrCreate(['nama' => 'Keuangan'], ['is_active' => true, 'group' => 'manajemen'])->id;
        $b_farmasi = Bagian::firstOrCreate(['nama' => 'Farmasi'], ['is_active' => true, 'group' => 'penunjang'])->id;

        // Ensure parent_id is nullable
        try {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE sdm_jabatan MODIFY COLUMN parent_id BIGINT UNSIGNED NULL");
            }
        } catch (\Throwable $e) {}

        // 2. Build Tree Structure Nodes (Parent-Child Hierarchy)
        
        // LEVEL 1: Dewan Pengawas (Top Root)
        $dewasJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Dewan Pengawas'],
            [
                'kode_surat' => 'DEWAS',
                'parent_id' => null,
                'bagian_id' => $b_direksi,
                'tunjangan_jabatan' => 6000000,
            ]
        );

        // LEVEL 1.1: Sekretariat Dewas (Parent: Dewan Pengawas)
        $sekDewasJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Sekretariat Dewas'],
            [
                'kode_surat' => 'SEK-DEWAS',
                'parent_id' => $dewasJabatan->id,
                'bagian_id' => $b_direksi,
                'tunjangan_jabatan' => 2000000,
            ]
        );

        // LEVEL 2: Direktur Utama (Parent: Dewan Pengawas)
        $dirJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Direktur Utama'],
            [
                'kode_surat' => 'DIRUT',
                'parent_id' => $dewasJabatan->id,
                'bagian_id' => $b_direksi,
                'tunjangan_jabatan' => 5000000,
            ]
        );

        // LEVEL 3A: Manajer Pelayanan Pasien / MPP (Parent: Direktur Utama)
        $mppJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Manajer Pelayanan Pasien'],
            [
                'kode_surat' => 'MPP',
                'parent_id' => $dirJabatan->id,
                'bagian_id' => $b_medis,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        // LEVEL 3A.1: Pengawas Casemix (Parent: MPP)
        $casemixJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Pengawas Casemix'],
            [
                'kode_surat' => 'CASEMIX',
                'parent_id' => $mppJabatan->id,
                'bagian_id' => $b_medis,
                'tunjangan_jabatan' => 1800000,
            ]
        );

        // LEVEL 3B: Jajaran Wakil Direktur (Wadir) (Parent: Direktur Utama)
        $wadirMedisJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Wakil Direktur Medis & Keperawatan'],
            [
                'kode_surat' => 'WADIR-MEDIS',
                'parent_id' => $dirJabatan->id,
                'bagian_id' => $b_medis,
                'tunjangan_jabatan' => 4000000,
            ]
        );

        $wadirSdmJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Wakil Direktur SDM & Umum'],
            [
                'kode_surat' => 'WADIR-SDM',
                'parent_id' => $dirJabatan->id,
                'bagian_id' => $b_sdm,
                'tunjangan_jabatan' => 4000000,
            ]
        );

        $wadirKeuanganJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Wakil Direktur Keuangan'],
            [
                'kode_surat' => 'WADIR-KEU',
                'parent_id' => $dirJabatan->id,
                'bagian_id' => $b_keuangan,
                'tunjangan_jabatan' => 4000000,
            ]
        );

        // LEVEL 4A: Sub-Unit di bawah Wadir Medis & Keperawatan
        $kabidMedisJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Kabid Pelayanan Medis'],
            [
                'kode_surat' => 'KABID-MEDIS',
                'parent_id' => $wadirMedisJabatan->id,
                'bagian_id' => $b_medis,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        $kabidKeperawatanJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Kabid Keperawatan'],
            [
                'kode_surat' => 'KABID-KEP',
                'parent_id' => $wadirMedisJabatan->id,
                'bagian_id' => $b_keperawatan,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        // LEVEL 4B: Sub-Unit di bawah Wadir SDM & Umum
        $kabagSdmJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Kepala Bagian SDM'],
            [
                'kode_surat' => 'KABAG-SDM',
                'parent_id' => $wadirSdmJabatan->id,
                'bagian_id' => $b_sdm,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        $kabagUmumJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Kepala Bagian Umum'],
            [
                'kode_surat' => 'KABAG-UM',
                'parent_id' => $wadirSdmJabatan->id,
                'bagian_id' => $b_sdm,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        // LEVEL 4C: Sub-Unit di bawah Wadir Keuangan
        $kabagKeuanganJabatan = Jabatan::updateOrCreate(
            ['nama' => 'Kepala Bagian Keuangan'],
            [
                'kode_surat' => 'KABAG-KEU',
                'parent_id' => $wadirKeuanganJabatan->id,
                'bagian_id' => $b_keuangan,
                'tunjangan_jabatan' => 2500000,
            ]
        );

        // Define nodes array for seeding user accounts & roles
        $nodes = [
            // Dewan Pengawas
            [
                'role' => 'Dewan-Pengawas',
                'jabatan_id' => $dewasJabatan->id,
                'email' => 'dewan_pengawas@rsba.com',
                'nama' => 'Prof. Dr. Dewan Pengawas M.Si.',
                'nip' => 'NIP-DEWAS-001',
                'perms' => $dewasPermissions,
            ],
            // Sekretariat Dewas
            [
                'role' => 'Sekretariat-Dewan-Pengawas',
                'jabatan_id' => $sekDewasJabatan->id,
                'email' => 'sekretariat_dewas@rsba.com',
                'nama' => 'Sekretariat Dewas S.Sos.',
                'nip' => 'NIP-DEWAS-002',
                'perms' => $dewasPermissions,
            ],
            // Direktur Utama
            [
                'role' => 'Direktur',
                'jabatan_id' => $dirJabatan->id,
                'email' => 'direktur@rsba.com',
                'nama' => 'dr. H. Direktur Utama MARS',
                'nip' => 'NIP-DIRUT-001',
                'perms' => $direkturPermissions,
            ],
            // Sekretaris Direktur
            [
                'role' => 'Sekretaris-Direktur',
                'jabatan' => 'Sekretaris Direktur',
                'kode_surat' => 'SEK-DIR',
                'bagian_id' => $b_direksi,
                'parent_id' => $dirJabatan->id,
                'email' => 'sekretaris_direktur@rsba.com',
                'nama' => 'Sekretaris Direktur S.Pd.',
                'nip' => 'NIP-DIR-002',
                'perms' => $direkturPermissions,
            ],
            // Manajer Pelayanan Pasien (MPP)
            [
                'role' => 'Manajer-Pelayanan-Pasien',
                'jabatan_id' => $mppJabatan->id,
                'email' => 'mpp@rsba.com',
                'nama' => 'Manajer Pelayanan Pasien S.Kep.',
                'nip' => 'NIP-MPP-001',
                'perms' => $wadirMedisPermissions,
            ],
            // Pengawas Casemix
            [
                'role' => 'Pengawas-Casemix',
                'jabatan_id' => $casemixJabatan->id,
                'email' => 'pengawas_casemix@rsba.com',
                'nama' => 'Pengawas Casemix S.K.M.',
                'nip' => 'NIP-CASEMIX-001',
                'perms' => $wadirMedisPermissions,
            ],

            // Komite & Tim di bawah Direktur Utama
            [
                'role' => 'Komite-Etik-RS',
                'jabatan' => 'Ketua Komite Etik RS',
                'kode_surat' => 'KOM-ETIK',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'komite_etik@rsba.com',
                'nama' => 'dr. Ketua Komite Etik Sp.F',
                'nip' => 'NIP-KOM-001',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Komite-Mutu',
                'jabatan' => 'Ketua Komite Mutu',
                'kode_surat' => 'KOM-MUTU',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'komite_mutu@rsba.com',
                'nama' => 'dr. Ketua Komite Mutu MARS',
                'nip' => 'NIP-KOM-002',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Komite-Nakes-Lain',
                'jabatan' => 'Ketua Komite Nakes Lain',
                'kode_surat' => 'KOM-NAKES',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'komite_nakes@rsba.com',
                'nama' => 'Ketua Komite Nakes S.Farm.',
                'nip' => 'NIP-KOM-003',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Komite-Keperawatan',
                'jabatan' => 'Ketua Komite Keperawatan',
                'kode_surat' => 'KOM-KEP',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'komite_keperawatan@rsba.com',
                'nama' => 'Ketua Komite Keperawatan M.Kep.',
                'nip' => 'NIP-KOM-004',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Komite-Medik',
                'jabatan' => 'Ketua Komite Medik',
                'kode_surat' => 'KOM-MEDIK',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'komite_medik@rsba.com',
                'nama' => 'dr. Ketua Komite Medik Sp.B',
                'nip' => 'NIP-KOM-005',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'SPI',
                'jabatan' => 'Kepala SPI',
                'kode_surat' => 'SPI',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'spi@rsba.com',
                'nama' => 'Kepala SPI S.E., Ak.',
                'nip' => 'NIP-SPI-001',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Tim-PPI',
                'jabatan' => 'Ketua Tim PPI',
                'kode_surat' => 'TIM-PPI',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'tim_ppi@rsba.com',
                'nama' => 'Ketua Tim PPI S.Kep.',
                'nip' => 'NIP-PPI-001',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Tim-PPRA',
                'jabatan' => 'Ketua Tim PPRA',
                'kode_surat' => 'TIM-PPRA',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'tim_ppra@rsba.com',
                'nama' => 'dr. Ketua Tim PPRA Sp.PK',
                'nip' => 'NIP-PPRA-001',
                'perms' => $direkturPermissions,
            ],
            [
                'role' => 'Tim-Lainnya',
                'jabatan' => 'Ketua Tim Lainnya',
                'kode_surat' => 'TIM-LAIN',
                'bagian_id' => $b_komite,
                'parent_id' => $dirJabatan->id,
                'email' => 'tim_lainnya@rsba.com',
                'nama' => 'Ketua Tim Lainnya S.T.',
                'nip' => 'NIP-TIM-001',
                'perms' => $direkturPermissions,
            ],

            // Jajaran Wakil Direktur (Wadir)
            [
                'role' => 'Wadir-Medis-Keperawatan',
                'jabatan_id' => $wadirMedisJabatan->id,
                'email' => 'zkii0110011@gmail.com',
                'nama' => 'dr. Wadir Medis Sp.OG',
                'nip' => 'NIP-WADIR-001',
                'perms' => $wadirMedisPermissions,
            ],
            [
                'role' => 'Wadir-SDM-Umum',
                'jabatan_id' => $wadirSdmJabatan->id,
                'email' => 'don.remora0987@gmail.com',
                'nama' => 'Wadir SDM & Umum S.H., M.H.',
                'nip' => 'NIP-WADIR-002',
                'perms' => $wadirSdmPermissions,
            ],

            [
                'role' => 'Wadir-Keuangan',
                'jabatan_id' => $wadirKeuanganJabatan->id,
                'email' => 'fasialmuhammad2610@gmail.com',
                'nama' => 'Wadir Keuangan S.E., M.Si.',
                'nip' => 'NIP-WADIR-003',
                'perms' => $wadirKeuanganPermissions,
            ],


            // SUB-UNIT DI BAWAH WADIR MEDIS & KEPERAWATAN
            [
                'role' => 'Kepala-Bidang',
                'jabatan_id' => $kabidMedisJabatan->id,
                'email' => 'kabid_medis@rsba.com',
                'nama' => 'dr. Kabid Pelayanan Medis',
                'nip' => 'NIP-KABID-001',
                'perms' => $kabidPermissions,
            ],
            [
                'role' => 'Kepala-Bidang',
                'jabatan_id' => $kabidKeperawatanJabatan->id,
                'email' => 'kabid_keperawatan@rsba.com',
                'nama' => 'Kabid Keperawatan S.Kep., Ns.',
                'nip' => 'NIP-KABID-002',
                'perms' => $kabidPermissions,
            ],
            [
                'role' => 'Staff-UGD',
                'jabatan' => 'Koordinator Dokter IGD',
                'kode_surat' => 'KOOR-IGD',
                'bagian_id' => $b_medis,
                'parent_id' => $kabidMedisJabatan->id,
                'email' => 'koor_dokter_igd@rsba.com',
                'nama' => 'dr. Koordinator Dokter IGD',
                'nip' => 'NIP-KOOR-001',
                'perms' => array_merge($commonPermissions, ['view-kepegawaian-jadwal-kerja', 'view-dokter', 'view-spesialis']),
            ],
            [
                'role' => 'Kepala-Bidang',
                'jabatan' => 'Kepala Ruangan (Karu) IGD',
                'kode_surat' => 'KARU-IGD',
                'bagian_id' => $b_keperawatan,
                'parent_id' => $kabidKeperawatanJabatan->id,
                'email' => 'karu_igd@rsba.com',
                'nama' => 'Karu IGD S.Kep.',
                'nip' => 'NIP-KARU-001',
                'perms' => $kabidPermissions,
            ],

            // SUB-UNIT DI BAWAH WADIR SDM & UMUM
            [
                'role' => 'Kepala-Bidang',
                'jabatan_id' => $kabagSdmJabatan->id,
                'email' => 'kabag_sdm@rsba.com',
                'nama' => 'Kabag SDM S.Psi.',
                'nip' => 'NIP-KABAG-001',
                'perms' => $kabidPermissions,
            ],
            [
                'role' => 'Bagian-Umum',
                'jabatan_id' => $kabagUmumJabatan->id,
                'email' => 'kabag_umum@rsba.com',
                'nama' => 'Kabag Umum S.Sos.',
                'nip' => 'NIP-KABAG-002',
                'perms' => $wadirSdmPermissions,
            ],
            [
                'role' => 'Staff-SDM',
                'jabatan' => 'Staff Pelaksana SDM',
                'kode_surat' => 'STAF-SDM',
                'bagian_id' => $b_sdm,
                'parent_id' => $kabagSdmJabatan->id,
                'email' => 'staff_sdm_01@rsba.com',
                'nama' => 'Staff Pelaksana SDM A.Md.',
                'nip' => 'NIP-STAF-001',
                'perms' => array_merge($commonPermissions, ['view-kepegawaian-jadwal-kerja', 'view-kepegawaian-karyawan']),
            ],

            // SUB-UNIT DI BAWAH WADIR KEUANGAN
            [
                'role' => 'Keuangan',
                'jabatan_id' => $kabagKeuanganJabatan->id,
                'email' => 'kabag_keuangan@rsba.com',
                'nama' => 'Kabag Keuangan S.E.',
                'nip' => 'NIP-KABAG-003',
                'perms' => $wadirKeuanganPermissions,
            ],
            [
                'role' => 'Keuangan',
                'jabatan' => 'Staff Pelaksana Keuangan',
                'kode_surat' => 'STAF-KEU',
                'bagian_id' => $b_keuangan,
                'parent_id' => $kabagKeuanganJabatan->id,
                'email' => 'staff_keuangan_01@rsba.com',
                'nama' => 'Staff Pelaksana Keuangan A.Md.',
                'nip' => 'NIP-STAF-002',
                'perms' => array_merge($commonPermissions, ['view-kepegawaian-gaji']),
            ],
            [
                'role' => 'Pajak',
                'jabatan' => 'Staff Pelaksana Pajak',
                'kode_surat' => 'STAF-PAJAK',
                'bagian_id' => $b_keuangan,
                'parent_id' => $kabagKeuanganJabatan->id,
                'email' => 'staff_pajak_01@rsba.com',
                'nama' => 'Staff Pelaksana Pajak S.E.',
                'nip' => 'NIP-STAF-003',
                'perms' => array_merge($commonPermissions, ['view-kepegawaian-gaji']),
            ],
        ];

        // 3. Loop through nodes to seed Spatie Role, Permissions & Master Jabatan
        foreach ($nodes as $node) {
            // A. Create or Find Spatie Role
            $role = Role::firstOrCreate(['name' => $node['role']]);

            // Sync permissions securely
            foreach ($node['perms'] as $permName) {
                Permission::firstOrCreate(['name' => $permName]);
            }
            $role->syncPermissions($node['perms']);

            // B. Create or Update Jabatan
            if (isset($node['jabatan_id'])) {
                $jabatan = Jabatan::find($node['jabatan_id']);
            } else {
                $jabatan = Jabatan::updateOrCreate(
                    ['nama' => $node['jabatan']],
                    [
                        'kode_surat' => $node['kode_surat'],
                        'parent_id' => $node['parent_id'],
                        'bagian_id' => $node['bagian_id'],
                        'tunjangan_jabatan' => 1500000,
                    ]
                );
            }
        }


        Schema::enableForeignKeyConstraints();

        // Auto-sync tingkat_id untuk seluruh sdm_jabatan berdasarkan pola nama
        DB::table('sdm_jabatan')->where('nama', 'like', '%direktur utama%')->orWhere('nama', 'like', '%dewan pengawas%')->update(['tingkat_id' => 1]);
        DB::table('sdm_jabatan')->where('nama', 'like', '%wadir%')->orWhere('nama', 'like', '%wakil direktur%')->update(['tingkat_id' => 2]);
        DB::table('sdm_jabatan')->where('nama', 'like', '%kabid%')->orWhere('nama', 'like', '%kepala bidang%')->orWhere('nama', 'like', '%kabag%')->orWhere('nama', 'like', '%kepala bagian%')->orWhere('nama', 'like', '%manajer%')->update(['tingkat_id' => 3]);
        DB::table('sdm_jabatan')->where('nama', 'like', '%koordinator%')->orWhere('nama', 'like', '%karu%')->orWhere('nama', 'like', '%kepala ruangan%')->update(['tingkat_id' => 4]);

        // Bagian hanya ditetapkan pada penugasan karyawan/jadwal.
        // Master Ruangan dikelola manual dan tidak lagi dipetakan ke Bagian di sini.
        if ($b_farmasi) {
            // Buat/update jabatan Kepala Bidang Farmasi
            $kabidFarmasi = Jabatan::updateOrCreate(
                ['nama' => 'Kepala Bidang Farmasi'],
                [
                    'kode_surat' => 'KABID-FAR',
                    'bagian_id' => $b_farmasi,
                    'tingkat_id' => 3,
                    'tunjangan_jabatan' => 2500000,
                ]
            );

            // Assign Andi Surya sebagai Kabid Farmasi
            $andiSurya = Karyawan::where('nama', 'like', '%Andi Surya%')->first();
            if ($andiSurya && $kabidFarmasi) {
                KaryawanJabatan::updateOrCreate(
                    [
                        'karyawan_id' => $andiSurya->id,
                        'jabatan_id'  => $kabidFarmasi->id,
                    ],
                    [
                        'bagian_id'   => $kabidFarmasi->bagian_id,
                        'tgl_mulai'   => '2024-01-01',
                        'tgl_berakhir'=> null,
                    ]
                );

                if ($andiSurya->user) {
                    Role::firstOrCreate(['name' => 'Kepala-Bidang']);
                    $andiSurya->user->assignRole('Kepala-Bidang');
                }
            }
        }
    }
}
