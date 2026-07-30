<?php

namespace Database\Seeders;

use App\Enums\KategoriKerja;
use App\Enums\StatusJadwalKerja;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * JadwalAbsensiJuli2026Seeder
 *
 * Membuat:
 * 1. Jadwal kerja (sdm_jadwal_kerja + sdm_jadwal_kerja_detail) untuk Juli 2026
 * 2. Import log absensi (sdm_absensi_import_log) untuk periode 1-21 Juli 2026
 * 3. Data absensi staging (sdm_absensi_staging) untuk semua karyawan s.d. hari ini (21 Juli 2026)
 *    - Kehadiran realistis: masuk 07:00-08:00, pulang 15:30-17:00 (+ variasi keterlambatan)
 *    - Sabtu/Minggu: tidak ada record absen (libur)
 *    - Cuti Bersama: akan di-input manual via UI
 *
 * Cara menjalankan:
 *   php artisan db:seed --class=JadwalAbsensiJuli2026Seeder
 */
class JadwalAbsensiJuli2026Seeder extends Seeder
{
    const BULAN = 7;
    const TAHUN = 2026;
    // Hari ini maksimal: 21 Juli 2026 (tanggal berjalan)
    const HARI_BERJALAN = 21;

    // Libur Nasional Juli 2026 (tidak ada absen meskipun hari kerja)
    const LIBUR_NASIONAL = [
        // '2026-07-XX' => 'Keterangan Libur'
        // Juli 2026: tidak ada libur nasional di kalender reguler
    ];

    public function run(): void
    {
        $this->command->info('=== SEEDER: Jadwal & Absensi Juli 2026 ===');

        $bulan = self::BULAN;
        $tahun = self::TAHUN;
        $hariMaksimal = self::HARI_BERJALAN;
        $daysInMonth  = Carbon::create($tahun, $bulan, 1)->daysInMonth; // 31

        $shiftReguler = JadwalShift::where('kode', 'REGULER')->first();
        if (!$shiftReguler) {
            $this->command->error('Shift REGULER tidak ditemukan!');
            return;
        }

        $karyawans        = Karyawan::with('ruangan')->get();
        $firstKaryawanId  = Karyawan::min('id');
        $firstUserId      = DB::table('users')->min('id') ?? 1;

        $this->command->info("Ditemukan {$karyawans->count()} karyawan.");

        // ----------------------------------------------------------------
        // STEP 1: Buat jadwal kerja Juli 2026 per ruangan
        // ----------------------------------------------------------------
        $this->command->info('[1/3] Membuat jadwal kerja Juli 2026...');

        $jadwalCache = [];
        foreach ($karyawans as $karyawan) {
            $ruanganId  = $karyawan->ruangan_id;
            $cacheKey   = $ruanganId ?? 'null';

            if (!isset($jadwalCache[$cacheKey])) {
                $jadwalCache[$cacheKey] = JadwalKerja::firstOrCreate(
                    ['ruangan_id' => $ruanganId, 'bulan' => $bulan, 'tahun' => $tahun],
                    [
                        'status'      => StatusJadwalKerja::PUBLISHED,
                        'dibuat_oleh' => $firstKaryawanId,
                        'created_by'  => $firstKaryawanId,
                        'updated_by'  => $firstKaryawanId,
                    ]
                );
            }

            $jadwalKerja = $jadwalCache[$cacheKey];

            // Hapus detail lama karyawan ini (idempotent)
            JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
                ->where('karyawan_id', $karyawan->id)
                ->delete();

            $details = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date    = Carbon::create($tahun, $bulan, $day);
                $dateStr = $date->format('Y-m-d');
                $dow     = $date->dayOfWeekIso; // 1=Mon..7=Sun

                $isLiburNasional = isset(self::LIBUR_NASIONAL[$dateStr]);

                if ($karyawan->kategori_kerja?->value === 'reguler') {
                    if ($isLiburNasional || $dow >= 6) {
                        $shiftId = null;
                    } else {
                        $shiftId = $shiftReguler->id;
                    }
                } else {
                    // SHIFT pola rolling 8-hari
                    $offset = ($karyawan->id % 8);
                    $cycle  = (($day - 1 + $offset) % 8);
                    if ($cycle < 6) {
                        // Cari shift ruangan
                        $ruanShift = JadwalShift::when($ruanganId, function ($q) use ($ruanganId, $cycle) {
                            $tipe = $cycle < 2 ? 'pagi' : ($cycle < 4 ? 'siang' : 'malam');
                            $q->where('kode', 'like', '%_R' . $ruanganId)
                              ->where('nama', 'like', "%$tipe%");
                        })->orderBy('id')->first();

                        $shiftId = $ruanShift?->id ?? $shiftReguler->id;
                    } else {
                        $shiftId = null; // hari libur roster
                    }
                }

                $details[] = [
                    'jadwal_kerja_id'  => $jadwalKerja->id,
                    'karyawan_id'      => $karyawan->id,
                    'tanggal'          => $dateStr,
                    'shift_id'         => $shiftId,
                    'status_kehadiran' => 'belum_dicek',
                    'catatan'          => $isLiburNasional ? (self::LIBUR_NASIONAL[$dateStr] ?? 'Libur Nasional') : null,
                    'created_by'       => $firstKaryawanId,
                    'updated_by'       => $firstKaryawanId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            JadwalKerjaDetail::insert($details);
        }

        $this->command->info('   ✓ Jadwal kerja Juli 2026 selesai dibuat.');

        // ----------------------------------------------------------------
        // STEP 2: Buat import log absensi (satu batch per-minggu s.d. hari ini)
        // ----------------------------------------------------------------
        $this->command->info('[2/3] Membuat import log absensi...');

        // Buat 3 batch log: Minggu 1 (1-6), Minggu 2 (7-13), Minggu 3 (14-21)
        $batches = [
            ['awal' => '2026-07-01', 'akhir' => '2026-07-06', 'label' => 'Absensi Juli 2026 Minggu 1'],
            ['awal' => '2026-07-07', 'akhir' => '2026-07-13', 'label' => 'Absensi Juli 2026 Minggu 2'],
            ['awal' => '2026-07-14', 'akhir' => '2026-07-21', 'label' => 'Absensi Juli 2026 Minggu 3'],
        ];

        $importLogs = [];
        foreach ($batches as $batch) {
            $log = AbsensiImportLog::firstOrCreate(
                ['nama_file' => $batch['label']],
                [
                    'periode_awal'    => $batch['awal'],
                    'periode_akhir'   => $batch['akhir'],
                    'total_baris'     => 0,
                    'baris_matched'   => 0,
                    'baris_unmatched' => 0,
                    'baris_anomali'   => 0,
                    'status'          => 'direkonsiliasi',
                    'diunggah_oleh'   => $firstUserId,
                ]
            );
            $importLogs[] = $log;
        }

        $this->command->info('   ✓ Import log absensi dibuat (' . count($importLogs) . ' batch).');

        // ----------------------------------------------------------------
        // STEP 3: Buat data absensi staging 1-21 Juli 2026
        //         (hanya hari kerja, skip Sabtu/Minggu & libur nasional)
        // ----------------------------------------------------------------
        $this->command->info('[3/3] Membuat data absensi staging...');

        // Hapus staging lama untuk periode ini agar idempotent
        AbsensiStaging::whereIn('import_batch_id', collect($importLogs)->pluck('id'))
            ->delete();

        $stagingRows = [];
        $totalRows   = 0;

        foreach ($karyawans as $karyawan) {
            for ($day = 1; $day <= $hariMaksimal; $day++) {
                $date    = Carbon::create($tahun, $bulan, $day);
                $dateStr = $date->format('Y-m-d');
                $dow     = $date->dayOfWeekIso;

                // Skip Sabtu, Minggu, dan libur nasional
                if ($dow >= 6 || isset(self::LIBUR_NASIONAL[$dateStr])) {
                    continue;
                }

                // Tentukan batch log yang sesuai
                $batchIdx = match (true) {
                    $day <= 6  => 0,
                    $day <= 13 => 1,
                    default    => 2,
                };
                $batchId = $importLogs[$batchIdx]->id;

                // Simulasi jam masuk dan pulang realistis
                // ~10% kemungkinan tidak hadir (tidak ada record absen)
                $seed = crc32($karyawan->nip . $dateStr);
                srand($seed);
                $r = rand(1, 100);

                if ($r <= 5) {
                    // 5% - tidak hadir sama sekali (tidak ada record)
                    continue;
                }

                // Jam masuk: 07:00 - 08:45 (kebanyakan 07:30-08:00)
                $mntMasuk    = rand(0, 105); // 0-105 menit setelah 07:00
                $jamMasuk    = Carbon::create($tahun, $bulan, $day, 7, 0)->addMinutes($mntMasuk)->format('H:i');
                // Jam pulang: 16:00 - 17:30 (reguler 16:30)
                $mntPulang   = rand(0, 90);  // 0-90 menit setelah 16:00
                $jamPulang   = Carbon::create($tahun, $bulan, $day, 16, 0)->addMinutes($mntPulang)->format('H:i');

                $stagingRows[] = [
                    'import_batch_id'   => $batchId,
                    'employee_id_mentah' => $karyawan->nip,
                    'nama_mentah'       => $karyawan->nama,
                    'tanggal'           => $dateStr,
                    'check_in_jadwal'   => '07:30',
                    'check_out_jadwal'  => '16:30',
                    'clock_in_aktual'   => $jamMasuk,
                    'clock_out_aktual'  => $jamPulang,
                    'catatan_mesin'     => null,
                    'karyawan_id'       => $karyawan->id,
                    'status_matching'   => 'matched',
                    'detail_terkirim_id' => null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
                $totalRows++;

                // Insert batch per 500 rows untuk memory efficiency
                if (count($stagingRows) >= 500) {
                    AbsensiStaging::insert($stagingRows);
                    $stagingRows = [];
                }
            }
        }

        // Insert sisa
        if (!empty($stagingRows)) {
            AbsensiStaging::insert($stagingRows);
        }

        // Update total_baris di import log
        foreach ($importLogs as $log) {
            $count = AbsensiStaging::where('import_batch_id', $log->id)->count();
            $log->update([
                'total_baris'   => $count,
                'baris_matched' => $count,
            ]);
        }

        $this->command->info("   ✓ Data absensi staging selesai: $totalRows records.");

        $this->command->newLine();
        $this->command->info('=== SEEDER SELESAI ===');
        $this->command->info("✓ Jadwal kerja Juli 2026: " . $karyawans->count() . " karyawan, 31 hari");
        $this->command->info("✓ Absensi staging 1-21 Juli 2026: $totalRows records (skip Sab/Min)");
        $this->command->newLine();
        $this->command->info('📌 LANGKAH SELANJUTNYA:');
        $this->command->info('   1. Buka: https://office.test/kepegawaian/surat?tab=cuti-bersama');
        $this->command->info('   2. Buat event Cuti Bersama baru untuk tanggal di Juli 2026');
        $this->command->info('   3. Klik Simulasi → Terapkan untuk melihat efeknya');
    }
}
