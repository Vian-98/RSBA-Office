<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * DokterAllPoliSeeder
 *
 * Seed minimal 4 dokter untuk setiap Poli beserta:
 *  - Record sdm_karyawan
 *  - Record dokter (karyawan_id → spesialisasi)
 *  - Akun User (login)
 *  - Jadwal Kerja tipe=dokter bulan Agustus 2026 (draft)
 *  - Detail jadwal (PAGI setiap hari)
 */
class DokterAllPoliSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── Pastikan Shift PAGI ada ─────────────────────────────────────────────
        $shiftPagi = DB::table('sdm_jadwal_shift')
            ->where('kode', 'PAGI')
            ->where('aktif', true)
            ->first();

        if (!$shiftPagi) {
            $shiftPagi = DB::table('sdm_jadwal_shift')->where('kode', 'PAGI')->first();
        }

        $shiftId = $shiftPagi ? $shiftPagi->id : null;

        // ── Spesialisasi ────────────────────────────────────────────────────────
        $spesialisasi = $this->ensureSpesialisasi();

        // ── Data dokter per Poli ────────────────────────────────────────────────
        $poliDokter = [
            'Poli Umum' => [
                'spesialis' => 'Dokter Umum',
                'kode' => 'PKPU',
                'dokters' => [
                    ['nama' => 'Arif Budiman',       'jk' => 'L', 'email' => 'dok-umum-1@rsba.com', 'tgl_lahir' => '1990-03-10'],
                    ['nama' => 'Sari Indrawati',     'jk' => 'P', 'email' => 'dok-umum-2@rsba.com', 'tgl_lahir' => '1992-07-22'],
                    ['nama' => 'Budi Santoso',       'jk' => 'L', 'email' => 'dok-umum-3@rsba.com', 'tgl_lahir' => '1988-11-05'],
                    ['nama' => 'Nurul Hidayah',      'jk' => 'P', 'email' => 'dok-umum-4@rsba.com', 'tgl_lahir' => '1994-02-28'],
                ],
            ],
            'Poli Gigi' => [
                'spesialis' => 'Dokter Gigi',
                'kode' => 'PKGG',
                'dokters' => [
                    ['nama' => 'Rini Setiawati',     'jk' => 'P', 'email' => 'dok-gigi-1@rsba.com', 'tgl_lahir' => '1989-06-15'],
                    ['nama' => 'Doni Prasetyo',      'jk' => 'L', 'email' => 'dok-gigi-2@rsba.com', 'tgl_lahir' => '1991-09-20'],
                    ['nama' => 'Lestari Wahyuni',    'jk' => 'P', 'email' => 'dok-gigi-3@rsba.com', 'tgl_lahir' => '1993-04-12'],
                    ['nama' => 'Wahyu Kurniawan',    'jk' => 'L', 'email' => 'dok-gigi-4@rsba.com', 'tgl_lahir' => '1987-12-30'],
                ],
            ],
            'Poli Anak' => [
                'spesialis' => 'Sp.A',
                'kode' => 'PKPA',
                'dokters' => [
                    ['nama' => 'Mira Anggraini',     'jk' => 'P', 'email' => 'dok-anak-1@rsba.com', 'tgl_lahir' => '1988-01-18'],
                    ['nama' => 'Farhan Ardiansyah',  'jk' => 'L', 'email' => 'dok-anak-2@rsba.com', 'tgl_lahir' => '1990-05-25'],
                    ['nama' => 'Yuliana Putri',      'jk' => 'P', 'email' => 'dok-anak-3@rsba.com', 'tgl_lahir' => '1992-08-14'],
                    ['nama' => 'Rahmat Hidayat',     'jk' => 'L', 'email' => 'dok-anak-4@rsba.com', 'tgl_lahir' => '1986-10-03'],
                ],
            ],
            'Poli Penyakit Dalam' => [
                'spesialis' => 'Sp.PD',
                'kode' => 'PKPD',
                'dokters' => [
                    ['nama' => 'Rizky Pratama',      'jk' => 'L', 'email' => 'poli-pd-1@rsba.com',  'tgl_lahir' => '1988-03-15'],
                    ['nama' => 'Dewi Kusuma',        'jk' => 'P', 'email' => 'poli-pd-2@rsba.com',  'tgl_lahir' => '1991-07-07'],
                    ['nama' => 'Hendra Wijaya',      'jk' => 'L', 'email' => 'poli-pd-3@rsba.com',  'tgl_lahir' => '1985-12-12'],
                    ['nama' => 'Fitriani Noor',      'jk' => 'P', 'email' => 'poli-pd-4@rsba.com',  'tgl_lahir' => '1993-05-05'],
                ],
            ],
            'Poli Kandungan (Obgyn)' => [
                'spesialis' => 'Sp.OG',
                'kode' => 'PKOG',
                'dokters' => [
                    ['nama' => 'Endah Sulistyorini', 'jk' => 'P', 'email' => 'dok-og-1@rsba.com',   'tgl_lahir' => '1987-04-08'],
                    ['nama' => 'Andri Wicaksono',    'jk' => 'L', 'email' => 'dok-og-2@rsba.com',   'tgl_lahir' => '1989-11-17'],
                    ['nama' => 'Fitria Sari',        'jk' => 'P', 'email' => 'dok-og-3@rsba.com',   'tgl_lahir' => '1991-02-22'],
                    ['nama' => 'Bagas Permana',      'jk' => 'L', 'email' => 'dok-og-4@rsba.com',   'tgl_lahir' => '1985-06-30'],
                ],
            ],
            'Poli Bedah' => [
                'spesialis' => 'Sp.B',
                'kode' => 'PKBD',
                'dokters' => [
                    ['nama' => 'Hanif Hakim',        'jk' => 'L', 'email' => 'dok-bedah-1@rsba.com','tgl_lahir' => '1983-07-19'],
                    ['nama' => 'Retno Wulandari',    'jk' => 'P', 'email' => 'dok-bedah-2@rsba.com','tgl_lahir' => '1988-09-04'],
                    ['nama' => 'Lukman Hakim',       'jk' => 'L', 'email' => 'dok-bedah-3@rsba.com','tgl_lahir' => '1986-01-23'],
                    ['nama' => 'Ayu Rahmawati',      'jk' => 'P', 'email' => 'dok-bedah-4@rsba.com','tgl_lahir' => '1990-12-11'],
                ],
            ],
            'Poli Saraf' => [
                'spesialis' => 'Sp.S',
                'kode' => 'PKSF',
                'dokters' => [
                    ['nama' => 'Teguh Prasetyo',     'jk' => 'L', 'email' => 'dok-saraf-1@rsba.com','tgl_lahir' => '1984-08-27'],
                    ['nama' => 'Dian Maharani',      'jk' => 'P', 'email' => 'dok-saraf-2@rsba.com','tgl_lahir' => '1989-03-16'],
                    ['nama' => 'Faisal Rahman',      'jk' => 'L', 'email' => 'dok-saraf-3@rsba.com','tgl_lahir' => '1991-05-09'],
                    ['nama' => 'Laras Kusuma',       'jk' => 'P', 'email' => 'dok-saraf-4@rsba.com','tgl_lahir' => '1993-11-01'],
                ],
            ],
            'Poli Mata' => [
                'spesialis' => 'Sp.M',
                'kode' => 'PKMT',
                'dokters' => [
                    ['nama' => 'Rudi Hartono',       'jk' => 'L', 'email' => 'dok-mata-1@rsba.com', 'tgl_lahir' => '1985-02-14'],
                    ['nama' => 'Nining Astuti',      'jk' => 'P', 'email' => 'dok-mata-2@rsba.com', 'tgl_lahir' => '1990-08-05'],
                    ['nama' => 'Galih Saputro',      'jk' => 'L', 'email' => 'dok-mata-3@rsba.com', 'tgl_lahir' => '1988-04-20'],
                    ['nama' => 'Wina Marlina',       'jk' => 'P', 'email' => 'dok-mata-4@rsba.com', 'tgl_lahir' => '1992-10-15'],
                ],
            ],
            'Poli THT' => [
                'spesialis' => 'Sp.THT-KL',
                'kode' => 'PKTHT',
                'dokters' => [
                    ['nama' => 'Irwan Syahputra',    'jk' => 'L', 'email' => 'dok-tht-1@rsba.com',  'tgl_lahir' => '1986-09-30'],
                    ['nama' => 'Kartika Dewi',       'jk' => 'P', 'email' => 'dok-tht-2@rsba.com',  'tgl_lahir' => '1991-06-18'],
                    ['nama' => 'Bambang Susilo',     'jk' => 'L', 'email' => 'dok-tht-3@rsba.com',  'tgl_lahir' => '1987-12-07'],
                    ['nama' => 'Yanti Rahayu',       'jk' => 'P', 'email' => 'dok-tht-4@rsba.com',  'tgl_lahir' => '1994-03-25'],
                ],
            ],
            'Poli Jantung' => [
                'spesialis' => 'Sp.JP',
                'kode' => 'PKJP',
                'dokters' => [
                    ['nama' => 'Agus Wirawan',       'jk' => 'L', 'email' => 'dok-jp-1@rsba.com',   'tgl_lahir' => '1981-05-12'],
                    ['nama' => 'Sri Wahyuningsih',   'jk' => 'P', 'email' => 'dok-jp-2@rsba.com',   'tgl_lahir' => '1986-10-24'],
                    ['nama' => 'Hadi Nugroho',       'jk' => 'L', 'email' => 'dok-jp-3@rsba.com',   'tgl_lahir' => '1983-07-08'],
                    ['nama' => 'Rina Damayanti',     'jk' => 'P', 'email' => 'dok-jp-4@rsba.com',   'tgl_lahir' => '1989-01-19'],
                ],
            ],
            'Poli Paru' => [
                'spesialis' => 'Sp.P',
                'kode' => 'PKPP',
                'dokters' => [
                    ['nama' => 'Eko Purnomo',        'jk' => 'L', 'email' => 'dok-paru-1@rsba.com', 'tgl_lahir' => '1984-03-21'],
                    ['nama' => 'Tia Lestari',        'jk' => 'P', 'email' => 'dok-paru-2@rsba.com', 'tgl_lahir' => '1990-11-14'],
                    ['nama' => 'Seno Prasetyo',      'jk' => 'L', 'email' => 'dok-paru-3@rsba.com', 'tgl_lahir' => '1987-08-03'],
                    ['nama' => 'Mega Wulandari',     'jk' => 'P', 'email' => 'dok-paru-4@rsba.com', 'tgl_lahir' => '1993-06-27'],
                ],
            ],
            'Poli Ortopedi' => [
                'spesialis' => 'Sp.OT',
                'kode' => 'PKOT',
                'dokters' => [
                    ['nama' => 'Dimas Aditya',       'jk' => 'L', 'email' => 'dok-ot-1@rsba.com',   'tgl_lahir' => '1983-02-09'],
                    ['nama' => 'Anggun Pertiwi',     'jk' => 'P', 'email' => 'dok-ot-2@rsba.com',   'tgl_lahir' => '1988-07-31'],
                    ['nama' => 'Yudi Hartawan',      'jk' => 'L', 'email' => 'dok-ot-3@rsba.com',   'tgl_lahir' => '1985-04-16'],
                    ['nama' => 'Novia Kristiani',    'jk' => 'P', 'email' => 'dok-ot-4@rsba.com',   'tgl_lahir' => '1991-09-23'],
                ],
            ],
            'Poli Kulit & Kelamin' => [
                'spesialis' => 'Sp.KK',
                'kode' => 'PKKK',
                'dokters' => [
                    ['nama' => 'Aditya Nugraha',     'jk' => 'L', 'email' => 'dok-kk-1@rsba.com',   'tgl_lahir' => '1987-10-05'],
                    ['nama' => 'Bella Octaviani',    'jk' => 'P', 'email' => 'dok-kk-2@rsba.com',   'tgl_lahir' => '1992-01-28'],
                    ['nama' => 'Chandra Wibowo',     'jk' => 'L', 'email' => 'dok-kk-3@rsba.com',   'tgl_lahir' => '1989-05-17'],
                    ['nama' => 'Diana Sari',         'jk' => 'P', 'email' => 'dok-kk-4@rsba.com',   'tgl_lahir' => '1994-08-11'],
                ],
            ],
            'Poli Rehabilitasi Medik' => [
                'spesialis' => 'Sp.KFR',
                'kode' => 'PKRM',
                'dokters' => [
                    ['nama' => 'Efendi Kurniawan',   'jk' => 'L', 'email' => 'dok-rm-1@rsba.com',   'tgl_lahir' => '1985-11-22'],
                    ['nama' => 'Fitri Handayani',    'jk' => 'P', 'email' => 'dok-rm-2@rsba.com',   'tgl_lahir' => '1990-04-07'],
                    ['nama' => 'Gunawan Pratama',    'jk' => 'L', 'email' => 'dok-rm-3@rsba.com',   'tgl_lahir' => '1988-06-13'],
                    ['nama' => 'Hana Febriyanti',    'jk' => 'P', 'email' => 'dok-rm-4@rsba.com',   'tgl_lahir' => '1993-12-02'],
                ],
            ],
        ];

        $bulan = 8;
        $tahun = 2026;
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        $nipCounter = 400;

        foreach ($poliDokter as $namaRuangan => $poliData) {
            // ── Pastikan ruangan ada ─────────────────────────────────────────────
            $ruangan = Ruangan::firstOrCreate(
                ['nama' => $namaRuangan],
                ['is_active' => true]
            );

            // ── Pastikan spesialisasi ada ────────────────────────────────────────
            $spesialisasiId = $this->getOrCreateSpesialisasi($poliData['spesialis'], $spesialisasi);

            // ── Pastikan jadwal kerja tipe=dokter ada ────────────────────────────
            $jadwalKerja = JadwalKerja::firstOrCreate(
                [
                    'ruangan_id' => $ruangan->id,
                    'bulan'      => $bulan,
                    'tahun'      => $tahun,
                    'tipe'       => 'dokter',
                ],
                [
                    'bagian_id'   => $ruangan->bagian_id,
                    'status'      => 'draft',
                    'dibuat_oleh' => 1,
                ]
            );

            foreach ($poliData['dokters'] as $idx => $dokterData) {
                $nipCounter++;
                $kodeNum = str_pad($nipCounter, 3, '0', STR_PAD_LEFT);
                $nip     = "DOK-{$poliData['kode']}-{$kodeNum}";
                $nik     = '33' . rand(10, 74) . '0' . rand(10, 99) . rand(10, 99) . rand(1000, 9999) . rand(10, 99);
                $pinAbsen = $poliData['kode'] . $kodeNum;

                $gelarDepan  = 'dr.';
                $gelarBelakang = $poliData['spesialis'];
                // Penyesuaian gelar depan berdasarkan jenis spesialisasi
                if (in_array($poliData['spesialis'], ['Dokter Umum', 'Dokter Gigi'])) {
                    $gelarDepan = $poliData['spesialis'] === 'Dokter Gigi' ? 'drg.' : 'dr.';
                    $gelarBelakang = '';
                }

                // ── Karyawan ─────────────────────────────────────────────────────
                $karyawan = Karyawan::updateOrCreate(
                    ['nip' => $nip],
                    [
                        'nik'               => $nik,
                        'pin_absen'         => $pinAbsen,
                        'nama'              => $dokterData['nama'],
                        'gelar_depan'       => $gelarDepan,
                        'gelar_belakang'    => $gelarBelakang,
                        'jk'                => $dokterData['jk'],
                        'tempat_lahir'      => 'Semarang',
                        'tgl_lahir'         => $dokterData['tgl_lahir'],
                        'status_pernikahan' => 'menikah',
                        'hp'                => '0822' . rand(10000000, 99999999),
                        'prov'              => 'Jawa Tengah',
                        'kab'               => 'Semarang',
                        'kec'               => 'Tembalang',
                        'desa'              => 'Bulusan',
                        'alamat'            => 'Jl. RSBA No. ' . ($nipCounter),
                        'agama'             => 'islam',
                        'suku'              => 'Jawa',
                        'status'            => 'tetap',
                        'tgl_masuk'         => '2020-01-01',
                        'kategori_kerja'    => 'shift',
                        'pendidikan_setara' => 'Profesi Dokter Spesialis',
                        'no_sip'            => 'SIP.' . $nipCounter . '/DINKES/2023',
                        'sip_berakhir'      => '2028-01-01',
                        'nama_bank'         => 'BRI',
                        'no_rekening'       => '00' . $nipCounter . '00',
                        'ruangan_id'        => $ruangan->id,
                    ]
                );

                // ── Tabel dokter ──────────────────────────────────────────────────
                DB::table('dokter')->updateOrInsert(
                    ['karyawan_id' => $karyawan->id],
                    [
                        'spesialis_id' => $spesialisasiId,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );

                // ── Akun User ─────────────────────────────────────────────────────
                $user = User::updateOrCreate(
                    ['email' => $dokterData['email']],
                    [
                        'password'    => Hash::make('1234'),
                        'karyawan_id' => $karyawan->id,
                    ]
                );

                // ── Detail Jadwal (PAGI tiap hari) ────────────────────────────────
                if ($shiftId) {
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $tanggal = Carbon::create($tahun, $bulan, $d)->toDateString();
                        $exists = DB::table('sdm_jadwal_kerja_detail')
                            ->where('jadwal_kerja_id', $jadwalKerja->id)
                            ->where('karyawan_id', $karyawan->id)
                            ->where('tanggal', $tanggal)
                            ->exists();
                        if (!$exists) {
                            DB::table('sdm_jadwal_kerja_detail')->insert([
                                'jadwal_kerja_id'  => $jadwalKerja->id,
                                'karyawan_id'      => $karyawan->id,
                                'shift_id'         => $shiftId,
                                'tanggal'          => $tanggal,
                                'status_kehadiran' => 'belum_dicek',
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }
                }

                $this->command->info("  ✓ {$karyawan->full_nama} [{$dokterData['email']}] → {$namaRuangan}");
            }

            $this->command->info("  📅 Jadwal Dokter {$namaRuangan} Agustus 2026 → ID={$jadwalKerja->id}");
        }

        Schema::enableForeignKeyConstraints();

        $this->command->info('');
        $this->command->info('DokterAllPoliSeeder selesai.');
        $this->command->info('Total: 14 Poli × 4 Dokter = 56 Dokter');
        $this->command->info('Password semua: 1234');
    }

    /**
     * Pastikan data spesialisasi ada di DB dan kembalikan map singkatan→id.
     */
    private function ensureSpesialisasi(): array
    {
        $list = [
            'Dokter Umum'     => ['nama' => 'Dokter Umum',                              'singkatan' => 'Dokter Umum', 'kategori' => 'umum'],
            'Dokter Gigi'     => ['nama' => 'Dokter Gigi',                              'singkatan' => 'Dokter Gigi', 'kategori' => 'umum'],
            'Sp.A'            => ['nama' => 'Spesialis Anak',                           'singkatan' => 'Sp.A',        'kategori' => 'spesialis'],
            'Sp.PD'           => ['nama' => 'Spesialis Penyakit Dalam',                 'singkatan' => 'Sp.PD',       'kategori' => 'spesialis'],
            'Sp.OG'           => ['nama' => 'Spesialis Obstetri & Ginekologi',          'singkatan' => 'Sp.OG',       'kategori' => 'spesialis'],
            'Sp.B'            => ['nama' => 'Spesialis Bedah',                          'singkatan' => 'Sp.B',        'kategori' => 'spesialis'],
            'Sp.S'            => ['nama' => 'Spesialis Saraf',                          'singkatan' => 'Sp.S',        'kategori' => 'spesialis'],
            'Sp.M'            => ['nama' => 'Spesialis Mata',                           'singkatan' => 'Sp.M',        'kategori' => 'spesialis'],
            'Sp.THT-KL'       => ['nama' => 'Spesialis THT – Kepala Leher',            'singkatan' => 'Sp.THT-KL',   'kategori' => 'spesialis'],
            'Sp.JP'           => ['nama' => 'Spesialis Jantung & Pembuluh Darah',       'singkatan' => 'Sp.JP',       'kategori' => 'spesialis'],
            'Sp.P'            => ['nama' => 'Spesialis Paru',                           'singkatan' => 'Sp.P',        'kategori' => 'spesialis'],
            'Sp.OT'           => ['nama' => 'Spesialis Ortopedi & Traumatologi',        'singkatan' => 'Sp.OT',       'kategori' => 'spesialis'],
            'Sp.KK'           => ['nama' => 'Spesialis Kulit & Kelamin',                'singkatan' => 'Sp.KK',       'kategori' => 'spesialis'],
            'Sp.KFR'          => ['nama' => 'Spesialis Kedokteran Fisik & Rehabilitasi','singkatan' => 'Sp.KFR',      'kategori' => 'spesialis'],
        ];

        $map = [];
        foreach ($list as $key => $data) {
            $id = DB::table('dokter_spesialisasi')->where('singkatan', $data['singkatan'])->value('id');
            if (!$id) {
                $id = DB::table('dokter_spesialisasi')->insertGetId([
                    'nama'       => $data['nama'],
                    'singkatan'  => $data['singkatan'],
                    'kategori'   => $data['kategori'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $map[$key] = $id;
        }
        return $map;
    }

    private function getOrCreateSpesialisasi(string $key, array $map): int
    {
        if (isset($map[$key])) {
            return $map[$key];
        }
        // Fallback: buat entri baru
        return DB::table('dokter_spesialisasi')->insertGetId([
            'nama'       => $key,
            'singkatan'  => $key,
            'kategori'   => 'spesialis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
