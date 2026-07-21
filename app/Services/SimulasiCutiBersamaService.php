<?php

namespace App\Services;

use App\Enums\KategoriKerja;
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
        $cutiBersama->loadMissing(['tanggal', 'jenisCuti']);

        $tanggals = $cutiBersama->tanggal->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
        
        $karyawans = Karyawan::with(['latestJabatan.jabatan'])->orderBy('nama')->get();
        $jenisCutiId = $cutiBersama->jenis_cuti_id ?? 1; // Default Cuti Tahunan

        $details = [];
        $totalPegawaiTerdampak = 0;
        $totalHariDipotong = 0;
        $totalPegawaiPiket = 0;
        $totalPegawaiLiburRoster = 0;
        $totalJadwalBelumAda = 0;

        $karyawanSummaryMap = [];

        foreach ($karyawans as $karyawan) {
            $isReguler = ($karyawan->kategori_kerja === KategoriKerja::REGULER);
            $hariDipotongKaryawan = 0;

            foreach ($tanggals as $tglStr) {
                $jadwalDetail = JadwalKerjaDetail::where('karyawan_id', $karyawan->id)
                    ->where('tanggal', $tglStr)
                    ->with('shift')
                    ->first();

                $hasil = [
                    'karyawan_id' => $karyawan->id,
                    'karyawan_nama' => $karyawan->full_nama,
                    'kategori_kerja' => $karyawan->kategori_kerja?->nama() ?? 'Reguler',
                    'tanggal' => $tglStr,
                    'shift_kode' => $jadwalDetail?->shift?->kode ?? null,
                    'shift_nama' => $jadwalDetail?->shift?->nama ?? 'Libur / Non-Shift',
                    'potong_cuti' => false,
                    'status_aksi' => '',
                    'keterangan' => '',
                ];

                if (!$jadwalDetail) {
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

                                    $hasAbsenStaging = \App\Models\Sdm\AbsensiStaging::where('karyawan_id', $karyawan->id)
                                        ->where('tanggal', $tglStr)
                                        ->where(function ($q) {
                                            $q->whereNotNull('clock_in_aktual')->orWhereNotNull('clock_out_aktual');
                                        })
                                        ->exists();

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

            $sisaCuti = $karyawan->getSisaCutiUntukJenis($jenisCutiId);
            $karyawanSummaryMap[$karyawan->id] = [
                'id' => $karyawan->id,
                'nama' => $karyawan->full_nama,
                'kategori' => $karyawan->kategori_kerja?->nama() ?? '-',
                'sisa_cuti_saat_ini' => $sisaCuti,
                'hari_terpotong' => $hariDipotongKaryawan,
                'sisa_cuti_setelah_event' => max(0, $sisaCuti - $hariDipotongKaryawan),
                'is_minus' => ($sisaCuti < $hariDipotongKaryawan),
            ];
        }

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
            'details' => $details,
            'karyawan_summary' => array_values($karyawanSummaryMap),
        ];
    }
}
