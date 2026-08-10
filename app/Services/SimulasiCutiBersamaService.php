<?php

namespace App\Services;

use App\Enums\KategoriKerja;
use App\Models\Surat\CutiJenis;
use App\Models\Surat\SuratCuti;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use Carbon\Carbon;

class SimulasiCutiBersamaService
{
    /**
     * Jalankan simulasi Cuti Bersama secara read-only.
     */
    public function simulasikan(CutiBersama $cutiBersama): array
    {
        $cutiBersama->loadMissing(['tanggal', 'jenisCuti', 'partisipasiKaryawan']);

        $tanggals = $cutiBersama->tanggal->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        // Map partisipasi (is_ikut: true/false) dari database
        $partisipasiMap = \App\Models\Sdm\CutiBersamaKaryawan::where('cuti_bersama_id', $cutiBersama->id)
            ->pluck('is_ikut', 'karyawan_id')
            ->all();

        $karyawans = Karyawan::with(['latestJabatan.jabatan'])->orderBy('nama')->get();
        $karyawanIds = $karyawans->pluck('id')->all();
        $jenisCutiId = $cutiBersama->jenis_cuti_id ?? 1; // Default Cuti Tahunan

        $jadwalDetailMap = JadwalKerjaDetail::query()
            ->whereIn('karyawan_id', $karyawanIds)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(tanggal)'), $tanggals)
            ->with('shift')
            ->get()
            ->keyBy(fn($row) => $row->karyawan_id . '|' . Carbon::parse($row->tanggal)->format('Y-m-d'));

        $absensiStagingMap = AbsensiStaging::query()
            ->whereIn('karyawan_id', $karyawanIds)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(tanggal)'), $tanggals)
            ->where(function ($q) {
                $q->whereNotNull('clock_in_aktual')->orWhereNotNull('clock_out_aktual');
            })
            ->get(['karyawan_id', 'tanggal'])
            ->mapWithKeys(fn($row) => [
                $row->karyawan_id . '|' . Carbon::parse($row->tanggal)->format('Y-m-d') => true,
            ]);

        $sisaCutiMap = $this->hitungSisaCutiPerKaryawan($karyawans, $jenisCutiId);

        $details = [];
        $totalPegawaiTerdampak = 0;
        $totalHariDipotong = 0;
        $totalPegawaiPiket = 0;
        $totalPegawaiLiburRoster = 0;
        $totalJadwalBelumAda = 0;
        $totalPegawaiDikecualikan = 0;

        $karyawanSummaryMap = [];
        $totalPegawaiDefisit = 0;
        $totalHariDefisit    = 0;

        foreach ($karyawans as $karyawan) {
            $isReguler = ($karyawan->kategori_kerja === KategoriKerja::REGULER);
            $isIkut    = isset($partisipasiMap[$karyawan->id]) ? (bool)$partisipasiMap[$karyawan->id] : true;
            $hariDipotongKaryawan = 0;

            foreach ($tanggals as $tglStr) {
                $jadwalDetail = $jadwalDetailMap->get($karyawan->id . '|' . $tglStr);

                $hasil = [
                    'karyawan_id' => $karyawan->id,
                    'karyawan_nama' => $karyawan->full_nama,
                    'kategori_kerja' => $karyawan->kategori_kerja?->nama() ?? 'Reguler',
                    'tanggal' => $tglStr,
                    'shift_kode' => $jadwalDetail?->shift?->kode ?? null,
                    'shift_nama' => $jadwalDetail?->shift?->nama ?? 'Libur / Non-Shift',
                    'potong_cuti' => false,
                    'is_ikut'     => $isIkut,
                    'status_aksi' => '',
                    'keterangan' => '',
                ];

                if (!$isIkut) {
                    $hasil['status_aksi'] = 'DIKECUALIKAN';
                    $hasil['keterangan'] = 'Dikecualikan dari Cuti Bersama (Tidak Ikut)';
                } elseif (!$jadwalDetail) {
                    $totalJadwalBelumAda++;
                    $hasil['status_aksi'] = 'JADWAL_BELUM_ADA';
                    $hasil['keterangan'] = 'Jadwal kerja belum dibuat (akan diproses jika dibuat susulan)';
                } else {
                    $isShiftWorker = ($karyawan->kategori_kerja === KategoriKerja::SHIFT);
                    $hasShift = !is_null($jadwalDetail->shift_id);
                    $isRegulerShift = ($hasShift && strtoupper($jadwalDetail->shift?->kode ?? '') === 'REGULER');

                    if ($cutiBersama->potong_cuti_tahunan) {
                        if ($isShiftWorker) {
                            if ($hasShift) {
                                // Shift worker scheduled to work (Piket/Shift)
                                $hasil['status_aksi'] = 'TETAP_HADIR';
                                $hasil['keterangan'] = 'Masuk piket shift, tidak memotong cuti';
                                $totalPegawaiPiket++;
                            } else {
                                // Shift worker off day
                                $hasil['status_aksi'] = 'LIBUR_ROSTER';
                                $hasil['keterangan'] = 'Libur roster shift, tidak memotong cuti';
                                $totalPegawaiLiburRoster++;
                            }
                        } else {
                            // Reguler worker
                            if ($hasShift || $isReguler) {
                                $dateObj = Carbon::parse($tglStr);
                                $isWeekend = ($dateObj->dayOfWeekIso >= 6);

                                if ($isWeekend && !$hasShift) {
                                    $hasil['status_aksi'] = 'LIBUR_WEEKEND';
                                    $hasil['keterangan'] = 'Libur akhir pekan reguler, tidak memotong cuti';
                                } else {
                                    // Cek apakah ada record absen di jadwal_detail atau absensi_staging
                                    $hasAbsenJadwal = (!is_null($jadwalDetail->absen_masuk_at) || !is_null($jadwalDetail->absen_keluar_at) || in_array($jadwalDetail->status_kehadiran?->value ?? $jadwalDetail->status_kehadiran, ['hadir', 'terlambat', 'pulang_cepat']));

                                    $hasAbsenStaging = (bool) $absensiStagingMap->get($karyawan->id . '|' . $tglStr, false);

                                    if ($hasAbsenJadwal || $hasAbsenStaging) {
                                        $hasil['status_aksi'] = 'TETAP_HADIR';
                                        $hasil['keterangan'] = 'Terdeteksi ada presensi/absen mesin pada tanggal ini, tidak memotong cuti';
                                        $totalPegawaiPiket++;
                                    } else {
                                        $hasil['potong_cuti'] = true;
                                        $hasil['status_aksi'] = 'DIPOTONG_CUTI';
                                        $hasil['keterangan'] = 'Memotong kuota Cuti Tahunan';
                                        $hariDipotongKaryawan++;
                                        $totalHariDipotong++;
                                    }
                                }
                            }
                        }
                    } else {
                        // Event bebas / tidak memotong cuti
                        $hasil['status_aksi'] = 'CUTI_BERSAMA_BEBAS';
                        $hasil['keterangan'] = 'Libur bersama bebas (tidak memotong kuota)';
                    }
                }

                $details[] = $hasil;
            }

            if ($hariDipotongKaryawan > 0) {
                $totalPegawaiTerdampak++;
            }

            $sisaCuti = (int) ($sisaCutiMap[$karyawan->id] ?? 0);
            $isMinus = ($sisaCuti < $hariDipotongKaryawan);
            $hariDefisit = $isMinus ? ($hariDipotongKaryawan - $sisaCuti) : 0;

            if ($isMinus) {
                $totalPegawaiDefisit++;
                $totalHariDefisit += $hariDefisit;
            }

            $karyawanSummaryMap[$karyawan->id] = [
                'id' => $karyawan->id,
                'nama' => $karyawan->full_nama,
                'kategori' => $karyawan->kategori_kerja?->nama() ?? '-',
                'sisa_cuti_saat_ini' => $sisaCuti,
                'hari_terpotong' => $hariDipotongKaryawan,
                'sisa_cuti_setelah_event' => max(0, $sisaCuti - $hariDipotongKaryawan),
                'is_minus' => $isMinus,
                'hari_defisit' => $hariDefisit,
            ];
        }

        $totalPegawaiDikecualikan = count(array_filter($partisipasiMap, fn($v) => !(bool)$v));

        return [
            'cuti_bersama_id' => $cutiBersama->id,
            'nama_event' => $cutiBersama->nama,
            'potong_cuti_tahunan' => $cutiBersama->potong_cuti_tahunan,
            'jenis_cuti' => $cutiBersama->jenisCuti?->nama ?? 'Cuti Tahunan',
            'total_pegawai' => count($karyawans),
            'total_pegawai_terdampak' => $totalPegawaiTerdampak,
            'total_hari_dipotong' => $totalHariDipotong,
            'total_pegawai_piket' => $totalPegawaiPiket,
            'total_pegawai_libur_roster' => $totalPegawaiLiburRoster,
            'total_jadwal_belum_ada' => $totalJadwalBelumAda,
            'total_pegawai_dikecualikan' => $totalPegawaiDikecualikan,
            'total_pegawai_ikut' => count($karyawans) - $totalPegawaiDikecualikan,
            'total_pegawai_defisit' => $totalPegawaiDefisit,
            'total_hari_defisit' => $totalHariDefisit,
            'details' => $details,
            'karyawan_summary' => array_values($karyawanSummaryMap),
        ];
    }

    /**
     * Hitung sisa cuti per karyawan dengan query batch agar tidak terjadi N+1 query.
     *
     * @param \Illuminate\Support\Collection<int, Karyawan> $karyawans
     * @return array<int, int>
     */
    protected function hitungSisaCutiPerKaryawan($karyawans, int $jenisCutiId): array
    {
        $jenis = CutiJenis::find($jenisCutiId);

        if (!$jenis) {
            return $karyawans->mapWithKeys(fn($karyawan) => [$karyawan->id => 0])->all();
        }

        $now = Carbon::now();
        $quota = (int) $jenis->lama;
        $periodePerKaryawan = [];
        $karyawanIds = [];

        foreach ($karyawans as $karyawan) {
            $karyawanIds[] = $karyawan->id;

            if (empty($karyawan->tgl_masuk)) {
                $periodePerKaryawan[$karyawan->id] = ['result' => 0];
                continue;
            }

            $tglMasuk = Carbon::parse($karyawan->tgl_masuk);

            // Cuti Tahunan (ID = 1) requires 1 year of service
            if ($jenisCutiId === 1 && $now->lt($tglMasuk->copy()->addYear())) {
                $periodePerKaryawan[$karyawan->id] = ['result' => -1];
                continue;
            }

            if ($jenis->periode === 'Y') {
                $anniversaryThisYear = $tglMasuk->copy()->year($now->year);
                if ($now->gte($anniversaryThisYear)) {
                    $startDate = $anniversaryThisYear;
                    $endDate = $anniversaryThisYear->copy()->addYear();
                } else {
                    $startDate = $anniversaryThisYear->copy()->subYear();
                    $endDate = $anniversaryThisYear;
                }
            } elseif ($jenis->periode === 'M') {
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
            } else {
                $startDate = Carbon::parse('1970-01-01');
                $endDate = Carbon::parse('2099-12-31');
            }

            $periodePerKaryawan[$karyawan->id] = [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ];
        }

        $periodeAktif = array_filter($periodePerKaryawan, fn($periode) => !isset($periode['result']));
        $usedByKaryawan = [];

        if (!empty($periodeAktif)) {
            $globalStart = min(array_column($periodeAktif, 'start'));
            $globalEnd = max(array_column($periodeAktif, 'end'));

            $rows = SuratCuti::query()
                ->whereIn('karyawan_id', $karyawanIds)
                ->where('urgensi_id', $jenisCutiId)
                ->where('status', '!=', 'rejected')
                ->whereBetween('tgl_mulai', [$globalStart, $globalEnd])
                ->get(['karyawan_id', 'tgl_mulai', 'lama_cuti']);

            foreach ($rows as $row) {
                $karyawanId = (int) $row->karyawan_id;
                $periode = $periodePerKaryawan[$karyawanId] ?? null;

                if (!$periode || isset($periode['result'])) {
                    continue;
                }

                $tglMulai = Carbon::parse($row->tgl_mulai)->format('Y-m-d');

                if ($tglMulai >= $periode['start'] && $tglMulai <= $periode['end']) {
                    $usedByKaryawan[$karyawanId] = ($usedByKaryawan[$karyawanId] ?? 0) + (int) $row->lama_cuti;
                }
            }
        }

        $result = [];
        foreach ($karyawanIds as $karyawanId) {
            $periode = $periodePerKaryawan[$karyawanId] ?? ['result' => 0];

            if (isset($periode['result'])) {
                $result[$karyawanId] = (int) $periode['result'];
                continue;
            }

            $used = (int) ($usedByKaryawan[$karyawanId] ?? 0);
            $result[$karyawanId] = max(0, $quota - $used);
        }

        return $result;
    }
}
