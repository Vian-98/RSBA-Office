<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Dokter;
use App\Models\Sdm\DokterSpesialisasi;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\JabatanTingkat;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanJabatan;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Sdm\RuanganShift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MultiActorTestSeeder extends Seeder
{
    /**
     * Run the multi-actor test seeder.
     * Sets up Master Data (Bagian, Ruangan, Jabatan, Shift Mapping),
     * Tax/PPh21 & Golongan References,
     * and 8 Multi-Actor Test Users with direct permissions.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('1. Seeding Master Bagian...');
        $bagianMedis = Bagian::updateOrCreate(['nama' => 'Pelayanan Medis'], ['group' => 'medis', 'is_active' => true]);
        $bagianKeperawatan = Bagian::updateOrCreate(['nama' => 'Keperawatan'], ['group' => 'medis', 'is_active' => true]);
        $bagianSdm = Bagian::updateOrCreate(['nama' => 'SDM & Umum'], ['group' => 'manajemen', 'is_active' => true]);
        $bagianKeuangan = Bagian::updateOrCreate(['nama' => 'Keuangan'], ['group' => 'manajemen', 'is_active' => true]);

        $this->command->info('2. Seeding Master Ruangan...');
        $ruanganIgd = Ruangan::updateOrCreate(['nama' => 'Instalasi Gawat Darurat (IGD)'], ['bagian_id' => $bagianKeperawatan->id, 'is_active' => true]);
        $ruanganIcu = Ruangan::updateOrCreate(['nama' => 'Intensive Care Unit (ICU)'], ['bagian_id' => $bagianKeperawatan->id, 'is_active' => true]);
        $ruanganDahlia = Ruangan::updateOrCreate(['nama' => 'Rawat Inap Dahlia'], ['bagian_id' => $bagianKeperawatan->id, 'is_active' => true]);
        $ruanganSdm = Ruangan::updateOrCreate(['nama' => 'Kantor SDM & Umum'], ['bagian_id' => $bagianSdm->id, 'is_active' => true]);
        $ruanganKeuangan = Ruangan::updateOrCreate(['nama' => 'Kantor Keuangan'], ['bagian_id' => $bagianKeuangan->id, 'is_active' => true]);

        $this->command->info('3. Seeding Dokter Spesialisasi...');
        $spesialisUmum = DokterSpesialisasi::updateOrCreate(['nama' => 'Dokter Umum'], ['singkatan' => 'dr.', 'kategori' => 'umum']);
        $spesialisBedah = DokterSpesialisasi::updateOrCreate(['nama' => 'Spesialis Bedah'], ['singkatan' => 'Sp.B', 'kategori' => 'spesialis']);

        $this->command->info('4. Seeding Master Jabatan (Tingkat 1 - 5)...');
        $jabDirektur = Jabatan::updateOrCreate(['nama' => 'Direktur Utama'], [
            'tingkat_id' => 1,
            'parent_id' => null,
            'bagian_id' => null,
            'tunjangan_jabatan' => 15000000,
        ]);

        $jabWadir = Jabatan::updateOrCreate(['nama' => 'Wadir Medis & Keperawatan'], [
            'tingkat_id' => 2,
            'parent_id' => $jabDirektur->id,
            'bagian_id' => $bagianMedis->id,
            'tunjangan_jabatan' => 10000000,
        ]);

        $jabKabid = Jabatan::updateOrCreate(['nama' => 'Kabid Keperawatan'], [
            'tingkat_id' => 3,
            'parent_id' => $jabWadir->id,
            'bagian_id' => $bagianKeperawatan->id,
            'tunjangan_jabatan' => 5000000,
        ]);

        $jabKaru = Jabatan::updateOrCreate(['nama' => 'Kepala Ruangan IGD'], [
            'tingkat_id' => 4,
            'parent_id' => $jabKabid->id,
            'bagian_id' => $bagianKeperawatan->id,
            'tunjangan_jabatan' => 2500000,
        ]);

        $jabDokter = Jabatan::updateOrCreate(['nama' => 'Dokter Jaga IGD'], [
            'tingkat_id' => 5,
            'parent_id' => $jabKaru->id,
            'bagian_id' => $bagianMedis->id,
            'tunjangan_jabatan' => 1500000,
        ]);

        $jabPerawat = Jabatan::updateOrCreate(['nama' => 'Perawat Pelaksana IGD'], [
            'tingkat_id' => 5,
            'parent_id' => $jabKaru->id,
            'bagian_id' => $bagianKeperawatan->id,
            'tunjangan_jabatan' => 1000000,
        ]);

        $jabSdm = Jabatan::updateOrCreate(['nama' => 'Staff SDM & Kepegawaian'], [
            'tingkat_id' => 5,
            'parent_id' => null,
            'bagian_id' => $bagianSdm->id,
            'tunjangan_jabatan' => 800000,
        ]);

        $jabKeuangan = Jabatan::updateOrCreate(['nama' => 'Staff Keuangan & Pajak'], [
            'tingkat_id' => 5,
            'parent_id' => null,
            'bagian_id' => $bagianKeuangan->id,
            'tunjangan_jabatan' => 800000,
        ]);

        $jabAkreditasi = Jabatan::updateOrCreate(['nama' => 'Assessor & Sekretariat Akreditasi'], [
            'tingkat_id' => 5,
            'parent_id' => null,
            'bagian_id' => null,
            'tunjangan_jabatan' => 500000,
        ]);

        $this->command->info('5. Seeding Ruangan Shift Mapping...');
        $shifts = JadwalShift::whereIn('kode', ['PAGI', 'SIANG', 'MALAM', 'REGULER'])->get()->keyBy('kode');
        if ($shifts->isNotEmpty()) {
            foreach ([$ruanganIgd, $ruanganIcu] as $r) {
                foreach (['PAGI', 'SIANG', 'MALAM'] as $code) {
                    if (isset($shifts[$code])) {
                        RuanganShift::updateOrCreate(['ruangan_id' => $r->id, 'shift_id' => $shifts[$code]->id]);
                    }
                }
            }
            foreach (['PAGI', 'SIANG', 'MALAM', 'REGULER'] as $code) {
                if (isset($shifts[$code])) {
                    RuanganShift::updateOrCreate(['ruangan_id' => $ruanganDahlia->id, 'shift_id' => $shifts[$code]->id]);
                }
            }
            if (isset($shifts['REGULER'])) {
                RuanganShift::updateOrCreate(['ruangan_id' => $ruanganSdm->id, 'shift_id' => $shifts['REGULER']->id]);
                RuanganShift::updateOrCreate(['ruangan_id' => $ruanganKeuangan->id, 'shift_id' => $shifts['REGULER']->id]);
            }
        }

        $this->command->info('6. Seeding Payroll Tax References & Golongan...');
        if (class_exists(PayrollPph21ReferenceSeeder::class)) {
            $this->call(PayrollPph21ReferenceSeeder::class);
        }
        if (class_exists(SdmPayrollGolonganMatrixSeeder::class)) {
            $this->call(SdmPayrollGolonganMatrixSeeder::class);
        }
        if (class_exists(PayrollSeeder::class)) {
            $this->call(PayrollSeeder::class);
        }

        $this->command->info('7. Seeding Multi-Actor Users & Karyawans...');
        $actorConfigs = [
            [
                'email' => 'direktur@rsba.test',
                'nama' => 'dr. H. Direktur Utama, Sp.B',
                'nip' => '197501012000011001',
                'nik' => '3271010101750001',
                'pin_absen' => 'DIR01',
                'jk' => 'L',
                'jabatan' => $jabDirektur,
                'ruangan' => null,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2015-01-01',
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya',
                    'view-karyawan', 'view-kepegawaian-karyawan', 'view-kepegawaian-karyawan-index',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'view-kepegawaian-laporan', 'view-kepegawaian-laporan-index',
                    'view-surat', 'view-kepegawaian-surat-cuti', 'view-kepegawaian-surat-perintah-tugas',
                    'view-kepegawaian-surat-balasan-pkl', 'view-kepegawaian-surat-balasan-penelitian',
                    'view-kepegawaian-surat-sp3', 'view-kepegawaian-surat-arsip',
                    'view-tanda-tangan-digital', 'tanda-tangan-digital', 'sign-surat-ttd',
                    'view-surat-ttd', 'view-surat-verification',
                    'view-kepegawaian-akreditasi', 'view-kepegawaian-akreditasi-index', 'view-akreditasi',
                ],
            ],
            [
                'email' => 'wadir@rsba.test',
                'nama' => 'dr. Wadir Medis, M.Kes',
                'nip' => '198002022005011002',
                'nik' => '3271010202800002',
                'pin_absen' => 'WADIR01',
                'jk' => 'L',
                'jabatan' => $jabWadir,
                'ruangan' => null,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2017-02-01',
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya',
                    'view-karyawan', 'view-kepegawaian-karyawan', 'view-kepegawaian-karyawan-index',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'approve-jadwal-wadir',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'approve-kepegawaian-cuti',
                    'view-surat', 'view-kepegawaian-surat-perintah-tugas',
                    'view-kepegawaian-surat-balasan-pkl', 'view-kepegawaian-surat-balasan-penelitian',
                    'view-kepegawaian-surat-sp3', 'view-kepegawaian-surat-arsip',
                    'view-tanda-tangan-digital', 'tanda-tangan-digital', 'sign-surat-ttd',
                    'view-surat-ttd', 'view-surat-verification',
                    'view-kepegawaian-laporan', 'view-kepegawaian-laporan-index',
                    'view-kepegawaian-gaji', 'view-kepegawaian-gaji-index', 'view-kepegawaian-gaji-rekap',
                    'approve-kepegawaian-gaji',
                ],
            ],
            [
                'email' => 'kabid@rsba.test',
                'nama' => 'Ns. Kabid Keperawatan, S.Kep',
                'nip' => '198503032010012003',
                'nik' => '3271010303850003',
                'pin_absen' => 'KABID01',
                'jk' => 'P',
                'jabatan' => $jabKabid,
                'ruangan' => null,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2018-03-01',
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya',
                    'view-karyawan', 'view-kepegawaian-karyawan', 'view-kepegawaian-karyawan-index',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'approve-jadwal-kabid',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'approve-kepegawaian-cuti',
                    'view-surat', 'view-kepegawaian-surat-cuti', 'view-kepegawaian-surat-perintah-tugas',
                    'view-kepegawaian-surat-sp3', 'view-kepegawaian-surat-arsip', 'view-sp3',
                    'view-tanda-tangan-digital', 'tanda-tangan-digital', 'sign-surat-ttd',
                    'view-surat-ttd', 'view-surat-verification',
                    'view-kepegawaian-laporan', 'view-kepegawaian-laporan-index',
                ],
            ],
            [
                'email' => 'karu@rsba.test',
                'nama' => 'Ns. Karu IGD, S.Kep',
                'nip' => '199004042015012004',
                'nik' => '3271010404900004',
                'pin_absen' => 'KARU01',
                'jk' => 'L',
                'jabatan' => $jabKaru,
                'ruangan' => $ruanganIgd,
                'kategori_kerja' => 'shift',
                'status' => 'tetap',
                'tgl_masuk' => '2019-04-01',
                'is_koordinator' => true,
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya', 'edit-profile-jadwal-tugas-saya',
                    'view-karyawan', 'view-kepegawaian-karyawan', 'view-kepegawaian-karyawan-index',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'add-kepegawaian-jadwal-kerja', 'add-kepegawaian-jadwal-kerja-index',
                    'edit-kepegawaian-jadwal-kerja-index', 'delete-kepegawaian-jadwal-kerja',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'approve-kepegawaian-cuti',
                    'view-surat', 'view-kepegawaian-surat-arsip',
                    'view-kepegawaian-konfigurasi-jadwal', 'view-kepegawaian-konfigurasi-jadwal-index',
                ],
            ],
            [
                'email' => 'staf@rsba.test',
                'nama' => 'Ns. Perawat Pelaksana IGD',
                'nip' => '199606062021012006',
                'nik' => '3271010606960006',
                'pin_absen' => 'IGD02',
                'jk' => 'P',
                'jabatan' => $jabPerawat,
                'ruangan' => $ruanganIgd,
                'kategori_kerja' => 'shift',
                'status' => 'tetap',
                'tgl_masuk' => '2021-06-01',
                'cuti' => 12,
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'add-kepegawaian-surat-cuti',
                    'view-kepegawaian-gaji', 'view-kepegawaian-gaji-detail', 'view-kepegawaian-gaji-index',
                ],
            ],
            [
                'email' => 'dokter@rsba.test',
                'nama' => 'dr. Dokter Jaga IGD',
                'nip' => '199505052020011005',
                'nik' => '3271010505950005',
                'pin_absen' => 'IGD01',
                'jk' => 'L',
                'jabatan' => $jabDokter,
                'ruangan' => $ruanganIgd,
                'kategori_kerja' => 'shift',
                'status' => 'tetap',
                'tgl_masuk' => '2020-05-01',
                'cuti' => 12,
                'is_dokter' => true,
                'permissions' => [
                    'view-dashboard', 'view-profile-jadwal-tugas-saya',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'add-kepegawaian-surat-cuti',
                    'view-kepegawaian-gaji', 'view-kepegawaian-gaji-detail', 'view-kepegawaian-gaji-index',
                ],
            ],
            [
                'email' => 'sdm@rsba.test',
                'nama' => 'Staff SDM Officer',
                'nip' => '199207072017011007',
                'nik' => '3271010707920007',
                'pin_absen' => 'SDM01',
                'jk' => 'L',
                'jabatan' => $jabSdm,
                'ruangan' => $ruanganSdm,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2017-07-01',
                'permissions' => [
                    'view-dashboard',
                    'view-karyawan', 'view-kepegawaian-karyawan', 'view-kepegawaian-karyawan-index',
                    'add-kepegawaian-karyawan-index', 'edit-kepegawaian-karyawan-index',
                    'edit-tgl-masuk-karyawan', 'export-karyawan',
                    'view-kepegawaian-master-jabatan', 'view-kepegawaian-master-jabatan-index',
                    'add-kepegawaian-master-jabatan-index', 'edit-kepegawaian-master-jabatan-index',
                    'view-kepegawaian-master-bagian', 'view-kepegawaian-master-bagian-index',
                    'add-kepegawaian-master-bagian-index', 'edit-kepegawaian-master-bagian-index',
                    'view-kepegawaian-master-ruangan', 'view-kepegawaian-master-ruangan-index',
                    'add-kepegawaian-master-ruangan-index', 'edit-kepegawaian-master-ruangan-index',
                    'view-kepegawaian-master-spesialisasi', 'view-kepegawaian-master-spesialisasi-index',
                    'add-kepegawaian-master-spesialisasi-index', 'edit-kepegawaian-master-spesialisasi-index',
                    'view-kepegawaian-master-cuti', 'view-kepegawaian-master-cuti-index',
                    'view-kepegawaian-master-jadwal-shift', 'add-kepegawaian-master-jadwal-shift',
                    'edit-kepegawaian-master-jadwal-shift',
                    'view-kepegawaian-master-jadwal-aturan', 'add-kepegawaian-master-jadwal-aturan',
                    'edit-kepegawaian-master-jadwal-aturan',
                    'view-kepegawaian-master-ruangan-shift', 'add-kepegawaian-master-ruangan-shift',
                    'edit-kepegawaian-master-ruangan-shift',
                    'view-kepegawaian-master-bagian-koordinator', 'add-kepegawaian-master-bagian-koordinator',
                    'edit-kepegawaian-master-bagian-koordinator',
                    'view-kepegawaian-master-tunjangan-golongan', 'view-kepegawaian-master-tunjangan-golongan-index',
                    'add-kepegawaian-master-tunjangan-golongan', 'edit-kepegawaian-master-tunjangan-golongan',
                    'view-kepegawaian-master-tunjangan-jabatan', 'view-kepegawaian-master-tunjangan-jabatan-index',
                    'add-kepegawaian-master-tunjangan-jabatan', 'edit-kepegawaian-master-tunjangan-jabatan',
                    'view-kepegawaian-master-tunjangan-lain', 'view-kepegawaian-master-tunjangan-lain-index',
                    'add-kepegawaian-master-tunjangan-lain', 'edit-kepegawaian-master-tunjangan-lain',
                    'view-kepegawaian-master-aturan-pajak', 'view-kepegawaian-master-aturan-pajak-index',
                    'manage-kepegawaian-master-aturan',
                    'view-kepegawaian-jadwal-kerja', 'view-kepegawaian-jadwal-kerja-index',
                    'view-kepegawaian-konfigurasi-jadwal', 'view-kepegawaian-konfigurasi-jadwal-index',
                    'edit-kepegawaian-konfigurasi-jadwal-index',
                    'view-kepegawaian-absensi', 'view-kepegawaian-absensi-index',
                    'add-kepegawaian-absensi-index',
                    'view-kepegawaian-surat-cuti', 'view-cuti', 'add-kepegawaian-surat-cuti',
                    'edit-kepegawaian-surat-cuti', 'create-cuti-other-karyawan', 'view-pengaturan-cuti',
                    'view-kepegawaian-cuti-bersama',
                    'view-surat', 'view-kepegawaian-surat-perintah-tugas',
                    'add-kepegawaian-surat-perintah-tugas', 'edit-kepegawaian-surat-perintah-tugas',
                    'view-kepegawaian-surat-balasan-pkl', 'add-kepegawaian-surat-balasan-pkl',
                    'edit-kepegawaian-surat-balasan-pkl',
                    'view-kepegawaian-surat-balasan-penelitian', 'add-kepegawaian-surat-balasan-penelitian',
                    'view-kepegawaian-surat-sp3', 'add-kepegawaian-surat-sp3', 'edit-kepegawaian-surat-sp3',
                    'view-kepegawaian-surat-arsip', 'add-kepegawaian-surat-arsip', 'view-sp3',
                    'view-surat-verification', 'edit-surat-verification',
                    'view-kepegawaian-gaji', 'view-kepegawaian-gaji-index', 'view-kepegawaian-gaji-rekap',
                    'view-kepegawaian-gaji-detail', 'add-kepegawaian-gaji-index', 'edit-kepegawaian-gaji-index',
                    'approve-kepegawaian-gaji-pajak',
                    'view-kepegawaian-laporan', 'view-kepegawaian-laporan-index',
                ],
            ],
            [
                'email' => 'keuangan@rsba.test',
                'nama' => 'Staff Keuangan Officer',
                'nip' => '199308082018011008',
                'nik' => '3271010808930008',
                'pin_absen' => 'KEU01',
                'jk' => 'P',
                'jabatan' => $jabKeuangan,
                'ruangan' => $ruanganKeuangan,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2018-08-01',
                'permissions' => [
                    'view-dashboard',
                    'view-kepegawaian-gaji', 'view-kepegawaian-gaji-index',
                    'view-kepegawaian-gaji-rekap', 'view-kepegawaian-gaji-detail',
                    'approve-kepegawaian-gaji', 'approve-kepegawaian-gaji-pajak', 'unlock-payroll-approved',
                    'view-kepegawaian-surat-sp3', 'view-sp3', 'approve-kepegawaian-sp3',
                    'view-keuangan-kuitansi', 'create-keuangan-kuitansi', 'edit-keuangan-kuitansi',
                    'print-keuangan-kuitansi', 'approval-keuangan-kuitansi',
                    'view-kepegawaian-master-aturan-pajak', 'view-kepegawaian-master-aturan-pajak-index',
                    'view-keuangan-laporan', 'view-keuangan-laporan-index', 'view-laporan',
                ],
            ],
            [
                'email' => 'akreditasi@rsba.test',
                'nama' => 'Assessor Akreditasi',
                'nip' => '199409092019011009',
                'nik' => '3271010909940009',
                'pin_absen' => 'AKRE01',
                'jk' => 'L',
                'jabatan' => $jabAkreditasi,
                'ruangan' => null,
                'kategori_kerja' => 'reguler',
                'status' => 'tetap',
                'tgl_masuk' => '2019-09-01',
                'permissions' => [
                    'view-dashboard',
                    'view-kepegawaian-akreditasi', 'view-kepegawaian-akreditasi-index',
                    'view-akreditasi',
                    'add-kepegawaian-akreditasi-index', 'edit-kepegawaian-akreditasi-index',
                    'delete-kepegawaian-akreditasi-index',
                    'assesor-akreditasi', 'sekretariat-akreditasi',
                ],
            ],
        ];

        foreach ($actorConfigs as $cfg) {
            $karyawan = Karyawan::updateOrCreate(
                ['nip' => $cfg['nip']],
                [
                    'nik' => $cfg['nik'],
                    'nama' => $cfg['nama'],
                    'pin_absen' => $cfg['pin_absen'],
                    'jk' => $cfg['jk'],
                    'tempat_lahir' => 'Bandar Lampung',
                    'tgl_lahir' => '1990-01-01',
                    'hp' => '081234567890',
                    'status' => $cfg['status'],
                    'tgl_masuk' => $cfg['tgl_masuk'],
                    'kategori_kerja' => $cfg['kategori_kerja'],
                    'ruangan_id' => $cfg['ruangan']?->id,
                    'cuti' => $cfg['cuti'] ?? 12,
                    'prov' => 'Lampung',
                    'kab' => 'Kota Bandar Lampung',
                    'kec' => 'Kedaton',
                    'desa' => 'Surabaya',
                    'alamat' => 'Jl. RS Bhayangkara No. 1',
                    'agama' => 'islam',
                ]
            );

            // Assign Jabatan
            if (isset($cfg['jabatan'])) {
                KaryawanJabatan::updateOrCreate(
                    [
                        'karyawan_id' => $karyawan->id,
                        'jabatan_id' => $cfg['jabatan']->id,
                    ],
                    [
                        'bagian_id' => $cfg['jabatan']->bagian_id,
                        'tgl_mulai' => $cfg['tgl_masuk'],
                        'tgl_berakhir' => null,
                    ]
                );
            }

            // Create Dokter record if needed
            if (!empty($cfg['is_dokter'])) {
                Dokter::updateOrCreate(
                    ['karyawan_id' => $karyawan->id],
                    ['spesialis_id' => $spesialisUmum->id]
                );
            }

            // Create/Update User
            $user = User::updateOrCreate(
                ['email' => $cfg['email']],
                [
                    'password' => Hash::make('password123'),
                    'karyawan_id' => $karyawan->id,
                    'email_verified_at' => now(),
                ]
            );

            // Assign Koordinator Ruangan if marked
            if (!empty($cfg['is_koordinator']) && $cfg['ruangan']) {
                RuanganKoordinator::updateOrCreate(
                    [
                        'ruangan_id' => $cfg['ruangan']->id,
                        'karyawan_id' => $karyawan->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'aktif' => true,
                    ]
                );
            }

            // Sync direct permissions
            $user->syncPermissions($cfg['permissions']);
            $this->command->info("  ✓ {$cfg['nama']} ({$cfg['email']}) -> " . count($cfg['permissions']) . " permissions");
        }

        Schema::enableForeignKeyConstraints();
        $this->command->info('Multi-Actor Test Seeding Completed Successfully!');
    }
}
