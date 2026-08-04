<?php

namespace App\Services;

use App\Models\Sdm\AbsensiRawPunch;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbsensiClearingService
{
    /**
     * Jalankan pipeline clearing lengkap untuk import_log_id tertentu.
     */
    public function clear(int $importLogId): object
    {
        // 1. Deduplikasi (10-minute window, Anchor Strategy)
        $duplicateCount = $this->deduplicatePunches($importLogId);

        // 2. Cross-Midnight Assignment (Shift Lintas Hari & Night Shift Pattern Detection)
        $this->assignCrossMidnightDates($importLogId);

        // 3. Pairing First-In / Last-Out & Sanity Check
        $pairedResults = $this->pairAndCheckSanity($importLogId);

        return (object) [
            'duplicateCount' => $duplicateCount,
            'paired'         => $pairedResults['paired'],
            'anomalyCount'   => $pairedResults['anomalyCount'],
            'anomalies'      => $pairedResults['anomalies'],
        ];
    }

    /**
     * Langkah 1: Deduplikasi Tap (10 Menit Window dengan Anchor Strategy)
     */
    public function deduplicatePunches(int $importLogId): int
    {
        $rawPunches = AbsensiRawPunch::where('import_log_id', $importLogId)
            ->where('is_discarded', false)
            ->orderBy('employee_id')
            ->orderBy('punch_datetime', 'asc')
            ->get();

        $grouped = $rawPunches->groupBy('employee_id');
        $duplicateCount = 0;

        foreach ($grouped as $employeeId => $punches) {
            $anchor = null;

            foreach ($punches as $punch) {
                if (!$punch->punch_datetime) {
                    continue;
                }

                if ($anchor === null) {
                    $anchor = $punch;
                    continue;
                }

                // Kalkulasi selisih dalam menit menggunakan timestamp detik penuh
                $diffInMinutes = abs($punch->punch_datetime->timestamp - $anchor->punch_datetime->timestamp) / 60;

                if ($diffInMinutes <= 10) {
                    $punch->update([
                        'is_discarded'           => true,
                        'discard_reason'         => "Duplicate tap (<= 10 menit dari tap anchor {$anchor->jam})",
                        'duplicate_reference_id' => $anchor->id,
                    ]);
                    $duplicateCount++;
                } else {
                    $anchor = $punch;
                }
            }
        }

        return $duplicateCount;
    }

    /**
     * Langkah 2: Cross-Midnight Assignment (Shift Lintas Hari H-1 & Night Shift Pattern Detection)
     *
     * Tap subuh pada hari T (<= 09:30) di-assign ke H-1 jika:
     * 1. Jadwal DB H-1 secara resmi diset lintas_hari = true, ATAU
     * 2. Pola tap mentah H-1 murni shift malam: memiliki tap malam (>= 19:00) DAN TIDAK memiliki tap pagi (06:00-12:00).
     */
    public function assignCrossMidnightDates(int $importLogId): void
    {
        $rawPunches = AbsensiRawPunch::where('import_log_id', $importLogId)
            ->where('is_discarded', false)
            ->orderBy('punch_datetime', 'asc')
            ->get();

        $employeeIds = $rawPunches->pluck('employee_id')->unique();
        $karyawanMap = Karyawan::whereIn('pin_absen', $employeeIds)
            ->pluck('id', 'pin_absen');

        foreach ($rawPunches as $tap) {
            $karyawanId = $karyawanMap[$tap->employee_id] ?? null;
            $yesterday = $tap->tanggal->copy()->subDay()->toDateString();

            $jamStr = is_object($tap->jam) ? $tap->jam->format('H:i:s') : (string) $tap->jam;
            $isMorningTapToday = substr($jamStr, 0, 5) <= '10:00';

            if ($isMorningTapToday) {
                // 1. Cek jadwal resmi DB H-1 (jika diset lintas_hari = true)
                $isLintasHariSchedule = false;
                if ($karyawanId) {
                    $yesterdayShift = DB::table('sdm_jadwal_kerja_detail as jkd')
                        ->join('sdm_jadwal_shift as js', 'jkd.shift_id', '=', 'js.id')
                        ->where('jkd.karyawan_id', $karyawanId)
                        ->whereDate('jkd.tanggal', $yesterday)
                        ->select('js.jam_masuk', 'js.jam_keluar', 'js.lintas_hari')
                        ->first();

                    if ($yesterdayShift && $yesterdayShift->lintas_hari) {
                        $isLintasHariSchedule = true;
                    }
                }

                // 2. Cek pola tap mentah H-1 (hanya tap mentah yang terikat pada tanggal H-1):
                $yesterdayPunches = AbsensiRawPunch::where('import_log_id', $importLogId)
                    ->where('employee_id', $tap->employee_id)
                    ->where(function ($q) use ($yesterday) {
                        $q->whereDate('assigned_date', $yesterday)
                          ->orWhere(function ($sub) use ($yesterday) {
                              $sub->whereNull('assigned_date')->whereDate('tanggal', $yesterday);
                          });
                    })
                    ->where('is_discarded', false)
                    ->get();

                $hasEveningTapYesterday = $yesterdayPunches->contains(function ($p) {
                    $jam = is_object($p->jam) ? $p->jam->format('H:i:s') : (string) $p->jam;
                    return substr($jam, 0, 5) >= '17:00';
                });


                $hasEarlierTapYesterday = $yesterdayPunches->contains(function ($p) {
                    $jam = is_object($p->jam) ? $p->jam->format('H:i:s') : (string) $p->jam;
                    return substr($jam, 0, 5) >= '06:00' && substr($jam, 0, 5) < '17:00';
                });

                // Jika jadwal DB resmi lintas_hari ATAU (pola H-1 murni shift malam: ada tap malam & tanpa tap lebih awal 06:00-17:00 H-1)
                if ($isLintasHariSchedule || ($hasEveningTapYesterday && !$hasEarlierTapYesterday)) {

                    $tap->update(['assigned_date' => $yesterday]);
                    continue;
                }
            }

            $tap->update(['assigned_date' => $tap->tanggal]);
        }
    }


    /**
     * Langkah 3 & 4: Pairing (First-In / Last-Out by DATETIME) & Sanity Check
     */
    public function pairAndCheckSanity(int $importLogId): array
    {
        $rawPunches = AbsensiRawPunch::where('import_log_id', $importLogId)
            ->where('is_discarded', false)
            ->orderBy('punch_datetime', 'asc')
            ->get();

        $grouped = $rawPunches->groupBy(function ($item) {
            $assignedDate = $item->assigned_date ? $item->assigned_date->format('Y-m-d') : $item->tanggal->format('Y-m-d');
            return $item->employee_id . '|' . $assignedDate;
        });

        $paired = [];
        $anomalies = [];
        $anomalyCount = 0;

        foreach ($grouped as $key => $punches) {
            if ($punches->isEmpty()) {
                continue;
            }

            [$employeeId, $assignedDate] = explode('|', $key);
            $punches = $punches->sortBy('punch_datetime')->values();
            $count = $punches->count();

            $firstTap = $punches->first();
            $lastTap  = $punches->last();

            $namaMentah = $firstTap->nama_mentah;
            $clockIn    = $firstTap->punch_datetime ? $firstTap->punch_datetime->format('Y-m-d H:i:s') : null;
            $clockOut   = null;
            $flags      = [];

            if ($count === 1) {
                $clockOut = null;
                $flags[]  = 'SINGLE_PUNCH';
            } else {
                $clockOut = $lastTap->punch_datetime ? $lastTap->punch_datetime->format('Y-m-d H:i:s') : null;

                if ($count === 3 || $count === 4) {
                    $flags[] = "EXTRA_PUNCH ({$count} rekaman)";
                    for ($i = 1; $i < $count - 1; $i++) {
                        $punches[$i]->update([
                            'is_discarded'   => true,
                            'discard_reason' => "Rekaman tengah dibuang ({$count} tap ekstra)",
                        ]);
                    }
                }

                // Hitung selisih durasi dalam menit dari DATETIME jam masuk & jam keluar
                if ($firstTap->punch_datetime && $lastTap->punch_datetime) {
                    $durationMinutes = abs($lastTap->punch_datetime->timestamp - $firstTap->punch_datetime->timestamp) / 60;

                    if ($durationMinutes < 15) {
                        $flags[] = 'DURASI_SANGAT_PENDEK (< 15 menit)';
                    } elseif ($durationMinutes > 960) { // 16 jam * 60 mnt
                        $flags[] = 'DURASI_SANGAT_PANJANG (> 16 jam)';
                    }
                }
            }

            $catatanMesin = !empty($flags) ? implode('; ', $flags) : null;

            $record = [
                'import_batch_id'    => $importLogId,
                'employee_id_mentah' => $employeeId,
                'nama_mentah'        => $namaMentah,
                'tanggal'            => $assignedDate,
                'clock_in_aktual'    => $clockIn,
                'clock_out_aktual'   => $clockOut,
                'catatan_mesin'      => $catatanMesin,
                'raw_tap_count'      => $count,
            ];

            $paired[] = $record;

            if ($catatanMesin !== null) {
                $anomalies[] = array_merge($record, [
                    'employee_id' => $employeeId,
                    'anomali'     => $catatanMesin,
                ]);
                $anomalyCount++;
            }
        }

        return [
            'paired'       => $paired,
            'anomalies'    => $anomalies,
            'anomalyCount' => $anomalyCount,
        ];
    }
}
