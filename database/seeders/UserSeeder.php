<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sdm\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed production-level Super-Admin users.
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
         * Production System Super-Admin Users
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
                'email'              => 'faisal@rsba.com',
                'role'               => 'Super-Admin',
                'nip'                => '0000000002',
                'nik'                => '3273010101800002',
                'pin_absen'          => 'ADMIN02',
                'nama'               => 'Muhammad Faisal',
                'jk'                 => 'L',
                'tempat_lahir'       => 'Bandung',
                'tgl_lahir'          => '1980-01-01',
                'status_pernikahan'  => 'menikah',
                'gelar_depan'        => 'Dr. Eng. Ir.',
                'gelar_belakang'     => 'S.T., M.T., IPM., Aseang Eng',
                'hp'                 => '08100000002',
                'prov'               => 'Jawa Barat',
                'kab'                => 'Kota Bandung',
                'kec'                => 'Coblong',
                'desa'               => 'Lebak Siliwangi',
                'alamat'             => 'Jl. Ganesha No. 10',
                'agama'              => 'islam',
                'suku'               => 'Sunda',
                'status'             => 'tetap',
                'tgl_masuk'          => '2020-01-01',
                'kategori_kerja'     => 'reguler',
                'pendidikan_setara'  => 'S3',
                'npwp'               => '00.000.000.0-000.002',
                'bpjs_kesehatan'     => '0000000000002',
                'bpjs_tk'            => 'KPJ0000000002',
                'nama_bank'          => 'Bank Mandiri',
                'no_rekening'        => '1300000000002',
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
                    'password'    => Hash::make('1234'),
                    'karyawan_id' => $karyawan->id,
                ]
            );

            Role::firstOrCreate(['name' => $u['role']]);
            $user->syncRoles([$u['role']]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
