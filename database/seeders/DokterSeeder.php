<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * DokterSeeder
 *
 * Seed semua data yang diperlukan untuk ekosistem dokter RS:
 * ─────────────────────────────────────────────────────────
 * 1. dokter_spesialisasi  → master spesialisasi (umum & spesialis)
 * 2. sdm_karyawan         → data lengkap 3 Koordinator Dokter
 * 3. dokter               → link karyawan → spesialisasi
 * 4. users                → akun login masing-masing koordinator
 * 5. role Koordinator-Dokter + permission view-kepegawaian-jadwal-kerja
 * 6. sdm_ruangan_koordinator → assign koor ke ruangan masing-masing
 *
 * Ruangan Koordinasi Dokter (3 unit terpisah):
 * ─────────────────────────────────────────────
 * • Koor Dokter IGD       → "IGD (Instalasi Gawat Darurat)"
 * • Koor Dokter Rawat Inap → "Rawat Inap (Koordinasi Dokter)"  [dibuat baru]
 * • Koor Dokter HD        → "Ruang Hemodialisa"
 *
 * Login:
 * ──────
 * koor-igd@rsba.com        / 1234
 * koor-rawatinap@rsba.com  / 1234
 * koor-hd@rsba.com         / 1234
 */
class DokterSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── 1. MASTER SPESIALISASI ───────────────────────────────────────────
        $spesialisasiList = [
            ['nama' => 'Dokter Umum',                        'singkatan' => 'dr.',         'kategori' => 'umum'],
            ['nama' => 'Spesialis Penyakit Dalam',           'singkatan' => 'Sp.PD',       'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Jantung & Pembuluh Darah', 'singkatan' => 'Sp.JP',       'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Anak',                     'singkatan' => 'Sp.A',        'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Bedah Umum',               'singkatan' => 'Sp.B',        'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Kebidanan dan Kandungan',   'singkatan' => 'Sp.OG',       'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Paru',                     'singkatan' => 'Sp.P',        'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Kulit & Kelamin',          'singkatan' => 'Sp.KK',       'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Bedah Onkologi',           'singkatan' => 'Sp.B(K)Onk',  'kategori' => 'spesialis'],
            ['nama' => 'Spesialis THT-KL',                   'singkatan' => 'Sp.THT-KL',   'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Mata',                     'singkatan' => 'Sp.M',        'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Saraf',                    'singkatan' => 'Sp.S',        'kategori' => 'spesialis'],
            ['nama' => 'Spesialis Bedah Mulut',               'singkatan' => 'Sp.BM',       'kategori' => 'spesialis'],
            ['nama' => 'Dokter Gigi',                        'singkatan' => 'drg.',        'kategori' => 'spesialis'],
        ];

        foreach ($spesialisasiList as $sp) {
            DB::table('dokter_spesialisasi')->updateOrInsert(
                ['nama' => $sp['nama']],
                array_merge($sp, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $spIdUmum    = DB::table('dokter_spesialisasi')->where('singkatan', 'dr.')->value('id');
        $spIdPD      = DB::table('dokter_spesialisasi')->where('singkatan', 'Sp.PD')->value('id');
        $spIdGinjal  = DB::table('dokter_spesialisasi')->where('singkatan', 'Sp.PD-KGH')->value('id') ?? $spIdPD;

        // ─── 2. RUANGAN KOORDINASI DOKTER ────────────────────────────────────
        // IGD dan Ruang Hemodialisa sudah ada di RuanganDummySeeder.
        // "Rawat Inap (Koordinasi Dokter)" dibuat baru — mewakili seluruh
        // rawat inap dari sisi kepala jadwal dokter (bisa ada banyak sub-ruangan).
        Ruangan::firstOrCreate(
            ['nama' => 'IGD (Instalasi Gawat Darurat)'],
            ['is_active' => true]
        );
        Ruangan::firstOrCreate(
            ['nama' => 'Rawat Inap (Koordinasi Dokter)'],
            ['is_active' => true]
        );
        Ruangan::firstOrCreate(
            ['nama' => 'Ruang Hemodialisa'],
            ['is_active' => true]
        );

        $ruanganIGD       = Ruangan::where('nama', 'IGD (Instalasi Gawat Darurat)')->first();
        $ruanganRawatInap = Ruangan::where('nama', 'Rawat Inap (Koordinasi Dokter)')->first();
        $ruanganHD        = Ruangan::where('nama', 'Ruang Hemodialisa')->first();

        // ─── 3. ROLE KOORDINATOR-DOKTER ──────────────────────────────────────
        $roleKoorDokter = Role::firstOrCreate(['name' => 'Koordinator-Dokter']);

        // Permission minimal: bisa lihat halaman jadwal kerja & ajukan ke Wadir
        $permissionsKoorDokter = [
            'view-dashboard',
            'view-profile-jadwal-tugas-saya',
            'view-kepegawaian-jadwal-kerja',
        ];
        foreach ($permissionsKoorDokter as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        $roleKoorDokter->syncPermissions($permissionsKoorDokter);

        // ─── 4. DATA KOORDINATOR DOKTER ──────────────────────────────────────
        $koordinators = [
            [
                // ── Koordinator Dokter IGD ──────────────────────────────────
                'karyawan' => [
                    'nip'               => 'DOK-IGD-001',
                    'nik'               => '3374010101850001',
                    'pin_absen'         => 'KIGD01',
                    'nama'              => 'Ahmad Fauzan',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD',
                    'jk'                => 'L',
                    'tempat_lahir'      => 'Semarang',
                    'tgl_lahir'         => '1985-04-10',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08211100001',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Semarang Selatan',
                    'desa'              => 'Randusari',
                    'alamat'            => 'Jl. Randusari No. 12',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2015-03-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.123/DINKES/2023',
                    'sip_berakhir'      => '2028-03-01',
                    'npwp'              => '011.111.111.1-111.000',
                    'bpjs_kesehatan'    => '0011100000001',
                    'bpjs_tk'           => 'KPJ0011100001',
                    'nama_bank'         => 'BRI',
                    'no_rekening'       => '0011100001',
                    'ruangan_id'        => $ruanganIGD->id,
                ],
                'spesialis_id' => $spIdPD,
                'email'        => 'koor-igd@rsba.com',
                'ruangan'      => $ruanganIGD,
                'label'        => 'Koordinator Dokter IGD',
            ],
            [
                // ── Koordinator Dokter Rawat Inap ───────────────────────────
                'karyawan' => [
                    'nip'               => 'DOK-RI-001',
                    'nik'               => '3374010505870002',
                    'pin_absen'         => 'KRI001',
                    'nama'              => 'Siti Rahayu',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD',
                    'jk'                => 'P',
                    'tempat_lahir'      => 'Solo',
                    'tgl_lahir'         => '1987-05-05',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08211100002',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Surakarta',
                    'kec'               => 'Banjarsari',
                    'desa'              => 'Banyuanyar',
                    'alamat'            => 'Jl. Banyuanyar No. 7',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2017-07-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.124/DINKES/2023',
                    'sip_berakhir'      => '2028-07-01',
                    'npwp'              => '022.222.222.2-222.000',
                    'bpjs_kesehatan'    => '0011100000002',
                    'bpjs_tk'           => 'KPJ0011100002',
                    'nama_bank'         => 'BNI',
                    'no_rekening'       => '0011100002',
                    'ruangan_id'        => $ruanganRawatInap->id,
                ],
                'spesialis_id' => $spIdPD,
                'email'        => 'koor-rawatinap@rsba.com',
                'ruangan'      => $ruanganRawatInap,
                'label'        => 'Koordinator Dokter Rawat Inap',
            ],
            [
                // ── Koordinator Dokter HD (Hemodialisa) ─────────────────────
                'karyawan' => [
                    'nip'               => 'DOK-HD-001',
                    'nik'               => '3374010102800003',
                    'pin_absen'         => 'KHD001',
                    'nama'              => 'Budi Santoso',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD-KGH',
                    'jk'                => 'L',
                    'tempat_lahir'      => 'Yogyakarta',
                    'tgl_lahir'         => '1980-01-02',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08211100003',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Banyumanik',
                    'desa'              => 'Pudakpayung',
                    'alamat'            => 'Jl. Pudakpayung No. 3',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2013-01-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.125/DINKES/2023',
                    'sip_berakhir'      => '2028-01-01',
                    'npwp'              => '033.333.333.3-333.000',
                    'bpjs_kesehatan'    => '0011100000003',
                    'bpjs_tk'           => 'KPJ0011100003',
                    'nama_bank'         => 'BCA',
                    'no_rekening'       => '0011100003',
                    'ruangan_id'        => $ruanganHD->id,
                ],
                'spesialis_id' => $spIdGinjal,
                'email'        => 'koor-hd@rsba.com',
                'ruangan'      => $ruanganHD,
                'label'        => 'Koordinator Dokter HD',
            ],
        ];

        foreach ($koordinators as $koor) {
            // 4a. Buat/update data Karyawan
            $karyawan = Karyawan::updateOrCreate(
                ['nip' => $koor['karyawan']['nip']],
                $koor['karyawan']
            );

            // 4b. Buat record di tabel dokter (link karyawan → spesialisasi)
            DB::table('dokter')->updateOrInsert(
                ['karyawan_id' => $karyawan->id],
                [
                    'spesialis_id' => $koor['spesialis_id'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );

            // 4c. Buat akun login
            $user = User::updateOrCreate(
                ['email' => $koor['email']],
                [
                    'password'    => '1234',
                    'karyawan_id' => $karyawan->id,
                ]
            );

            // 4d. Assign role Koordinator-Dokter
            $user->syncRoles(['Koordinator-Dokter']);

            // 4e. Assign sebagai koordinator ruangan
            DB::table('sdm_ruangan_koordinator')->updateOrInsert(
                [
                    'ruangan_id'  => $koor['ruangan']->id,
                    'karyawan_id' => $karyawan->id,
                ],
                [
                    'user_id'    => $user->id,
                    'aktif'      => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info("✓ {$koor['label']}: {$karyawan->full_nama} [{$koor['email']}] → {$koor['ruangan']->nama}");
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('');
        $this->command->info('DokterSeeder selesai. 3 Koordinator Dokter siap:');
        $this->command->info('  koor-igd@rsba.com       → IGD (Instalasi Gawat Darurat)');
        $this->command->info('  koor-rawatinap@rsba.com → Rawat Inap (Koordinasi Dokter)');
        $this->command->info('  koor-hd@rsba.com        → Ruang Hemodialisa');
        $this->command->info('  Password semua: 1234');
    }
}
