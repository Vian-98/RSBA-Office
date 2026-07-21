<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;

/**
 * JadwalKerjaJanuari2025Seeder
 *
 * Membuat data jadwal kerja untuk periode Januari 2025 untuk semua karyawan.
 * Berguna sebagai data pengujian fitur Cuti Bersama, Rekap Absensi, dll.
 *
 * REGULER : Senin-Jumat masuk (shift REGULER), Sabtu-Minggu libur
 * SHIFT   : Pola 2 hari Pagi - 2 hari Siang - 2 hari Malam - 2 hari Libur (rolling)
 *
 * Cuti Bersama bisa di-input manual via UI setelah seeder ini dijalankan.
 */
class JadwalKerjaJanuari2025Seeder extends Seeder
{
    const BULAN = 1;
    const TAHUN = 2025;

    public function run(): void
    {
        $this->command->info('Memulai seeding jadwal kerja Januari 2025...');

        $bulan = self::BULAN;
        $tahun = self::TAHUN;
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth; // 31

        $shiftReguler = JadwalShift::where('kode', 'REGULER')->first();
        if (!$shiftReguler) {
            $this->command->error('Shift REGULER tidak ditemukan! Pastikan JadwalSeeder sudah dijalankan.');
            return;
        }

        $karyawans = Karyawan::with('ruangan')->get();
        $this->command->info("Ditemukan {$karyawans->count()} karyawan.");

        // Dapatkan ID karyawan pertama untuk mengisi field dibuat_oleh
        $firstKaryawanId = Karyawan::min('id');

        $jadwalCache = []; // cache per ruangan_id

        foreach ($karyawans as $karyawan) {
            // Tentukan ruangan_id: pakai ruangan karyawan atau ruangan default (null)
            $ruanganId = $karyawan->ruangan_id;

            // Cari atau buat jadwal kerja per ruangan
            $cacheKey = $ruanganId ?? 'null';
            if (!isset($jadwalCache[$cacheKey])) {
                $jadwalCache[$cacheKey] = JadwalKerja::firstOrCreate(
                    ['ruangan_id' => $ruanganId, 'bulan' => $bulan, 'tahun' => $tahun],
                    [
                        'status'      => \App\Enums\StatusJadwalKerja::PUBLISHED,
                        'dibuat_oleh' => $firstKaryawanId,
                        'created_by'  => $firstKaryawanId,
                        'updated_by'  => $firstKaryawanId,
                    ]
                );
            }
            $jadwalKerja = $jadwalCache[$cacheKey];

            // Hapus detail lama karyawan ini agar bersih
            JadwalKerjaDetail::where('jadwal_kerja_id', $jadwalKerja->id)
                ->where('karyawan_id', $karyawan->id)
                ->delete();

            $details = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($tahun, $bulan, $day);
                $dateStr = $date->format('Y-m-d');
                $dayOfWeek = $date->dayOfWeekIso; // 1=Mon ... 7=Sun

                if ($karyawan->kategori_kerja?->value === 'reguler') {
                    // REGULER: Senin-Jumat shift REGULER, Sabtu-Minggu libur
                    if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                        $shiftId        = $shiftReguler->id;
                        $statusKehadiran = 'belum_dicek';
                    } else {
                        // Sabtu/Minggu: shift null, belum_dicek (tidak ada libur di ENUM)
                        $shiftId        = null;
                        $statusKehadiran = 'belum_dicek';
                    }
                } else {
                    // SHIFT: pola rolling per karyawan berdasarkan ID
                    // Pola: P, P, S, S, M, M, L, L (8-hari rolling)
                    // Offset awal berbeda per karyawan untuk variasi
                    [$shiftId, $statusKehadiran] = $this->getShiftPola(
                        $karyawan,
                        $day,
                        $ruanganId
                    );
                }

                $details[] = [
                    'jadwal_kerja_id'  => $jadwalKerja->id,
                    'karyawan_id'      => $karyawan->id,
                    'tanggal'          => $dateStr,
                    'shift_id'         => $shiftId,
                    'status_kehadiran' => $statusKehadiran,
                    'catatan'          => null,
                    'created_by'       => $firstKaryawanId,
                    'updated_by'       => $firstKaryawanId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            JadwalKerjaDetail::insert($details);
        }

        $this->command->info('Seeding jadwal kerja Januari 2025 selesai!');
        $this->command->info('Silakan input Cuti Bersama via UI di: https://office.test/kepegawaian/surat?tab=cuti-bersama');
    }

    /**
     * Tentukan shift dan status kehadiran untuk karyawan SHIFT
     * berdasarkan pola rolling 8-hari: P, P, S, S, M, M, L, L
     *
     * @return array [shift_id|null, status_kehadiran]
     */
    private function getShiftPola(Karyawan $karyawan, int $day, ?int $ruanganId): array
    {
        // Dapatkan shift-shift yang tersedia untuk ruangan ini
        $shifts = JadwalShift::when($ruanganId, function ($q) use ($ruanganId) {
                $q->where('kode', 'like', '%_R' . $ruanganId);
            })
            ->orderBy('id')
            ->get();

        $pagi   = $shifts->first(fn($s) => str_contains(strtolower($s->nama), 'pagi'));
        $siang  = $shifts->first(fn($s) => str_contains(strtolower($s->nama), 'siang'));
        $malam  = $shifts->first(fn($s) => str_contains(strtolower($s->nama), 'malam'));

        // Fallback jika tidak ada shift khusus
        if (!$pagi) {
            $pagi = JadwalShift::where('kode', 'REGULER')->first();
        }

        // Offset awal per karyawan (variasi agar beda jadwal)
        $offset = ($karyawan->id % 8);
        $cycle  = (($day - 1 + $offset) % 8);

        // Pola 8-hari: 0,1 = Pagi; 2,3 = Siang; 4,5 = Malam; 6,7 = Libur
        if ($cycle < 2) {
            return [$pagi?->id, 'belum_dicek'];
        } elseif ($cycle < 4) {
            return [$siang?->id ?? $pagi?->id, 'belum_dicek'];
        } elseif ($cycle < 6) {
            return [$malam?->id ?? $pagi?->id, 'belum_dicek'];
        } else {
            // Hari libur roster shift - shift null, status belum_dicek
            return [null, 'belum_dicek'];
        }
    }
}
