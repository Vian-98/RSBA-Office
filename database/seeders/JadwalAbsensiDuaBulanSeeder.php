<?php

namespace Database\Seeders;

use App\Enums\KategoriKerja;
use App\Enums\StatusJadwalKerja;
use App\Models\Ruangan;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\JabatanTingkat;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\KaryawanJabatan;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Sdm\RuanganShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

class JadwalAbsensiDuaBulanSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('===========================================================');
        $this->command->info('🚀 MENJALANKAN SEEDER: JADWAL & ABSENSI 2 BULAN (JAN-FEB 2026)');
        $this->command->info('===========================================================');

        // ---------------------------------------------------------------------
        // 1. PASTIKAN TINGKAT JABATAN & BAGIAN
        // ---------------------------------------------------------------------
        $this->command->info('📌 [1/6] Menyiapkan Master Tingkat Jabatan & Bagian...');

        $tingkatWadir = JabatanTingkat::firstOrCreate(['urutan' => 2], ['nama' => 'Wadir / Kepala Divisi', 'is_penyusun_jadwal' => false]);
        $tingkatKabid = JabatanTingkat::firstOrCreate(['urutan' => 3], ['nama' => 'Kepala Bagian / Kabid', 'is_penyusun_jadwal' => false]);
        $tingkatKoor  = JabatanTingkat::firstOrCreate(['urutan' => 4], ['nama' => 'Koordinator / Kepala Ruangan', 'is_penyusun_jadwal' => true]);
        $tingkatStaf  = JabatanTingkat::firstOrCreate(['urutan' => 5], ['nama' => 'Pelaksana / Staf Operasional', 'is_penyusun_jadwal' => false]);

        $bagianMedis = Bagian::firstOrCreate(['nama' => 'Pelayanan Medis'], ['group' => 'medis']);
        $bagianKeperawatan = Bagian::firstOrCreate(['nama' => 'Keperawatan & Rawat Inap'], ['group' => 'medis']);
        $bagianIgd = Bagian::firstOrCreate(['nama' => 'Instalasi Gawat Darurat'], ['group' => 'medis']);
        $bagianSdm = Bagian::firstOrCreate(['nama' => 'SDM & Umum'], ['group' => 'non_medis']);

        // ---------------------------------------------------------------------
        // 2. MASTER SHIFT & OVERRIDE
        // ---------------------------------------------------------------------
        $this->command->info('📌 [2/6] Menyiapkan Master Shift (Pagi, Siang, Malam, Reguler)...');

        $shiftPagi = JadwalShift::firstOrCreate(['kode' => 'PAGI'], [
            'nama'                  => 'Shift Pagi',
            'jam_masuk'             => '07:00:00',
            'jam_keluar'            => '14:00:00',
            'warna'                 => '#10B981',
            'lintas_hari'           => false,
            'aktif'                 => true,
            'toleransi_telat_menit' => 15,
        ]);

        $shiftSiang = JadwalShift::firstOrCreate(['kode' => 'SIANG'], [
            'nama'                  => 'Shift Siang',
            'jam_masuk'             => '14:00:00',
            'jam_keluar'            => '21:00:00',
            'warna'                 => '#3B82F6',
            'lintas_hari'           => false,
            'aktif'                 => true,
            'toleransi_telat_menit' => 15,
        ]);

        $shiftMalam = JadwalShift::firstOrCreate(['kode' => 'MALAM'], [
            'nama'                  => 'Shift Malam',
            'jam_masuk'             => '21:00:00',
            'jam_keluar'            => '07:00:00',
            'warna'                 => '#8B5CF6',
            'lintas_hari'           => true,
            'aktif'                 => true,
            'toleransi_telat_menit' => 15,
        ]);

        $shiftReguler = JadwalShift::firstOrCreate(['kode' => 'REGULER'], [
            'nama'                  => 'Reguler (Jam Kantor)',
            'jam_masuk'             => '08:00:00',
            'jam_keluar'            => '16:00:00',
            'warna'                 => '#64748B',
            'lintas_hari'           => false,
            'aktif'                 => true,
            'toleransi_telat_menit' => 15,
        ]);

        $shiftOff = JadwalShift::firstOrCreate(['kode' => 'OFF'], [
            'nama'                  => 'Libur Shift / OFF',
            'jam_masuk'             => '00:00:00',
            'jam_keluar'            => '00:00:00',
            'warna'                 => '#EF4444',
            'lintas_hari'           => false,
            'aktif'                 => true,
            'toleransi_telat_menit' => 0,
        ]);

        // ---------------------------------------------------------------------
        // 3. MASTER RUANGAN & ATRIBUT BYPASS DOKTER
        // ---------------------------------------------------------------------
        $this->command->info('📌 [3/6] Menyiapkan 3 Ruangan (IGD, VIP, Dokter Jaga Bypass)...');

        $ruanganIgd = Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)'], [
            'bagian_id' => $bagianIgd->id,
            'is_active' => true,
        ]);

        $ruanganVip = Ruangan::firstOrCreate(['nama' => 'Ruang Perawatan VIP'], [
            'bagian_id' => $bagianKeperawatan->id,
            'is_active' => true,
        ]);

        $ruanganDokter = Ruangan::firstOrCreate(['nama' => 'Koordinasi Dokter Jaga / Poli'], [
            'bagian_id' => $bagianMedis->id,
            'is_active' => true,
        ]);

        // Mapping shift ke setiap ruangan
        foreach ([$ruanganIgd, $ruanganVip, $ruanganDokter] as $rng) {
            foreach ([$shiftPagi, $shiftSiang, $shiftMalam, $shiftReguler] as $shf) {
                RuanganShift::firstOrCreate([
                    'ruangan_id' => $rng->id,
                    'shift_id'   => $shf->id,
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 4. JABATAN, KARYAWAN & USER AKUN
        // ---------------------------------------------------------------------
        $this->command->info('📌 [4/6] Menyiapkan Jabatan, Karyawan, dan User Multi-Aktor...');

        $jabWadirMedis = Jabatan::firstOrCreate(['nama' => 'wadir medis'], [
            'tingkat_id' => $tingkatWadir->id,
            'kode_surat' => 'WADIR-MEDIS',
        ]);
        $jabKoorIgd = Jabatan::firstOrCreate(['nama' => 'Kepala Ruangan IGD'], [
            'bagian_id'  => $bagianIgd->id,
            'tingkat_id' => $tingkatKoor->id,
            'kode_surat' => 'KARU-IGD',
        ]);
        $jabKoorVip = Jabatan::firstOrCreate(['nama' => 'Kepala Ruangan Rawat Inap VIP'], [
            'bagian_id'  => $bagianKeperawatan->id,
            'tingkat_id' => $tingkatKoor->id,
            'kode_surat' => 'KARU-VIP',
        ]);
        $jabPerawat = Jabatan::firstOrCreate(['nama' => 'Perawat Pelaksana'], [
            'bagian_id'  => $bagianKeperawatan->id,
            'tingkat_id' => $tingkatStaf->id,
            'kode_surat' => 'PERAWAT',
        ]);
        $jabDokter = Jabatan::firstOrCreate(['nama' => 'Dokter Umum / Jaga'], [
            'bagian_id'  => $bagianMedis->id,
            'tingkat_id' => $tingkatStaf->id,
            'kode_surat' => 'DOKTER',
        ]);

        // Helper fungsi membuat Karyawan + User
        $createPerson = function (array $karyawanData, array $userData, ?Jabatan $jabatan = null, ?Ruangan $ruangan = null, ?string $roleName = null) {
            $karyawan = Karyawan::where('nip', $karyawanData['nip'])->orWhere('pin_absen', $karyawanData['pin_absen'])->first();
            if (!$karyawan) {
                $karyawan = Karyawan::create(array_merge([
                    'nik'               => '187101' . rand(1000000000, 9999999999),
                    'jk'                => 'L',
                    'tgl_lahir'         => '1992-05-15',
                    'hp'                => '0812' . rand(10000000, 99999999),
                    'status_pernikahan' => 'menikah',
                    'prov'              => 'Lampung',
                    'kab'               => 'Bandar Lampung',
                    'kec'               => 'Kedaton',
                    'desa'              => 'Penengahan',
                    'alamat'            => 'Jl. ZA Pagar Alam No. 1',
                    'agama'             => 'islam',
                    'status'            => 'tetap',
                    'tgl_masuk'         => '2023-01-01',
                ], $karyawanData));
            } else {
                $karyawan->update($karyawanData);
            }

            if ($ruangan) {
                $karyawan->update(['ruangan_id' => $ruangan->id]);
                DB::table('sdm_kary_ruangan')->updateOrInsert(
                    ['karyawan_id' => $karyawan->id, 'ruangan_id' => $ruangan->id],
                    ['is_utama' => true, 'tgl_mulai' => '2025-01-01', 'created_at' => now(), 'updated_at' => now()]
                );
            }

            if ($jabatan) {
                DB::table('sdm_kary_jabatan')->updateOrInsert(
                    ['karyawan_id' => $karyawan->id, 'jabatan_id' => $jabatan->id],
                    [
                        'bagian_id'   => $jabatan->bagian_id,
                        'tgl_mulai'   => '2025-01-01',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }

            $user = User::where('email', $userData['email'])->orWhere('karyawan_id', $karyawan->id)->first();
            if (!$user) {
                $user = User::create([
                    'email'       => $userData['email'],
                    'password'    => Hash::make('1234'),
                    'karyawan_id' => $karyawan->id,
                ]);
            } else {
                $user->update([
                    'email'       => $userData['email'],
                    'karyawan_id' => $karyawan->id,
                ]);
            }

            if ($roleName && Role::where('name', $roleName)->exists()) {
                $user->syncRoles([$roleName]);
            }

            return [$karyawan, $user];
        };

        // A. WADIR MEDIS
        [$wadirMedisKary, $wadirMedisUser] = $createPerson(
            ['nip' => 'DOK-WADIR-001', 'pin_absen' => '2001', 'nama' => 'Dr. Eng. I Muhammad Faisal S.T., M.T.', 'kategori_kerja' => KategoriKerja::REGULER],
            ['name' => 'Dr. Eng. I Muhammad Faisal S.T., M.T.', 'email' => 'zkii0110011@gmail.com'],
            $jabWadirMedis,
            null,
            'Wadir-Medis-Keperawatan'
        );

        // B. KOORDINATOR 1: Karu IGD
        [$koorIgdKary, $koorIgdUser] = $createPerson(
            ['nip' => 'NIP-22230383', 'pin_absen' => '22230383', 'nama' => 'Fika Erisandy', 'kategori_kerja' => KategoriKerja::REGULER],
            ['name' => 'Fika Erisandy', 'email' => 'koor_igd@rsba.com'],
            $jabKoorIgd,
            $ruanganIgd,
            'Guest'
        );

        // C. KOORDINATOR 2: Karu VIP
        [$koorVipKary, $koorVipUser] = $createPerson(
            ['nip' => 'NIP-21220077', 'pin_absen' => '21220077', 'nama' => 'Agustina', 'kategori_kerja' => KategoriKerja::REGULER],
            ['name' => 'Agustina', 'email' => 'koor_vip@rsba.com'],
            $jabKoorVip,
            $ruanganVip,
            'Guest'
        );

        // D. RUANGAN 1 (IGD): 2 Orang Staf Shift
        [$stafIgd1, $stafIgd1User] = $createPerson(
            ['nip' => '22210264', 'pin_absen' => '22210264', 'nama' => 'Arif Pamungkas', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'Arif Pamungkas', 'email' => 'perawat@rsba.com'],
            $jabPerawat,
            $ruanganIgd,
            'Guest'
        );

        [$stafIgd2, $stafIgd2User] = $createPerson(
            ['nip' => '21220175', 'pin_absen' => '21220175', 'nama' => 'Afrizal', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'Afrizal', 'email' => 'koor_ugd@rsba.com'],
            $jabPerawat,
            $ruanganIgd,
            'Guest'
        );

        // E. RUANGAN 2 (VIP): 2 Orang Staf Shift
        [$stafVip1, $stafVip1User] = $createPerson(
            ['nip' => '22200233', 'pin_absen' => '22200233', 'nama' => 'Rosiana', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'Rosiana', 'email' => 'koor_poli_anak@rsba.com'],
            $jabPerawat,
            $ruanganVip,
            'Guest'
        );

        [$stafVip2, $stafVip2User] = $createPerson(
            ['nip' => '22200234', 'pin_absen' => '22200234', 'nama' => 'Siti Rahayu', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'Siti Rahayu', 'email' => 'staf_vip@rsba.com'],
            $jabPerawat,
            $ruanganVip,
            'Guest'
        );

        // F. RUANGAN 3 (DOKTER JAGA - BYPASS): 2 Orang Dokter Shift
        [$dokter1, $dokter1User] = $createPerson(
            ['nip' => 'DOK-PKPU-401', 'pin_absen' => '401', 'nama' => 'dr. Arif Budiman', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'dr. Arif Budiman', 'email' => 'dok-umum-1@rsba.com'],
            $jabDokter,
            $ruanganDokter,
            'Dokter'
        );

        [$dokter2, $dokter2User] = $createPerson(
            ['nip' => 'DOK-PKPU-402', 'pin_absen' => '402', 'nama' => 'dr. Sari Indrawati', 'kategori_kerja' => KategoriKerja::SHIFT],
            ['name' => 'dr. Sari Indrawati', 'email' => 'dok-umum-2@rsba.com'],
            $jabDokter,
            $ruanganDokter,
            'Dokter'
        );

        // ---------------------------------------------------------------------
        // 5. PENUGASAN KOORDINATOR RUANGAN (sdm_ruangan_koordinator)
        // ---------------------------------------------------------------------
        $this->command->info('📌 [5/6] Mengaitkan Penugasan Koordinator Ruangan...');

        RuanganKoordinator::updateOrInsert(
            ['ruangan_id' => $ruanganIgd->id],
            [
                'karyawan_id' => $koorIgdKary->id,
                'user_id'     => $koorIgdUser->id,
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        RuanganKoordinator::updateOrInsert(
            ['ruangan_id' => $ruanganVip->id],
            [
                'karyawan_id' => $koorVipKary->id,
                'user_id'     => $koorVipUser->id,
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        RuanganKoordinator::updateOrInsert(
            ['ruangan_id' => $ruanganDokter->id],
            [
                'karyawan_id' => $dokter1->id,
                'user_id'     => $dokter1User->id,
                'aktif'       => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // ---------------------------------------------------------------------
        // 6. GENERATE JADWAL KERJA (JANUARI & FEBRUARI 2026)
        // ---------------------------------------------------------------------
        $this->command->info('📌 [6/6] Membangun Jadwal Kerja Lengkap (Januari & Februari 2026)...');

        $bulanList = [
            1 => ['days' => 31, 'status' => StatusJadwalKerja::PUBLISHED],
            2 => ['days' => 28, 'status' => StatusJadwalKerja::PUBLISHED],
        ];

        $shiftCycle = [
            $shiftPagi->id,
            $shiftSiang->id,
            $shiftMalam->id,
            $shiftOff->id,
        ];

        $roomsSetup = [
            [
                'ruangan'     => $ruanganIgd,
                'koordinator' => $koorIgdKary,
                'karyawans'   => [$stafIgd1, $stafIgd2],
                'offset'      => [0, 2], // Person 1 starts Pagi, Person 2 starts Malam
                'tipe'        => 'karyawan',
            ],
            [
                'ruangan'     => $ruanganVip,
                'koordinator' => $koorVipKary,
                'karyawans'   => [$stafVip1, $stafVip2],
                'offset'      => [1, 3], // Person 1 starts Siang, Person 2 starts OFF
                'tipe'        => 'karyawan',
            ],
            [
                'ruangan'     => $ruanganDokter,
                'koordinator' => $dokter1,
                'karyawans'   => [$dokter1, $dokter2],
                'offset'      => [0, 2], // Dokter 1 starts Pagi, Dokter 2 starts Malam
                'tipe'        => 'dokter',
            ],
        ];

        $allScheduleDetails = []; // Untuk generate dataset absensi

        foreach ($bulanList as $bln => $blnConfig) {
            $totalDays = $blnConfig['days'];
            $status    = $blnConfig['status'];

            foreach ($roomsSetup as $roomInfo) {
                $rng  = $roomInfo['ruangan'];
                $koor = $roomInfo['koordinator'];

                $jadwal = JadwalKerja::updateOrCreate(
                    [
                        'ruangan_id' => $rng->id,
                        'bulan'      => $bln,
                        'tahun'      => 2026,
                    ],
                    [
                        'bagian_id'    => $rng->bagian_id,
                        'tipe'         => $roomInfo['tipe'],
                        'status'       => $status,
                        'dibuat_oleh'  => $koor->id,
                        'published_at' => '2026-01-01 08:00:00',
                        'updated_at'   => now(),
                    ]
                );

                // Bersihkan detail lama jika ada
                JadwalKerjaDetail::where('jadwal_kerja_id', $jadwal->id)->delete();

                foreach ($roomInfo['karyawans'] as $kIdx => $kary) {
                    $offset = $roomInfo['offset'][$kIdx] ?? 0;

                    for ($day = 1; $day <= $totalDays; $day++) {
                        $shiftIndex = ($day - 1 + $offset) % 4;
                        $chosenShiftId = $shiftCycle[$shiftIndex];
                        $tgl = sprintf('2026-%02d-%02d', $bln, $day);

                        $detail = JadwalKerjaDetail::create([
                            'jadwal_kerja_id' => $jadwal->id,
                            'karyawan_id'     => $kary->id,
                            'shift_id'        => $chosenShiftId,
                            'tanggal'         => $tgl,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);

                        $allScheduleDetails[] = [
                            'bulan'       => $bln,
                            'tanggal'     => $tgl,
                            'day'         => $day,
                            'karyawan'    => $kary,
                            'shift'       => $chosenShiftId == $shiftPagi->id ? $shiftPagi : ($chosenShiftId == $shiftSiang->id ? $shiftSiang : ($chosenShiftId == $shiftMalam->id ? $shiftMalam : $shiftOff)),
                            'is_off'      => $chosenShiftId == $shiftOff->id,
                            'ruangan'     => $rng,
                        ];
                    }
                }
            }
        }

        // ---------------------------------------------------------------------
        // 7. GENERATOR DATASET IMPORT ABSENSI (CSV & EXCEL)
        // ---------------------------------------------------------------------
        $this->command->info('📊 [7/7] Men-generate 4 File Dataset Import Absensi...');

        $this->generateImportFiles($allScheduleDetails);

        $this->command->info('===========================================================');
        $this->command->info('✅ SEEDER JADWAL & ABSENSI 2 BULAN BERHASIL DISELESAIKAN!');
        $this->command->info('===========================================================');
    }

    /**
     * Generate file CSV Raw Punch & Excel Rekap (.xlsx) untuk Jan & Feb 2026
     */
    private function generateImportFiles(array $details): void
    {
        $exportPaths = [
            base_path('../'),                       // Root Rsba workspace
            public_path('downloads/sample_import/'), // Public download
        ];

        foreach ($exportPaths as $dir) {
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        foreach ([1 => 'januari', 2 => 'februari'] as $blnNum => $blnName) {
            $monthDetails = array_filter($details, fn($d) => $d['bulan'] == $blnNum);

            // -----------------------------------------------------------------
            // A. GENERATE CSV RAW PUNCH LOG MESIN
            // -----------------------------------------------------------------
            $csvRows = [];
            $csvRows[] = ['No.', 'Employee ID', 'First Name', 'Department', 'Date', 'Time', 'Punch State', 'Data Sources'];

            $no = 1;
            foreach ($monthDetails as $item) {
                if ($item['is_off']) {
                    continue; // Tidak ada tap saat libur
                }

                $pin      = $item['karyawan']->pin_absen ?? $item['karyawan']->nip;
                $nama     = $item['karyawan']->nama;
                $dept     = $item['ruangan']->nama;
                $tglDmy   = Carbon::parse($item['tanggal'])->format('d-m-Y');
                $shift    = $item['shift'];

                // Variasi tap masuk realistis (5-10 menit sebelum shift)
                $masukCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_masuk)->subMinutes(rand(3, 12));
                $keluarCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_keluar)->addMinutes(rand(2, 10));
                if ($shift->kode === 'MALAM') {
                    $keluarCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_keluar)->addDay()->addMinutes(rand(2, 10));
                }

                $csvRows[] = [
                    $no++,
                    $pin,
                    $nama,
                    $dept,
                    $tglDmy,
                    $masukCarbon->format('H:i'),
                    'Check In',
                    'Device',
                ];

                $csvRows[] = [
                    $no++,
                    $pin,
                    $nama,
                    $dept,
                    $keluarCarbon->format('d-m-Y'),
                    $keluarCarbon->format('H:i'),
                    'Check Out',
                    'Device',
                ];
            }

            $csvFileName = "test_absensi_raw_{$blnName}_2026.csv";
            foreach ($exportPaths as $dir) {
                $targetFile = rtrim($dir, '/') . '/' . $csvFileName;
                $fp = fopen($targetFile, 'w');
                // UTF-8 BOM
                fputs($fp, "\xEF\xBB\xBF");
                foreach ($csvRows as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
            }
            $this->command->info("   📄 Berhasil membuat: {$csvFileName}");

            // -----------------------------------------------------------------
            // B. GENERATE EXCEL REKAP RAPI (.xlsx)
            // -----------------------------------------------------------------
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(ucfirst($blnName) . ' 2026');

            // Header metadata (6 baris pertama)
            $sheet->setCellValue('A1', 'REKAPITULASI PRESENSI PEGAWAI RS BINTANG AMIN');
            $sheet->setCellValue('A2', 'Periode : ' . ($blnNum == 1 ? '01-01-2026 s.d. 31-01-2026' : '01-02-2026 s.d. 28-02-2026'));
            $sheet->setCellValue('A3', 'Unit Kerja : Seluruh Instalasi (IGD, VIP, Dokter Jaga)');
            $sheet->setCellValue('A4', 'Tanggal Unduh : ' . date('d-m-Y H:i'));
            $sheet->setCellValue('A5', ''); // Baris kosong

            // Kolom Header pada baris 6 (Sesuai AbsensiImport.php)
            $headers = ['Employee ID', 'Nama Pegawai', 'Tanggal', 'Check In', 'Check Out', 'Clock In', 'Clock Out', 'Catatan'];
            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            foreach ($headers as $hIdx => $hText) {
                $sheet->setCellValue($colLetters[$hIdx] . '6', $hText);
            }

            $excelRow = 7;
            foreach ($monthDetails as $item) {
                if ($item['is_off']) {
                    continue; // Skip libur
                }

                $pin    = $item['karyawan']->pin_absen ?? $item['karyawan']->nip;
                $nama   = $item['karyawan']->nama;
                $tglDmy = Carbon::parse($item['tanggal'])->format('d-m-Y');
                $shift  = $item['shift'];

                $masukCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_masuk)->subMinutes(rand(2, 10));
                $keluarCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_keluar)->addMinutes(rand(2, 8));
                if ($shift->kode === 'MALAM') {
                    $keluarCarbon = Carbon::parse($item['tanggal'] . ' ' . $shift->jam_keluar)->addDay()->addMinutes(rand(2, 8));
                }

                $sheet->setCellValueExplicit('A' . $excelRow, (string) $pin, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('B' . $excelRow, $nama);
                $sheet->setCellValue('C' . $excelRow, $tglDmy);
                $sheet->setCellValue('D' . $excelRow, $masukCarbon->format('H:i'));
                $sheet->setCellValue('E' . $excelRow, $keluarCarbon->format('H:i'));
                $sheet->setCellValue('F' . $excelRow, $masukCarbon->format('H:i'));
                $sheet->setCellValue('G' . $excelRow, $keluarCarbon->format('H:i'));
                $sheet->setCellValue('H' . $excelRow, 'Hadir (' . $shift->nama . ')');

                $excelRow++;
            }

            // Auto width columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $xlsxFileName = "test_absensi_rekap_{$blnName}_2026.xlsx";
            $writer = new Xlsx($spreadsheet);
            foreach ($exportPaths as $dir) {
                $targetFile = rtrim($dir, '/') . '/' . $xlsxFileName;
                $writer->save($targetFile);
            }
            $this->command->info("   📊 Berhasil membuat: {$xlsxFileName}");
        }
    }
}
