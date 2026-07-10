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
        // Kita asumsikan Cuti mencakup rentang tgl_mulai s/d tgl_akhir dengan status approved
        $cuti = SuratCuti::where('karyawan_id', $karyawanId)
            ->where('status', 'approved')
            ->where(function ($q) use ($tanggal) {
                $q->whereDate('tgl_mulai', '<=', $tanggal)
                  ->whereDate('tgl_akhir', '>=', $tanggal);
            })
            ->first();

        if ($cuti) {
            // Karena tabel cuti tidak punya kolom pembeda tegas antara cuti/izin (hanya urgensi/jenis),
            // secara default kita anggap CUTI. Bisa disesuaikan nanti dengan relasi jenis_cuti.
            return [
                'status' => StatusKehadiran::CUTI,
                'catatan' => 'Cuti/Izin resmi (' . $cuti->no_surat . ')'
            ];
        }

        // 2. Cek apakah Clock In & Clock Out kosong
        if (empty($clockIn) && empty($clockOut)) {
            return [
                'status' => StatusKehadiran::PERLU_VERIFIKASI,
                'catatan' => 'Tidak ada rekaman jam mesin.'
            ];
        }

        // 3. Cek Shift 
        if (!$shiftId) {
            // Hadir di hari libur (tidak ada shift terikat)
            return [
                'status' => StatusKehadiran::HADIR,
                'catatan' => 'Hadir tanpa jadwal shift.'
            ];
        }

        $jadwalShift = JadwalShift::find($shiftId);
        
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
            $ruanganShift = RuanganShift::where('ruangan_id', $ruanganId)
                                        ->where('shift_id', $shiftId)
                                        ->first();
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
                $diffMenit = $actualIn->diffInMinutes($jamMasukBatas);
                $catatan[] = 'Terlambat ' . $diffMenit . ' menit';
            }

            // Hitung Pulang Cepat
            if ($actualOut && $actualOut->lessThan($jamKeluarBatas)) {
                if ($status === StatusKehadiran::HADIR) {
                    $status = StatusKehadiran::PULANG_CEPAT;
                }
                $diffMenit = $jamKeluarBatas->diffInMinutes($actualOut);
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
