<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sdm\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed system-level users (admin, staff SDM, Kabid, Wadir, dll).
     *
     * FIELD LENGKAP: semua kolom sdm_karyawan diisi agar modul payroll,
     * absensi, dan surat tidak mengalami null pointer atau error kalkulasi.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Rename old admin@admin.com user if exists
        $oldAdmin = User::where('email', 'admin@admin.com')->first();
        if ($oldAdmin) {
            $oldAdmin->update(['email' => 'admin@rsba.com']);
        }

        /**
         * Data system users.
         *
         * Field-field yang diisi:
         * - nip, nik, nama, jk, tempat_lahir, tgl_lahir, hp
         * - status_pernikahan, gelar_depan, gelar_belakang
         * - prov, kab, kec, desa, alamat
         * - agama, suku, status, tgl_masuk, kategori_kerja
         * - pendidikan_setara, pin_absen
         * - npwp, bpjs_kesehatan, bpjs_tk
         * - nama_bank, no_rekening
         * - ruangan_id (nullable untuk user sistem non-klinis)
         */
        $usersToSeed = [
            [
                'email'              => 'admin@rsba.com',
                'role'               => 'Super-Admin',
                'nip'                => '0000000000',
                'nik'                => '0000000000000000',
                'pin_absen'          => 'ADMIN00',
                'nama'               => 'Super Admin',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Jakarta',
                'tgl_lahir'          => '1990-01-01',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => null,
                'hp'                 => '08100000000',
                'prov'               => 'DKI Jakarta',
                'kab'                => 'Jakarta Pusat',
                'kec'                => 'Tanah Abang',
                'desa'               => '-',
                'alamat'             => 'Jl. Admin No. 1',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2020-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => null,
                'bpjs_kesehatan'     => null,
                'bpjs_tk'            => null,
                'nama_bank'          => null,
                'no_rekening'        => null,
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'dimasfaqih005@gmail.com',
                'role'               => 'Super-Admin',
                'nip'                => '0000000001',
                'nik'                => '0000000000000001',
                'pin_absen'          => 'ADMIN01',
                'nama'               => 'Dimas Faqih',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Semarang',
                'tgl_lahir'          => '1998-05-10',
                'status_pernikahan'  => 'belum_menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.Kom.',
                'hp'                 => '08100000001',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Semarang',
                'kec'                => 'Banyumanik',
                'desa'               => '-',
                'alamat'             => 'Jl. Developer No. 1',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2022-03-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => null,
                'bpjs_kesehatan'     => null,
                'bpjs_tk'            => null,
                'nama_bank'          => null,
                'no_rekening'        => null,
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'sdm@rsba.com',
                'role'               => 'Staff-SDM',
                'nip'                => '1111111111',
                'nik'                => '1111111111111111',
                'pin_absen'          => 'SDM001',
                'nama'               => 'Staff SDM',
                'jk'                 => 'P',
                'tempat_lahir'       => 'Semarang',
                'tgl_lahir'          => '1993-07-15',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.Psi.',
                'hp'                 => '08111111111',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Semarang',
                'kec'                => 'Semarang Utara',
                'desa'               => 'Bandarharjo',
                'alamat'             => 'Jl. Bandarharjo No. 12',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2019-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => '111.111.111.1-111.000',
                'bpjs_kesehatan'     => '0001111111111',
                'bpjs_tk'            => 'KPJ1111111111',
                'nama_bank'          => 'BRI',
                'no_rekening'        => '1234567001',
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'umum@rsba.com',
                'role'               => 'Bagian-Umum',
                'nip'                => '2222222222',
                'nik'                => '2222222222222222',
                'pin_absen'          => 'UMM001',
                'nama'               => 'Staff Umum',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Demak',
                'tgl_lahir'          => '1991-03-20',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.Sos.',
                'hp'                 => '08122222222',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Demak',
                'kec'                => 'Demak',
                'desa'               => 'Bintoro',
                'alamat'             => 'Jl. Raya Demak No. 5',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2018-04-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => '222.222.222.2-222.000',
                'bpjs_kesehatan'     => '0002222222222',
                'bpjs_tk'            => 'KPJ2222222222',
                'nama_bank'          => 'BNI',
                'no_rekening'        => '1234567002',
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'keuangan@rsba.com',
                'role'               => 'Keuangan',
                'nip'                => '3333333333',
                'nik'                => '3333333333333333',
                'pin_absen'          => 'KEU001',
                'nama'               => 'Staff Keuangan',
                'jk'                 => 'P',
                'tempat_lahir'       => 'Kudus',
                'tgl_lahir'          => '1994-11-08',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.E.',
                'hp'                 => '08133333333',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Kudus',
                'kec'                => 'Kudus',
                'desa'               => 'Rendeng',
                'alamat'             => 'Jl. Sunan Kudus No. 8',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2020-02-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => '333.333.333.3-333.000',
                'bpjs_kesehatan'     => '0003333333333',
                'bpjs_tk'            => 'KPJ3333333333',
                'nama_bank'          => 'BCA',
                'no_rekening'        => '1234567003',
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'administrasi@rsba.com',
                'role'               => 'Administrasi',
                'nip'                => '4444444444',
                'nik'                => '4444444444444444',
                'pin_absen'          => 'ADM001',
                'nama'               => 'Staff Administrasi',
                'jk'                 => 'P',
                'tempat_lahir'       => 'Jepara',
                'tgl_lahir'          => '1996-04-12',
                'status_pernikahan'  => 'belum_menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'A.Md.',
                'hp'                 => '08144444444',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Jepara',
                'kec'                => 'Jepara',
                'desa'               => 'Panggang',
                'alamat'             => 'Jl. Kartini No. 4',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2021-06-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'D3',
                'npwp'               => null,
                'bpjs_kesehatan'     => '0004444444444',
                'bpjs_tk'            => 'KPJ4444444444',
                'nama_bank'          => 'Mandiri',
                'no_rekening'        => '1234567004',
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'guest@rsba.com',
                'role'               => 'Guest',
                'nip'                => '5555555555',
                'nik'                => '5555555555555555',
                'pin_absen'          => 'GST001',
                'nama'               => 'Guest User',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Semarang',
                'tgl_lahir'          => '2000-01-01',
                'status_pernikahan'  => 'belum_menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => null,
                'hp'                 => '08155555555',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Semarang',
                'kec'                => 'Semarang Tengah',
                'desa'               => '-',
                'alamat'             => '-',
                'agama'              => 'islam',
                'suku'               => null,
                'status'             => 'magang',
                'tgl_masuk'          => '2025-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'SMA',
                'npwp'               => null,
                'bpjs_kesehatan'     => null,
                'bpjs_tk'            => null,
                'nama_bank'          => null,
                'no_rekening'        => null,
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'kabid@rsba.com',
                'role'               => 'Kepala-Bidang',
                'nip'                => '9999999991',
                'nik'                => '9999999999999991',
                'pin_absen'          => 'KABID01',
                'nama'               => 'Kepala Bidang Medis',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Semarang',
                'tgl_lahir'          => '1985-06-20',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.Kep., M.Kes.',
                'hp'                 => '08199999991',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Semarang',
                'kec'                => 'Gajahmungkur',
                'desa'               => 'Gajahmungkur',
                'alamat'             => 'Jl. Gajahmungkur No. 10',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2012-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S2',
                'npwp'               => '999.999.999.9-991.000',
                'bpjs_kesehatan'     => '0009999999991',
                'bpjs_tk'            => 'KPJ9999999991',
                'nama_bank'          => 'BRI',
                'no_rekening'        => '9876543001',
                'ruangan_id'         => null,
                'jabatan_nama'       => 'Kabid Pelayanan Medis',
            ],
            [
                'email'              => 'kabid.farmasi@rsba.com',
                'role'               => 'Kepala-Bidang',
                'nip'                => '9999999998',
                'nik'                => '9999999999999998',
                'pin_absen'          => 'KABIDFAR01',
                'nama'               => 'Siti Rahmawati',
                'jk'                 => 'P',
                'tempat_lahir'       => 'Bandar Lampung',
                'tgl_lahir'          => '1988-03-15',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => 'apt.',
                'gelar_belakang'     => 'S.Farm.',
                'hp'                 => '08199999998',
                'prov'               => 'Lampung',
                'kab'               => 'Bandar Lampung',
                'kec'                => 'Kedaton',
                'desa'               => 'Kedaton',
                'alamat'             => 'Jl. Kedaton No. 45',
                'agama'              => 'islam',
                'suku'               => 'Lampung',
                'status'             => 'tetap',
                'tgl_masuk'          => '2015-05-10',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => '999.999.999.9-998.000',
                'bpjs_kesehatan'     => '0009999999998',
                'bpjs_tk'            => 'KPJ9999999998',
                'nama_bank'          => 'Mandiri',
                'no_rekening'        => '9876543008',
                'ruangan_id'         => null,
                'jabatan_nama'       => null,
            ],
            [
                'email'              => 'wadir@rsba.com',
                'role'               => 'Wakil-Direktur',
                'nip'                => '9999999992',
                'nik'                => '9999999999999992',
                'pin_absen'          => 'WADIR01',
                'nama'               => 'Wakil Direktur',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Yogyakarta',
                'tgl_lahir'          => '1978-03-15',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => 'dr.',
                'gelar_belakang'     => 'M.Kes.',
                'hp'                 => '08199999992',
                'prov'               => 'DI Yogyakarta',
                'kab'                => 'Sleman',
                'kec'                => 'Depok',
                'desa'               => 'Maguwoharjo',
                'alamat'             => 'Jl. Adisucipto No. 5',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2008-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S2',
                'npwp'               => '999.999.999.9-992.000',
                'bpjs_kesehatan'     => '0009999999992',
                'bpjs_tk'            => 'KPJ9999999992',
                'nama_bank'          => 'BNI',
                'no_rekening'        => '9876543002',
                'ruangan_id'         => null,
            ],
            [
                'email'              => 'pajak@rsba.com',
                'role'               => 'Pajak',
                'nip'                => '8888888888',
                'nik'                => '8888888888888888',
                'pin_absen'          => 'PAJ001',
                'nama'               => 'Staff Pajak',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Semarang',
                'tgl_lahir'          => '1992-09-25',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => null,
                'gelar_belakang'     => 'S.E., BKP.',
                'hp'                 => '08188888888',
                'prov'               => 'Jawa Tengah',
                'kab'                => 'Semarang',
                'kec'                => 'Tembalang',
                'desa'               => 'Tembalang',
                'alamat'             => 'Jl. Tembalang No. 7',
                'agama'              => 'islam',
                'suku'               => 'Jawa',
                'status'             => 'tetap',
                'tgl_masuk'          => '2021-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S1',
                'npwp'               => '888.888.888.8-888.000',
                'bpjs_kesehatan'     => '0008888888888',
                'bpjs_tk'            => 'KPJ8888888888',
                'nama_bank'          => 'Mandiri',
                'no_rekening'        => '9876543003',
                'ruangan_id'         => null,
            ],
        ];

        $optionalColumns = [
            'pin_absen', 'kategori_kerja', 'pendidikan_setara', 'ruangan_id',
            'bpjs_kesehatan', 'bpjs_tk', 'nama_bank', 'no_rekening', 'npwp',
            'suku', 'gelar_depan', 'gelar_belakang', 'status_pernikahan',
            'prov', 'kab', 'kec', 'desa', 'alamat', 'agama', 'tempat_lahir'
        ];

        foreach ($usersToSeed as $u) {
            $data = [
                'nik'       => $u['nik'],
                'nama'      => $u['nama'],
                'jk'        => $u['jk'],
                'hp'        => $u['hp'],
                'status'    => $u['status'],
                'tgl_masuk' => $u['tgl_masuk'],
                'tgl_lahir' => $u['tgl_lahir'],
            ];

            foreach ($optionalColumns as $col) {
                if (array_key_exists($col, $u) && Schema::hasColumn('sdm_karyawan', $col)) {
                    $val = $u[$col];
                    if (in_array($col, ['gelar_depan', 'gelar_belakang']) && is_string($val) && strlen($val) > 10) {
                        $val = substr($val, 0, 10);
                    }
                    $data[$col] = $val;
                }
            }

            $karyawan = Karyawan::updateOrCreate(
                ['nip' => $u['nip']],
                $data
            );

            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'password'    => \Illuminate\Support\Facades\Hash::make('1234'),
                    'karyawan_id' => $karyawan->id,
                ]
            );

            Role::firstOrCreate(['name' => $u['role']]);
            $user->syncRoles([$u['role']]);

            if (!empty($u['jabatan_nama'])) {
                $jabatanTarget = \App\Models\Sdm\Jabatan::where('nama', $u['jabatan_nama'])->first();
                if ($jabatanTarget) {
                    \App\Models\Sdm\KaryawanJabatan::updateOrCreate(
                        [
                            'karyawan_id' => $karyawan->id,
                            'jabatan_id'  => $jabatanTarget->id,
                        ],
                        [
                            'bagian_id'   => $jabatanTarget->bagian_id,
                            'tgl_mulai'   => '2020-01-01',
                            'tgl_berakhir'=> null,
                        ]
                    );
                }
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
