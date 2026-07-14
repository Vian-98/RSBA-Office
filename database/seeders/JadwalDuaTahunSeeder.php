<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Ruangan;
use App\Models\Sdm\RuanganShift;
use App\Enums\KategoriKerja;
use Carbon\Carbon;

class JadwalDuaTahunSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Memulai Seeder Jadwal Kerja Periode 2023 - 2024...");

        // Setup ruangan dan kategori kerja untuk akun-akun pengetesan utama agar memiliki jadwal reguler
        $manajemenRuanganId = DB::table('ruangan')->where('nama', 'Kantor Manajemen (SDM & Keuangan)')->value('id');
        $direksiRuanganId = DB::table('ruangan')->where('nama', 'Ruang Direksi')->value('id');

        if ($manajemenRuanganId) {
            $emailsToManajemen = ['administrasi@rsba.com', 'keuangan@rsba.com', 'sdm@rsba.com', 'umum@rsba.com', 'guest@rsba.com'];
            foreach ($emailsToManajemen as $email) {
                $user = \App\Models\User::where('email', $email)->first();
                if ($user && $user->karyawan) {
                    $user->karyawan->update([
                        'ruangan_id' => $manajemenRuanganId,
                        'kategori_kerja' => \App\Enums\KategoriKerja::REGULER
                    ]);
                }
            }
        }

        if ($direksiRuanganId) {
            $user = \App\Models\User::where('email', 'admin@rsba.com')->first();
            if ($user && $user->karyawan) {
                $user->karyawan->update([
                    'ruangan_id' => $direksiRuanganId,
                    'kategori_kerja' => \App\Enums\KategoriKerja::REGULER
                ]);
            }
        }

        // Nonaktifkan foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Bersihkan data lama untuk rentang 2023 - 2024 agar idempotent
        $this->command->info("Membersihkan data lama tahun 2023-2024 jika ada...");
        $oldJadwals = JadwalKerja::whereIn('tahun', [2023, 2024])->get();
        foreach ($oldJadwals as $oj) {
            JadwalKerjaDetail::where('jadwal_kerja_id', $oj->id)->delete();
            $oj->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $ruangans = Ruangan::all();
        
        // Cache master shift
        $shifts = DB::table('sdm_jadwal_shift')->get()->keyBy('id');
        $regulerShift = DB::table('sdm_jadwal_shift')->where('kode', 'REGULER')->first();
        $regulerShiftId = $regulerShift ? $regulerShift->id : null;

        $years = [2023, 2024];
        $months = range(1, 12);

        $detailsToInsert = [];
        $totalHeaderCreated = 0;

        foreach ($years as $tahun) {
            foreach ($months as $bulan) {
                $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
                $this->command->info("Memproses: {$bulan}-{$tahun} ({$daysInMonth} hari)...");

                foreach ($ruangans as $ruangan) {
                    // Ambil karyawan aktif di ruangan tersebut
                    $karyawans = Karyawan::where('ruangan_id', $ruangan->id)
                        ->whereNull('resign_at')
                        ->get();

                    if ($karyawans->isEmpty()) {
                        continue;
                    }

                    // Pemetaan Khusus Tahun 2023
                    if ($tahun === 2023) {
                        if ($bulan % 2 === 1) {
                            // Bulan Ganjil: Hanya pekerja Shift
                            $karyawans = $karyawans->filter(fn($k) => $k->kategori_kerja === KategoriKerja::SHIFT);
                        } else {
                            // Bulan Genap: Hanya pekerja Reguler (Jam Kantor)
                            $karyawans = $karyawans->filter(fn($k) => $k->kategori_kerja === KategoriKerja::REGULER);
                        }
                    }

                    if ($karyawans->isEmpty()) {
                        continue;
                    }

                    // Buat header Jadwal Kerja
                    $jadwalKerja = JadwalKerja::create([
                        'ruangan_id' => $ruangan->id,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'status' => 'published', // Publikasikan langsung agar muncul di UI rekap
                        'dibuat_oleh' => 1,
                    ]);
                    $totalHeaderCreated++;

                    // Dapatkan shift yang terdaftar khusus ruangan ini
                    $roomShifts = RuanganShift::where('ruangan_id', $ruangan->id)->pluck('shift_id')->toArray();

                    foreach ($karyawans as $kIndex => $karyawan) {
                        // Pola rotasi shift karyawan
                        $polaShift = ['P', 'P', 'S', 'S', 'M', 'M', 'L', 'L'];
                        $startIndex = ($kIndex * 2) % count($polaShift);

                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $date = Carbon::create($tahun, $bulan, $d);
                            $shiftId = null;

                            if ($karyawan->kategori_kerja === KategoriKerja::REGULER) {
                                // Reguler: Senin s.d Jumat masuk REGULER, Sabtu-Minggu LIBUR
                                if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                                    // Cari shift reguler ruangan jika ada, jika tidak, gunakan default reguler global
                                    $officeShift = DB::table('sdm_jadwal_shift')
                                        ->whereIn('id', $roomShifts)
                                        ->where('kode', 'like', 'P08%')
                                        ->first();
                                    $shiftId = $officeShift ? $officeShift->id : $regulerShiftId;
                                }
                            } else {
                                // Shift Workers: Gunakan pola rotasi
                                $polaIndex = ($startIndex + $d - 1) % count($polaShift);
                                $type = $polaShift[$polaIndex];

                                if ($type !== 'L' && !empty($roomShifts)) {
                                    $selectedShiftId = null;
                                    foreach ($roomShifts as $rsId) {
                                        $s = $shifts->get($rsId);
                                        if ($s) {
                                            if ($type === 'P' && str_contains(strtolower($s->nama), 'pagi')) {
                                                $selectedShiftId = $s->id;
                                                break;
                                            } elseif ($type === 'S' && str_contains(strtolower($s->nama), 'siang')) {
                                                $selectedShiftId = $s->id;
                                                break;
                                            } elseif ($type === 'M' && str_contains(strtolower($s->nama), 'malam')) {
                                                $selectedShiftId = $s->id;
                                                break;
                                            }
                                        }
                                    }
                                    $shiftId = $selectedShiftId ?? $roomShifts[0];
                                }
                            }

                            // Dapatkan objek shift terpilih
                            $selectedShiftObj = $shiftId ? $shifts->get($shiftId) : null;

                            // Generate data absensi riil fiktif
                            $attendance = $this->generateMockAttendance($date, $selectedShiftObj);

                            $detailsToInsert[] = [
                                'jadwal_kerja_id' => $jadwalKerja->id,
                                'karyawan_id' => $karyawan->id,
                                'shift_id' => $shiftId,
                                'tanggal' => $date->format('Y-m-d'),
                                'status_kehadiran' => $attendance['status_kehadiran'],
                                'absen_masuk_at' => $attendance['absen_masuk_at'],
                                'absen_keluar_at' => $attendance['absen_keluar_at'],
                                'catatan' => $attendance['catatan'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            // Batch insert to avoid memory limit exhaustion
                            if (count($detailsToInsert) >= 2000) {
                                JadwalKerjaDetail::insert($detailsToInsert);
                                $detailsToInsert = [];
                            }
                        }
                    }
                }
            }
        }

        // Insert remaining details
        if (!empty($detailsToInsert)) {
            JadwalKerjaDetail::insert($detailsToInsert);
        }

        $this->command->info("Selesai! Berhasil membuat {$totalHeaderCreated} header Jadwal Kerja selama periode 2023 - 2024.");
    }

    /**
     * Membangun jam masuk & pulang fiktif secara realistik berdasarkan shift
     */
    private function generateMockAttendance(Carbon $date, $shift): array
    {
        if (!$shift) {
            return [
                'status_kehadiran' => 'belum_dicek',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => null
            ];
        }

        $rand = rand(1, 100);
        if ($rand <= 3) {
            // Kasus Cuti
            return [
                'status_kehadiran' => 'cuti',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Cuti/Izin resmi (' . rand(100, 999) . '/C/' . $date->year . ')'
            ];
        } elseif ($rand <= 5) {
            // Kasus Tidak Hadir / Alpa
            return [
                'status_kehadiran' => 'tidak_hadir',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Tidak ada rekaman jam mesin.'
            ];
        }

        // Kasus Hadir / Terlambat / Pulang Cepat
        $jamMasuk = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->jam_masuk);
        $jamKeluar = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->jam_keluar);
        if ($shift->lintas_hari) {
            $jamKeluar->addDay();
        }

        // Jam Tap Masuk
        $inRand = rand(1, 10);
        if ($inRand <= 8) {
            // Tepat waktu (5 s.d 20 menit sebelum jam shift)
            $actualIn = $jamMasuk->copy()->subMinutes(rand(5, 20));
            $isLate = false;
            $lateMinutes = 0;
        } else {
            // Terlambat (5 s.d 45 menit telat)
            $lateMinutes = rand(5, 45);
            $actualIn = $jamMasuk->copy()->addMinutes($lateMinutes);
            $isLate = $lateMinutes > ($shift->toleransi_telat_menit ?? 15);
        }

        // Jam Tap Pulang
        $outRand = rand(1, 10);
        if ($outRand <= 8) {
            // Pulang tepat waktu / lembur dikit (1 s.d 30 menit setelah jam shift)
            $actualOut = $jamKeluar->copy()->addMinutes(rand(1, 30));
            $isEarly = false;
            $earlyMinutes = 0;
        } else {
            // Pulang Cepat (5 s.d 30 menit lebih awal)
            $earlyMinutes = rand(5, 30);
            $actualOut = $jamKeluar->copy()->subMinutes($earlyMinutes);
            $isEarly = true;
        }

        // Tentukan Status Final Kehadiran
        $status = 'hadir';
        $catatanParts = [];

        if ($isLate) {
            $status = 'terlambat';
            $catatanParts[] = "Terlambat {$lateMinutes} menit";
        }
        if ($isEarly) {
            if ($status === 'hadir') {
                $status = 'pulang_cepat';
            }
            $catatanParts[] = "Pulang cepat {$earlyMinutes} menit";
        }

        return [
            'status_kehadiran' => $status,
            'absen_masuk_at' => $actualIn->format('Y-m-d H:i:s'),
            'absen_keluar_at' => $actualOut->format('Y-m-d H:i:s'),
            'catatan' => !empty($catatanParts) ? implode('. ', $catatanParts) . '.' : null
        ];
    }
}
