<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DokterPoliSeeder
 *
 * Seed 1 Poli beserta 4 Dokter yang bertugas di dalamnya.
 * ──────────────────────────────────────────────────────────────
 * Poli   : Poli Penyakit Dalam
 * Dokter :
 *   1. dr. Rizky Pratama, Sp.PD         → poli-pd-1@rsba.com  / 1234
 *   2. dr. Dewi Kusuma, Sp.PD           → poli-pd-2@rsba.com  / 1234
 *   3. dr. Hendra Wijaya, Sp.PD-KGEH    → poli-pd-3@rsba.com  / 1234
 *   4. dr. Fitriani Noor, Sp.PD         → poli-pd-4@rsba.com  / 1234
 *
 * Setiap dokter:
 *   - Dibuat sebagai sdm_karyawan dengan ruangan_id → Poli Penyakit Dalam
 *   - Didaftarkan ke tabel dokter (link karyawan → spesialisasi)
 *   - Dibuat akun User (login)
 *   - Di-assign ke ruangan poli via sdm_ruangan_koordinator
 */
class DokterPoliSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // ─── 1. PASTIKAN RUANGAN POLI ADA ─────────────────────────────────────
        $ruanganPoli = Ruangan::firstOrCreate(
            ['nama' => 'Poli Penyakit Dalam'],
            ['is_active' => true]
        );

        // ─── 2. SPESIALISASI ───────────────────────────────────────────────────
        $spIdPD = DB::table('dokter_spesialisasi')
            ->where('singkatan', 'Sp.PD')
            ->value('id');

        if (!$spIdPD) {
            $spIdPD = DB::table('dokter_spesialisasi')->insertGetId([
                'nama'       => 'Spesialis Penyakit Dalam',
                'singkatan'  => 'Sp.PD',
                'kategori'   => 'spesialis',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $spIdPDKGEH = DB::table('dokter_spesialisasi')
            ->where('singkatan', 'Sp.PD-KGEH')
            ->value('id');

        if (!$spIdPDKGEH) {
            $spIdPDKGEH = DB::table('dokter_spesialisasi')->insertGetId([
                'nama'       => 'Spesialis Penyakit Dalam Konsultan Gastroentero-Hepatologi',
                'singkatan'  => 'Sp.PD-KGEH',
                'kategori'   => 'spesialis',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ─── 3. DATA 4 DOKTER POLI ─────────────────────────────────────────────
        $dokterList = [
            [
                'karyawan' => [
                    'nip'               => 'DOK-PD-001',
                    'nik'               => '3374021503880011',
                    'pin_absen'         => 'PD001',
                    'nama'              => 'Rizky Pratama',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD',
                    'jk'                => 'L',
                    'tempat_lahir'      => 'Bandung',
                    'tgl_lahir'         => '1988-03-15',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08221200001',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Tembalang',
                    'desa'              => 'Bulusan',
                    'alamat'            => 'Jl. Bulusan Raya No. 5',
                    'agama'             => 'islam',
                    'suku'              => 'Sunda',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2018-04-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.201/DINKES/2023',
                    'sip_berakhir'      => '2028-04-01',
                    'npwp'              => '044.444.444.4-111.000',
                    'bpjs_kesehatan'    => '0022200000001',
                    'bpjs_tk'           => 'KPJ0022200001',
                    'nama_bank'         => 'BRI',
                    'no_rekening'       => '0022200001',
                    'ruangan_id'        => $ruanganPoli->id,
                ],
                'spesialis_id' => $spIdPD,
                'email'        => 'poli-pd-1@rsba.com',
            ],
            [
                'karyawan' => [
                    'nip'               => 'DOK-PD-002',
                    'nik'               => '3374020707910012',
                    'pin_absen'         => 'PD002',
                    'nama'              => 'Dewi Kusuma',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD',
                    'jk'                => 'P',
                    'tempat_lahir'      => 'Semarang',
                    'tgl_lahir'         => '1991-07-07',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08221200002',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Candisari',
                    'desa'              => 'Kaliwiru',
                    'alamat'            => 'Jl. Kaliwiru No. 14',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2019-09-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.202/DINKES/2023',
                    'sip_berakhir'      => '2028-09-01',
                    'npwp'              => '055.555.555.5-222.000',
                    'bpjs_kesehatan'    => '0022200000002',
                    'bpjs_tk'           => 'KPJ0022200002',
                    'nama_bank'         => 'BNI',
                    'no_rekening'       => '0022200002',
                    'ruangan_id'        => $ruanganPoli->id,
                ],
                'spesialis_id' => $spIdPD,
                'email'        => 'poli-pd-2@rsba.com',
            ],
            [
                'karyawan' => [
                    'nip'               => 'DOK-PD-003',
                    'nik'               => '3374021212850013',
                    'pin_absen'         => 'PD003',
                    'nama'              => 'Hendra Wijaya',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD-KGEH',
                    'jk'                => 'L',
                    'tempat_lahir'      => 'Surabaya',
                    'tgl_lahir'         => '1985-12-12',
                    'status_pernikahan' => 'menikah',
                    'hp'                => '08221200003',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Pedurungan',
                    'desa'              => 'Tlogosari Wetan',
                    'alamat'            => 'Jl. Tlogosari No. 22',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2016-02-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.203/DINKES/2023',
                    'sip_berakhir'      => '2028-02-01',
                    'npwp'              => '066.666.666.6-333.000',
                    'bpjs_kesehatan'    => '0022200000003',
                    'bpjs_tk'           => 'KPJ0022200003',
                    'nama_bank'         => 'Mandiri',
                    'no_rekening'       => '0022200003',
                    'ruangan_id'        => $ruanganPoli->id,
                ],
                'spesialis_id' => $spIdPDKGEH,
                'email'        => 'poli-pd-3@rsba.com',
            ],
            [
                'karyawan' => [
                    'nip'               => 'DOK-PD-004',
                    'nik'               => '3374020505930014',
                    'pin_absen'         => 'PD004',
                    'nama'              => 'Fitriani Noor',
                    'gelar_depan'       => 'dr.',
                    'gelar_belakang'    => 'Sp.PD',
                    'jk'                => 'P',
                    'tempat_lahir'      => 'Purwokerto',
                    'tgl_lahir'         => '1993-05-05',
                    'status_pernikahan' => 'belum_menikah',
                    'hp'                => '08221200004',
                    'prov'              => 'Jawa Tengah',
                    'kab'               => 'Semarang',
                    'kec'               => 'Semarang Barat',
                    'desa'              => 'Gisikdrono',
                    'alamat'            => 'Jl. Gisikdrono No. 8',
                    'agama'             => 'islam',
                    'suku'              => 'Jawa',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2021-03-01',
                    'kategori_kerja'    => 'shift',
                    'pendidikan_setara' => 'Profesi Dokter Spesialis',
                    'no_sip'            => 'SIP.204/DINKES/2023',
                    'sip_berakhir'      => '2028-03-01',
                    'npwp'              => '077.777.777.7-444.000',
                    'bpjs_kesehatan'    => '0022200000004',
                    'bpjs_tk'           => 'KPJ0022200004',
                    'nama_bank'         => 'BCA',
                    'no_rekening'       => '0022200004',
                    'ruangan_id'        => $ruanganPoli->id,
                ],
                'spesialis_id' => $spIdPD,
                'email'        => 'poli-pd-4@rsba.com',
            ],
        ];

        // ─── 4. INSERT / UPDATE SETIAP DOKTER ─────────────────────────────────
        foreach ($dokterList as $data) {
            // 4a. Karyawan
            $karyawan = Karyawan::updateOrCreate(
                ['nip' => $data['karyawan']['nip']],
                $data['karyawan']
            );

            // 4b. Tabel dokter (link karyawan → spesialisasi)
            DB::table('dokter')->updateOrInsert(
                ['karyawan_id' => $karyawan->id],
                [
                    'spesialis_id' => $data['spesialis_id'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );

            // 4c. Akun login
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'password'    => '1234',
                    'karyawan_id' => $karyawan->id,
                ]
            );

            $this->command->info("✓ {$karyawan->full_nama} [{$data['email']}] (Dokter Poli)");
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('');
        $this->command->info('DokterPoliSeeder selesai. 4 Dokter Poli Penyakit Dalam:');
        $this->command->info('  poli-pd-1@rsba.com  → dr. Rizky Pratama, Sp.PD');
        $this->command->info('  poli-pd-2@rsba.com  → dr. Dewi Kusuma, Sp.PD');
        $this->command->info('  poli-pd-3@rsba.com  → dr. Hendra Wijaya, Sp.PD-KGEH');
        $this->command->info('  poli-pd-4@rsba.com  → dr. Fitriani Noor, Sp.PD');
        $this->command->info('  Password semua: 1234');
    }
}
