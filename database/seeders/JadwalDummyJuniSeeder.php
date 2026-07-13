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
        $bulan = 6;
        $tahun = 2026;

        $this->command->info("Memulai Seeder Jadwal Kerja Juni 2026...");

        // Hapus jadwal lama untuk Juni 2026 jika ada
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
                'status' => 'published', // Publish agar bisa dilihat di dashboard
                'dibuat_oleh' => 1,
            ]);

            // Dapatkan shift yang terhubung dengan ruangan ini
            $roomShifts = RuanganShift::where('ruangan_id', $ruangan->id)->pluck('shift_id')->toArray();
            
            // Cek apakah ada shift Reguler
            $regulerShift = DB::table('sdm_jadwal_shift')->where('kode', 'REGULER')->first();
            $regulerShiftId = $regulerShift ? $regulerShift->id : null;

            foreach ($karyawans as $kIndex => $karyawan) {
                // Untuk rotasi shift (jika karyawan shift)
                // Kita gunakan pola sederhana: Pagi (P), Pagi (P), Siang (S), Siang (S), Malam (M), Malam (M), Libur (L), Libur (L)
                $polaShift = ['P', 'P', 'S', 'S', 'M', 'M', 'L', 'L'];
                $startIndex = ($kIndex * 2) % count($polaShift); // Buat agar antar karyawan beda awal shiftnya

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = Carbon::create($tahun, $bulan, $d);
                    $shiftId = null;

                    if ($karyawan->kategori_kerja === KategoriKerja::REGULER) {
                        // Pegawai reguler (kantor): Senin - Jumat kerja REGULER, Sabtu - Minggu LIBUR
                        if ($date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5) {
                            // Cari shift khusus kantor atau default REGULER
                            $officeShift = DB::table('sdm_jadwal_shift')
                                ->whereIn('id', $roomShifts)
                                ->where('kode', 'like', 'P08%')
                                ->first();
                            $shiftId = $officeShift ? $officeShift->id : $regulerShiftId;
                        }
                    } else {
                        // Karyawan Shift
                        $polaIndex = ($startIndex + $d - 1) % count($polaShift);
                        $type = $polaShift[$polaIndex];

                        if ($type !== 'L' && !empty($roomShifts)) {
                            // Pilih shift berdasarkan tipe
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

                            // Fallback jika tidak menemukan tipe spesifik, ambil shift pertama ruangan
                            $shiftId = $selectedShiftId ?? $roomShifts[0];
                        }
                    }

                    $detailsToInsert[] = [
                        'jadwal_kerja_id' => $jadwalKerja->id,
                        'karyawan_id' => $karyawan->id,
                        'shift_id' => $shiftId,
                        'tanggal' => $date->format('Y-m-d'),
                        'status_kehadiran' => 'belum_dicek',
                        'absen_masuk_at' => null,
                        'absen_keluar_at' => null,
                        'catatan' => null,
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

        $this->command->info("Selesai! Berhasil membuat Jadwal Kerja Juni 2026 untuk " . count($ruangans) . " ruangan.");
    }
}
