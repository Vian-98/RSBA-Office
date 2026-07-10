<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummySdmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat 4 Ruangan
        $ruangans = [
            ['nama' => 'IGD', 'is_active' => true],
            ['nama' => 'ICU', 'is_active' => true],
            ['nama' => 'Poli Umum', 'is_active' => true],
            ['nama' => 'Ruang Rawat Inap Melati', 'is_active' => true],
        ];
        foreach ($ruangans as $r) {
            \App\Models\Ruangan::firstOrCreate(['nama' => $r['nama']], $r);
        }
        $r_igd = \App\Models\Ruangan::where('nama', 'IGD')->first()->id;
        $r_icu = \App\Models\Ruangan::where('nama', 'ICU')->first()->id;
        $r_poli = \App\Models\Ruangan::where('nama', 'Poli Umum')->first()->id;
        $r_melati = \App\Models\Ruangan::where('nama', 'Ruang Rawat Inap Melati')->first()->id;

        // 2. Buat 4 Bagian
        $bagians = [
            ['nama' => 'Pelayanan Medis', 'is_active' => true, 'group' => 'medis'],
            ['nama' => 'Keperawatan', 'is_active' => true, 'group' => 'medis'],
            ['nama' => 'Penunjang Medis', 'is_active' => true, 'group' => 'penunjang'],
            ['nama' => 'Manajemen SDM', 'is_active' => true, 'group' => 'manajemen'],
        ];
        foreach ($bagians as $b) {
            \App\Models\Sdm\Bagian::firstOrCreate(['nama' => $b['nama']], $b);
        }
        $b_medis = \App\Models\Sdm\Bagian::where('nama', 'Pelayanan Medis')->first()->id;
        $b_keperawatan = \App\Models\Sdm\Bagian::where('nama', 'Keperawatan')->first()->id;

        // 3. Buat 4 Shift
        $shifts = [
            ['kode' => 'REGULER', 'nama' => 'Reguler', 'jam_masuk' => '08:00:00', 'jam_keluar' => '16:00:00', 'aktif' => true],
            ['kode' => 'PAGI', 'nama' => 'Shift Pagi', 'jam_masuk' => '07:00:00', 'jam_keluar' => '14:00:00', 'aktif' => true],
            ['kode' => 'SIANG', 'nama' => 'Shift Siang', 'jam_masuk' => '14:00:00', 'jam_keluar' => '21:00:00', 'aktif' => true],
            ['kode' => 'MALAM', 'nama' => 'Shift Malam', 'jam_masuk' => '21:00:00', 'jam_keluar' => '07:00:00', 'lintas_hari' => true, 'aktif' => true],
        ];
        foreach ($shifts as $s) {
            \App\Models\Sdm\JadwalShift::firstOrCreate(['kode' => $s['kode']], $s);
        }

        // 4. Buat 4 Jabatan
        $jabatans = [
            ['nama' => 'Kepala IGD', 'bagian_id' => $b_medis, 'parent_id' => 1],
            ['nama' => 'Perawat Pelaksana IGD', 'bagian_id' => $b_keperawatan, 'parent_id' => 1],
            ['nama' => 'Dokter Jaga ICU', 'bagian_id' => $b_medis, 'parent_id' => 1],
            ['nama' => 'Staf SDM', 'bagian_id' => \App\Models\Sdm\Bagian::where('nama', 'Manajemen SDM')->first()->id, 'parent_id' => 1],
        ];
        foreach ($jabatans as $j) {
            \App\Models\Sdm\Jabatan::firstOrCreate(['nama' => $j['nama']], $j);
        }

        // 5. Buat 4 Karyawan
        $karyawans = [
            [
                'nip' => 'NIP-001', 'nik' => '3301', 'nama' => 'Dr. Andi', 'jk' => 'L',
                'tgl_lahir' => '1980-01-01', 'hp' => '0811', 'prov' => 'Jateng', 'kab' => 'Semarang',
                'kec' => 'Tengah', 'desa' => 'Tengah', 'alamat' => '-', 'agama' => 'islam',
                'status' => 'tetap', 'kategori_kerja' => 'reguler', 'tgl_masuk' => '2020-01-01',
                'ruangan_id' => $r_igd
            ],
            [
                'nip' => 'NIP-002', 'nik' => '3302', 'nama' => 'Suster Budi', 'jk' => 'P',
                'tgl_lahir' => '1990-01-01', 'hp' => '0812', 'prov' => 'Jateng', 'kab' => 'Semarang',
                'kec' => 'Tengah', 'desa' => 'Tengah', 'alamat' => '-', 'agama' => 'islam',
                'status' => 'tetap', 'kategori_kerja' => 'shift', 'tgl_masuk' => '2021-01-01',
                'ruangan_id' => $r_igd
            ],
            [
                'nip' => 'NIP-003', 'nik' => '3303', 'nama' => 'Dr. Cici', 'jk' => 'P',
                'tgl_lahir' => '1985-01-01', 'hp' => '0813', 'prov' => 'Jateng', 'kab' => 'Semarang',
                'kec' => 'Tengah', 'desa' => 'Tengah', 'alamat' => '-', 'agama' => 'islam',
                'status' => 'tetap', 'kategori_kerja' => 'shift', 'tgl_masuk' => '2019-01-01',
                'ruangan_id' => $r_icu
            ],
            [
                'nip' => 'NIP-004', 'nik' => '3304', 'nama' => 'Pak Dedi', 'jk' => 'L',
                'tgl_lahir' => '1995-01-01', 'hp' => '0814', 'prov' => 'Jateng', 'kab' => 'Semarang',
                'kec' => 'Tengah', 'desa' => 'Tengah', 'alamat' => '-', 'agama' => 'islam',
                'status' => 'kontrak', 'kategori_kerja' => 'reguler', 'tgl_masuk' => '2022-01-01',
                'ruangan_id' => $r_poli
            ],
        ];
        foreach ($karyawans as $k) {
            \App\Models\Sdm\Karyawan::firstOrCreate(['nip' => $k['nip']], $k);
        }

        // 6. Assign KaryawanJabatan
        $k_andi = \App\Models\Sdm\Karyawan::where('nip', 'NIP-001')->first()->id;
        $k_budi = \App\Models\Sdm\Karyawan::where('nip', 'NIP-002')->first()->id;
        
        \App\Models\Sdm\KaryawanJabatan::firstOrCreate([
            'karyawan_id' => $k_andi,
            'jabatan_id' => \App\Models\Sdm\Jabatan::where('nama', 'Kepala IGD')->first()->id,
            'tgl_mulai' => '2020-01-01'
        ]);
        \App\Models\Sdm\KaryawanJabatan::firstOrCreate([
            'karyawan_id' => $k_budi,
            'jabatan_id' => \App\Models\Sdm\Jabatan::where('nama', 'Perawat Pelaksana IGD')->first()->id,
            'tgl_mulai' => '2021-01-01'
        ]);

        // 7. Assign Koordinator (Dr. Andi sebagai Koordinator Bagian Pelayanan Medis)
        \App\Models\Sdm\BagianKoordinator::firstOrCreate([
            'bagian_id' => $b_medis,
            'karyawan_id' => $k_andi,
            'aktif' => true,
        ]);
        
        // 8. Atur Bagian Shift (Pelayanan Medis boleh semua shift)
        $shiftIds = \App\Models\Sdm\JadwalShift::pluck('id');
        foreach ($shiftIds as $sId) {
            \App\Models\Sdm\BagianShift::firstOrCreate([
                'bagian_id' => $b_medis,
                'shift_id' => $sId
            ]);
            \App\Models\Sdm\BagianShift::firstOrCreate([
                'bagian_id' => $b_keperawatan,
                'shift_id' => $sId
            ]);
        }
    }
}
