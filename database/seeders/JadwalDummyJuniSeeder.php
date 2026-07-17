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

class JadwalDummyJuniSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Memulai Seeder Jadwal Kerja...");

        // Pastikan Super Admin (karyawan_id = 1) terhubung ke Ruang Direksi
        $direksiRuanganId = DB::table('ruangan')->where('nama', 'Ruang Direksi')->value('id');
        if ($direksiRuanganId) {
            DB::table('sdm_karyawan')->where('id', 1)->update([
                'ruangan_id' => $direksiRuanganId,
                'kategori_kerja' => 'reguler'
            ]);
        }

        $periodes = [
            ['bulan' => 6, 'tahun' => 2026],
            ['bulan' => 7, 'tahun' => 2026],
        ];

        foreach ($periodes as $p) {
            $bulan = $p['bulan'];
            $tahun = $p['tahun'];
            
            $this->command->info("Memproses Periode: {$bulan}-{$tahun}...");

            // Hapus jadwal lama untuk periode ini jika ada
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $existingJadwals = JadwalKerja::where('bulan', $bulan)->where('tahun', $tahun)->get();
            foreach ($existingJadwals as $ej) {
                JadwalKerjaDetail::where('jadwal_kerja_id', $ej->id)->delete();
                $ej->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $ruangans = Ruangan::all();
            $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;

            $detailsToInsert = [];

            foreach ($ruangans as $ruangan) {
                $karyawans = Karyawan::where('ruangan_id', $ruangan->id)
                    ->whereNull('resign_at')
                    ->get();

                if ($karyawans->isEmpty()) {
                    continue;
                }

                // Buat header Jadwal Kerja
                $jadwalKerja = JadwalKerja::create([
                    'ruangan_id' => $ruangan->id,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'status' => 'published',
                    'dibuat_oleh' => 1,
                ]);

                // Dapatkan shift yang terhubung dengan ruangan ini
                $roomShifts = RuanganShift::where('ruangan_id', $ruangan->id)->pluck('shift_id')->toArray();
                
                // Cek apakah ada shift Reguler
                $regulerShift = DB::table('sdm_jadwal_shift')->where('kode', 'REGULER')->first();
                $regulerShiftId = $regulerShift ? $regulerShift->id : null;

                foreach ($karyawans as $kIndex => $karyawan) {
                    $polaShift = ['P', 'P', 'S', 'S', 'M', 'M', 'L', 'L'];
                    $startIndex = ($kIndex * 2) % count($polaShift);

                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $date = Carbon::create($tahun, $bulan, $d);
                        $shiftId = null;

                        if ($karyawan->kategori_kerja === KategoriKerja::REGULER) {
                            if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                                $officeShift = DB::table('sdm_jadwal_shift')
                                    ->whereIn('id', $roomShifts)
                                    ->where('kode', 'like', 'P08%')
                                    ->first();
                                $shiftId = $officeShift ? $officeShift->id : $regulerShiftId;
                            }
                        } else {
                            $polaIndex = ($startIndex + $d - 1) % count($polaShift);
                            $type = $polaShift[$polaIndex];

                            if ($type !== 'L' && !empty($roomShifts)) {
                                $selectedShiftId = null;
                                foreach ($roomShifts as $rsId) {
                                    $s = DB::table('sdm_jadwal_shift')->find($rsId);
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

                        if ($bulan == 6) {
                            $attendance = [
                                'status_kehadiran' => 'belum_dicek',
                                'absen_masuk_at' => null,
                                'absen_keluar_at' => null,
                                'catatan' => null
                            ];
                        } else {
                            if ($date->gt(Carbon::today())) {
                                $attendance = [
                                    'status_kehadiran' => 'belum_dicek',
                                    'absen_masuk_at' => null,
                                    'absen_keluar_at' => null,
                                    'catatan' => null
                                ];
                            } else {
                                $attendance = $this->generateMockAttendance($date, $shiftId);
                            }
                        }

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
                    }
                }
            }

            // Bulk insert details
            foreach (array_chunk($detailsToInsert, 500) as $chunk) {
                JadwalKerjaDetail::insert($chunk);
            }
        }

        $this->command->info("Selesai! Berhasil membuat Jadwal Kerja & Absensi untuk periode Juni & Juli 2026.");
    }

    private function generateMockAttendance(Carbon $date, $shiftId): array
    {
        $shifts = DB::table('sdm_jadwal_shift')->get()->keyBy('id');
        $shift = $shiftId ? $shifts->get($shiftId) : null;

        if (!$shift) {
            if (rand(1, 100) <= 8) {
                $masuk = Carbon::parse($date->format('Y-m-d') . ' ' . sprintf('%02d:%02d:00', rand(7, 9), rand(0, 59)));
                $durasiJam = rand(4, 8);
                $keluar = $masuk->copy()->addHours($durasiJam)->addMinutes(rand(0, 59));
                return [
                    'status_kehadiran' => 'hadir',
                    'absen_masuk_at' => $masuk->format('Y-m-d H:i:s'),
                    'absen_keluar_at' => $keluar->format('Y-m-d H:i:s'),
                    'catatan' => 'Lembur tugas khusus hari libur.'
                ];
            }
            return [
                'status_kehadiran' => 'belum_dicek',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => null
            ];
        }

        $rand = rand(1, 100);
        if ($rand <= 3) {
            return [
                'status_kehadiran' => 'cuti',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Cuti resmi (' . rand(100, 999) . '/C/' . $date->year . ')'
            ];
        } elseif ($rand <= 5) {
            return [
                'status_kehadiran' => 'tidak_hadir',
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Tidak ada rekaman jam mesin.'
            ];
        }

        $jamMasuk = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->jam_masuk);
        $jamKeluar = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->jam_keluar);
        if ($shift->lintas_hari) {
            $jamKeluar->addDay();
        }

        $inRand = rand(1, 10);
        if ($inRand <= 8) {
            $actualIn = $jamMasuk->copy()->subMinutes(rand(5, 20));
            $isLate = false;
            $lateMinutes = 0;
        } else {
            $lateMinutes = rand(5, 45);
            $actualIn = $jamMasuk->copy()->addMinutes($lateMinutes);
            $isLate = $lateMinutes > ($shift->toleransi_telat_menit ?? 15);
        }

        $outRand = rand(1, 100);
        $isEarly = false;
        $earlyMinutes = 0;
        
        if ($outRand <= 25) {
            $actualOut = $jamKeluar->copy()->addMinutes(rand(60, 180));
        } elseif ($outRand <= 85) {
            $actualOut = $jamKeluar->copy()->addMinutes(rand(1, 20));
        } else {
            $earlyMinutes = rand(5, 30);
            $actualOut = $jamKeluar->copy()->subMinutes($earlyMinutes);
            $isEarly = true;
        }

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
