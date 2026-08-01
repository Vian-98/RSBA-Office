<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Surat\SuratCuti;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\RuanganShift;
use App\Models\Sdm\JadwalShift;
use App\Enums\StatusKehadiran;

class KalkulasiKehadiranService
{
    protected static array $cutiCache = [];
    protected static array $shiftCache = [];
    protected static array $ruanganShiftCache = [];

    /**
     * Hitung status kehadiran berdasarkan data staging dan shift
     *
     * @param int $karyawanId
     * @param string $tanggal (Y-m-d)
     * @param string|null $clockIn (H:i)
     * @param string|null $clockOut (H:i)
     * @param int|null $shiftId
     * @param int|null $ruanganId
     * @return array [ 'status' => StatusKehadiran::class, 'catatan' => string ]
     */
    public function hitungStatus($karyawanId, $tanggal, $clockIn, $clockOut, $shiftId, $ruanganId)
    {
        // 1. Cek Surat Cuti / Izin (Prioritas Tertinggi)
        if (!isset(self::$cutiCache[$karyawanId])) {
            self::$cutiCache[$karyawanId] = SuratCuti::where('karyawan_id', $karyawanId)
                ->where('status', 'approved')
                ->get()
                ->toArray();
        }
        
        $cuti = null;
        foreach (self::$cutiCache[$karyawanId] as $c) {
            $tglMulai = substr($c['tgl_mulai'], 0, 10);
            $tglAkhir = substr($c['tgl_akhir'], 0, 10);
            if ($tanggal >= $tglMulai && $tanggal <= $tglAkhir) {
                $cuti = $c;
                break;
            }
        }

        if ($cuti) {
            $isCutiBersama = isset($cuti['sumber']) && $cuti['sumber'] === 'cuti_bersama';
            return [
                'status' => $isCutiBersama ? StatusKehadiran::CUTI_BERSAMA : StatusKehadiran::CUTI,
                'catatan' => $isCutiBersama ? 'Cuti Bersama' : ('Cuti/Izin resmi (' . ($cuti['no_surat'] ?? '') . ')')
            ];
        }

        // 1.5. Cek Cuti Bersama tanpa potong kuota (Event diterapkan)
        $isCutiBersamaTanggal = \App\Models\Sdm\CutiBersamaTanggal::where('tanggal', $tanggal)
            ->whereHas('cutiBersama', fn($q) => $q->where('status', 'diterapkan'))
            ->exists();

        if ($isCutiBersamaTanggal && empty($clockIn) && empty($clockOut)) {
            return [
                'status' => StatusKehadiran::CUTI_BERSAMA,
                'catatan' => 'Hari Cuti Bersama'
            ];
        }

        // 2. Cek apakah tidak ada shift scheduled (LIBUR)
        if (!$shiftId) {
            // Jika tidak ada shift dan tidak ada absen: ini hari libur biasa (TETAP LIBUR/TIDAK PERLU ABSEN)
            if (empty($clockIn) && empty($clockOut)) {
                return [
                    'status' => StatusKehadiran::BELUM_DICEK,
                    'catatan' => null
                ];
            }
            
            // Jika tidak ada shift tapi ternyata ada absen: hadir di hari libur
            return [
                'status' => StatusKehadiran::HADIR,
                'catatan' => 'Hadir tanpa jadwal shift (Lembur/Tugas Tambahan).'
            ];
        }

        // 3. Cek apakah Clock In & Clock Out kosong (dan ada shift terjadwal)
        if (empty($clockIn) && empty($clockOut)) {
            return [
                'status' => StatusKehadiran::TIDAK_HADIR,
                'catatan' => 'Tidak ada rekaman jam mesin.'
            ];
        }

        $jadwalShift = null;
        if (!isset(self::$shiftCache[$shiftId])) {
            self::$shiftCache[$shiftId] = JadwalShift::find($shiftId);
        }
        $jadwalShift = self::$shiftCache[$shiftId];
        
        if (!$jadwalShift) {
             return [
                'status' => StatusKehadiran::HADIR,
                'catatan' => 'Jadwal shift tidak valid.'
            ];
        }

        $jamMasukEfektifStr = $jadwalShift->jam_masuk;
        $jamKeluarEfektifStr = $jadwalShift->jam_keluar;
        $toleransi = $jadwalShift->toleransi_telat_menit ?? 0;

        if ($ruanganId) {
            $key = "{$ruanganId}_{$shiftId}";
            if (!isset(self::$ruanganShiftCache[$key])) {
                self::$ruanganShiftCache[$key] = RuanganShift::where('ruangan_id', $ruanganId)
                                            ->where('shift_id', $shiftId)
                                            ->first();
            }
            $ruanganShift = self::$ruanganShiftCache[$key];
            
            if ($ruanganShift) {
                $jamMasukEfektifStr = $ruanganShift->jam_masuk_efektif ?? $jamMasukEfektifStr;
                $jamKeluarEfektifStr = $ruanganShift->jam_keluar_efektif ?? $jamKeluarEfektifStr;
                $toleransi = $ruanganShift->toleransi_telat_menit_override ?? $toleransi;
            }
        }

        $tanggalMasuk = Carbon::parse($tanggal);
        $tanggalKeluar = clone $tanggalMasuk;

        // Lintas hari logic
        if ($jadwalShift->lintas_hari) {
            $tanggalKeluar->addDay();
        }

        $jamMasukBatas = Carbon::parse($tanggalMasuk->format('Y-m-d') . ' ' . $jamMasukEfektifStr)->addMinutes($toleransi);
        $jamKeluarBatas = Carbon::parse($tanggalKeluar->format('Y-m-d') . ' ' . $jamKeluarEfektifStr);

        // Actual datetime scan
        $actualIn = null;
        $invalidIn = false;
        if (!empty($clockIn)) {
            try {
                $actualIn = Carbon::parse($tanggalMasuk->format('Y-m-d') . ' ' . trim($clockIn));
            } catch (\Exception $e) {
                $invalidIn = true;
                $catatan[] = "Jam Masuk tidak valid: {$clockIn}";
            }
        }
        
        $actualOut = null;
        $invalidOut = false;
        if (!empty($clockOut)) {
            try {
                $actualOutDate = $tanggalMasuk->format('Y-m-d');
                if ($jadwalShift->lintas_hari) {
                    preg_match('/^(\d{1,2})/', trim($clockOut), $matches);
                    if (!empty($matches)) {
                        $jamNum = (int) $matches[1];
                        if ($jamNum < 15) { // asumsi shift malam pulang pagi/siang
                            $actualOutDate = $tanggalKeluar->format('Y-m-d');
                        }
                    }
                }
                $actualOut = Carbon::parse($actualOutDate . ' ' . trim($clockOut));
            } catch (\Exception $e) {
                $invalidOut = true;
                $catatan[] = "Jam Keluar tidak valid: {$clockOut}";
            }
        }

        $status = StatusKehadiran::HADIR;

        if ($invalidIn || $invalidOut) {
            $status = StatusKehadiran::PERLU_VERIFIKASI;
        } else {
            // Hitung Terlambat
            if ($actualIn && $actualIn->greaterThan($jamMasukBatas)) {
                $status = StatusKehadiran::TERLAMBAT;
                $diffMenit = abs($actualIn->diffInMinutes($jamMasukBatas));
                $catatan[] = 'Terlambat ' . $diffMenit . ' menit';
            }

            // Hitung Pulang Cepat
            if ($actualOut && $actualOut->lessThan($jamKeluarBatas)) {
                if ($status === StatusKehadiran::HADIR) {
                    $status = StatusKehadiran::PULANG_CEPAT;
                }
                $diffMenit = abs($jamKeluarBatas->diffInMinutes($actualOut));
                $catatan[] = 'Pulang cepat ' . $diffMenit . ' menit';
            }
        }

        // Kalau ada yang lupa tap in / tap out (kosong salah satu) tapi bukan karena invalid
        if (empty($clockIn) && !$invalidIn) {
            $catatan[] = 'Lupa Check In';
        }
        if (empty($clockOut) && !$invalidOut) {
            $catatan[] = 'Lupa Check Out';
        }

        return [
            'status' => $status,
            'catatan' => !empty($catatan) ? implode('. ', $catatan) . '.' : null,
        ];
    }
}
