<?php

namespace App\Services;

use App\Enums\KodeAturanJadwal;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalAturan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalShift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AturanJadwalService
{
    public function get(int $bagianId, KodeAturanJadwal $kode): int|bool
    {
        // 1. Cek aturan khusus bagian
        $nilai = JadwalAturan::where('bagian_id', $bagianId)
            ->where('kode', $kode->value)
            ->where('aktif', true)
            ->value('nilai');

        // 2. Fallback ke Aturan Umum RSBA (bagian_id IS NULL)
        if ($nilai === null) {
            $nilai = JadwalAturan::whereNull('bagian_id')
                ->where('kode', $kode->value)
                ->where('aktif', true)
                ->value('nilai');
        }

        // 3. Fallback ke default enum
        if ($nilai === null) {
            $nilai = $kode->defaultNilai();
        }

        return $kode->tipe() === 'bool' ? (bool) $nilai : (int) $nilai;
    }

    /**
     * Validasi kelayakan Jadwal Kerja terhadap aturan aktif (Umum RSBA & Override Departemen).
     * Returns array pesan pelanggaran. Jika kosong, berarti jadwal VALID!
     */
    public function validasiJadwal(JadwalKerja $jadwal): array
    {
        $violations = [];
        // Use the schedule snapshot. The room mapping is only a legacy fallback
        // while existing schedules are being backfilled.
        $bagianId = $jadwal->bagian_id ?? $jadwal->ruangan?->bagian_id ?? 0;

        // Ambil batas aturan
        $maxMalam = (int) $this->get($bagianId, KodeAturanJadwal::MAX_SHIFT_MALAM_BERTURUT);
        $maxKerja = (int) $this->get($bagianId, KodeAturanJadwal::MAX_HARI_KERJA_BERTURUT);
        $minIstirahat = (int) $this->get($bagianId, KodeAturanJadwal::MIN_ISTIRAHAT_JAM);

        // Group details per karyawan
        $detailsByKaryawan = $jadwal->details->groupBy('karyawan_id');

        foreach ($detailsByKaryawan as $karyawanId => $details) {
            $karyawan = $details->first()->karyawan;
            $nama = $karyawan?->full_nama ?? 'Karyawan ID ' . $karyawanId;
            $sortedDetails = $details->sortBy('tanggal')->values();

            $consecutiveNight = 0;
            $consecutiveWork = 0;
            $prevDetail = null;

            foreach ($sortedDetails as $detail) {
                $shift = $detail->shift;

                // 1. Cek Hari Kerja & Shift Malam Berturut-turut
                if (empty($detail->shift_id) || !$shift || str_contains(strtoupper($shift->kode), 'OFF') || str_contains(strtoupper($shift->kode), 'LIBUR')) {
                    $consecutiveNight = 0;
                    $consecutiveWork = 0;
                } else {
                    $consecutiveWork++;
                    if ($maxKerja > 0 && $consecutiveWork > $maxKerja) {
                        $violations[] = "Pegawai {$nama}: Melanggar Maksimal Hari Kerja Berturut-turut ({$consecutiveWork} hari kerja tanpa libur, maksimal diizinkan: {$maxKerja} hari).";
                    }

                    $shiftKode = strtoupper($shift->kode);
                    $isMalam = str_contains($shiftKode, 'M') || str_contains($shiftKode, 'MALAM');
                    if ($isMalam) {
                        $consecutiveNight++;
                        if ($maxMalam > 0 && $consecutiveNight > $maxMalam) {
                            $violations[] = "Pegawai {$nama}: Melanggar Maksimal Shift Malam Berturut-turut ({$consecutiveNight} malam berturut-turut, maksimal diizinkan: {$maxMalam} malam).";
                        }
                    } else {
                        $consecutiveNight = 0;
                    }

                    // 2. Cek Minimum Jeda Istirahat Antar Shift
                    if ($prevDetail && $prevDetail->shift && !empty($prevDetail->shift_id)) {
                        $prevDate = Carbon::parse($prevDetail->tanggal);
                        $currDate = Carbon::parse($detail->tanggal);

                        if ($prevDate->diffInDays($currDate) === 1) {
                            $prevKeluarStr = $prevDetail->shift->jam_keluar ?? '14:00';
                            $currMasukStr = $shift->jam_masuk ?? '07:00';

                            $prevEnd = Carbon::parse($prevDetail->tanggal->format('Y-m-d') . ' ' . $prevKeluarStr);
                            $prevStart = Carbon::parse($prevDetail->tanggal->format('Y-m-d') . ' ' . ($prevDetail->shift->jam_masuk ?? '07:00'));
                            if ($prevEnd->lessThanOrEqualTo($prevStart)) {
                                $prevEnd->addDay();
                            }

                            $currStart = Carbon::parse($detail->tanggal->format('Y-m-d') . ' ' . $currMasukStr);

                            $restHours = $prevEnd->diffInHours($currStart, false);
                            if ($restHours >= 0 && $restHours < $minIstirahat) {
                                $violations[] = "Pegawai {$nama} pada tgl " . $detail->tanggal->format('d/m/Y') . ": Jeda istirahat antar shift hanya {$restHours} jam (minimum diizinkan: {$minIstirahat} jam).";
                            }
                        }
                    }
                }

                $prevDetail = $detail;
            }
        }

        return array_unique($violations);
    }

    /**
     * Alias checkViolations untuk notifikasi dan sistem audit.
     */
    public function checkViolations(JadwalKerja $jadwal): array
    {
        $rawViolations = $this->validasiJadwal($jadwal);
        return array_map(function ($msg) {
            return is_array($msg) ? $msg : ['message' => $msg];
        }, $rawViolations);
    }

    /**
     * Kembalikan daftar shift yang valid untuk ruangan dan Bagian jadwal.
     *
     * Shift tanpa mapping Bagian berlaku umum. Jika sebuah shift memiliki
     * mapping Bagian, shift tersebut hanya valid untuk Bagian yang dipilih.
     * Konfigurasi RuanganShift tetap digunakan untuk override jam/toleransi.
     */
    public function shiftValidUntukRuangan(int $ruanganId, ?int $bagianId = null): Collection
    {
        $eligibleShiftIds = JadwalShift::query()
            ->where('aktif', true)
            ->where(function ($query) use ($bagianId) {
                $query->whereDoesntHave('bagians')
                      ->orWhere('kode', 'OFF')
                      ->orWhere('jam_masuk', '00:00:00');

                if ($bagianId) {
                    $query->orWhereHas('bagians', fn ($bagianQuery) =>
                        $bagianQuery->whereKey($bagianId)
                    );
                }
            })
            ->pluck('id');

        if ($eligibleShiftIds->isEmpty()) {
            return new Collection();
        }

        $ruanganShifts = \App\Models\Sdm\RuanganShift::where('ruangan_id', $ruanganId)
            ->whereIn('shift_id', $eligibleShiftIds)
            ->with('shift')
            ->get()
            ->filter(fn($rs) => $rs->shift && $rs->shift->aktif);

        if ($ruanganShifts->isEmpty()) {
            // Fallback: bungkus shift yang valid untuk Bagian ke dalam objek sementara
            return JadwalShift::whereIn('id', $eligibleShiftIds)->get()->map(function ($shift) use ($ruanganId) {
                $rs = new \App\Models\Sdm\RuanganShift();
                $rs->ruangan_id = $ruanganId;
                $rs->shift_id   = $shift->id;
                $rs->setRelation('shift', $shift);
                return $rs;
            });
        }

        return $ruanganShifts->values();
    }
}
