<?php

namespace App\Services;

use App\Enums\KodeAturanJadwal;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalAturan;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\JadwalKerja;
use Illuminate\Database\Eloquent\Collection;

class AturanJadwalService
{
    public function get(int $bagianId, KodeAturanJadwal $kode): int|bool
    {
        $nilai = JadwalAturan::where('bagian_id', $bagianId)
            ->where('kode', $kode->value)
            ->where('aktif', true)
            ->value('nilai') ?? $kode->defaultNilai();

        return $kode->tipe() === 'bool' ? (bool) $nilai : (int) $nilai;
    }

    /**
     * Kembalikan daftar RuanganShift (pivot) aktif untuk ruangan ini.
     * Fallback: jika ruangan belum dikonfigurasi, kembalikan semua shift aktif (tanpa override).
     */
    public function shiftValidUntukRuangan(int $ruanganId): Collection
    {
        $ruanganShifts = \App\Models\Sdm\RuanganShift::where('ruangan_id', $ruanganId)
            ->with('shift')
            ->get()
            ->filter(fn($rs) => $rs->shift && $rs->shift->aktif);

        if ($ruanganShifts->isEmpty()) {
            // Fallback: bungkus shift global ke dalam objek sementara
            return JadwalShift::where('aktif', true)->get()->map(function ($shift) use ($ruanganId) {
                $rs = new \App\Models\Sdm\RuanganShift();
                $rs->ruangan_id = $ruanganId;
                $rs->shift_id   = $shift->id;
                $rs->setRelation('shift', $shift);
                return $rs;
            });
        }

        return $ruanganShifts->values();
    }

    /**
     * Cek apakah ada pelanggaran aturan jadwal pada JadwalKerja tertentu
     */
    public function checkViolations(JadwalKerja $jadwalKerja): array
    {
        $violations = [];

        // Load details dengan karyawan.jabatan dan shift
        $details = $jadwalKerja->details()->with(['karyawan.jabatan', 'shift'])->get();

        // Group details berdasarkan karyawan
        $grouped = $details->groupBy('karyawan_id');

        foreach ($grouped as $karyawanId => $karyawanDetails) {
            $firstDetail = $karyawanDetails->first();
            $karyawan = $firstDetail->karyawan;
            if (!$karyawan) {
                continue;
            }

            // Ambil bagian_id untuk karyawan ini
            $bagianId = $karyawan->jabatan->first()?->id ? ($karyawan->jabatan->first()?->bagian_id ?? 1) : 1;

            // Ambil aturan aktif
            $maxMalam = $this->get($bagianId, KodeAturanJadwal::MAX_SHIFT_MALAM_BERTURUT);
            $minIstirahat = $this->get($bagianId, KodeAturanJadwal::MIN_ISTIRAHAT_JAM);
            $maxKerja = $this->get($bagianId, KodeAturanJadwal::MAX_HARI_KERJA_BERTURUT);

            // Urutkan details berdasarkan tanggal
            $sortedDetails = $karyawanDetails->sortBy('tanggal')->values();

            // 1. Cek Maks. Hari Kerja Berturut-turut
            $consecutiveWork = 0;
            foreach ($sortedDetails as $detail) {
                if ($detail->shift_id !== null) {
                    $consecutiveWork++;
                    if ($consecutiveWork > $maxKerja) {
                        $violations[] = [
                            'karyawan' => $karyawan->nama,
                            'tanggal' => $detail->tanggal->format('d M Y'),
                            'rule' => 'Maks. Hari Kerja Berturut-turut',
                            'message' => "Staf {$karyawan->nama} terjadwal bekerja {$consecutiveWork} hari berturut-turut melebihi batas {$maxKerja} hari."
                        ];
                    }
                } else {
                    $consecutiveWork = 0;
                }
            }

            // 2. Cek Maks. Shift Malam Berturut-turut
            $consecutiveMalam = 0;
            foreach ($sortedDetails as $detail) {
                $isMalam = $detail->shift && ($detail->shift->lintas_hari || str_contains(strtolower($detail->shift->kode), 'malam'));
                if ($isMalam) {
                    $consecutiveMalam++;
                    if ($consecutiveMalam > $maxMalam) {
                        $violations[] = [
                            'karyawan' => $karyawan->nama,
                            'tanggal' => $detail->tanggal->format('d M Y'),
                            'rule' => 'Maks. Shift Malam Berturut-turut',
                            'message' => "Staf {$karyawan->nama} terjadwal shift malam {$consecutiveMalam} hari berturut-turut melebihi batas {$maxMalam} hari."
                        ];
                    }
                } else {
                    $consecutiveMalam = 0;
                }
            }

            // 3. Cek Jeda Istirahat Minimal Antar Shift
            $count = $sortedDetails->count();
            for ($i = 0; $i < $count - 1; $i++) {
                $detail1 = $sortedDetails[$i];
                $detail2 = $sortedDetails[$i+1];

                if ($detail1->shift && $detail2->shift) {
                    $tgl1 = $detail1->tanggal;
                    $tgl2 = $detail2->tanggal;

                    $s1 = $detail1->shift;
                    $s2 = $detail2->shift;

                    $s1_out = $s1->jam_keluar;
                    $s2_in = $s2->jam_masuk;

                    try {
                        $endDateTime = Carbon::parse($tgl1->format('Y-m-d') . ' ' . $s1_out);
                        if ($s1->lintas_hari) {
                            $endDateTime->addDay();
                        }

                        $startDateTime = Carbon::parse($tgl2->format('Y-m-d') . ' ' . $s2_in);

                        $diffHours = $endDateTime->diffInHours($startDateTime, false);
                        if ($diffHours >= 0 && $diffHours < $minIstirahat) {
                            $violations[] = [
                                'karyawan' => $karyawan->nama,
                                'tanggal' => $detail2->tanggal->format('d M Y'),
                                'rule' => 'Minimum Jeda Istirahat Antar Shift',
                                'message' => "Staf {$karyawan->nama} memiliki jeda istirahat antar shift hanya {$diffHours} jam pada tanggal {$detail2->tanggal->format('d M Y')} (min. {$minIstirahat} jam)."
                            ];
                        }
                    } catch (\Throwable $e) {
                        // abaikan parsing error jika jam kosong/salah format
                    }
                }
            }
        }

        return $violations;
    }
}
