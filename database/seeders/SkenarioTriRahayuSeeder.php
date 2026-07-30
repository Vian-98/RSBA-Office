<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Enums\StatusApproval;
use App\Enums\StatusKehadiran;
use Carbon\Carbon;

class SkenarioTriRahayuSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Memulai Seeder Skenario Absensi Tri Rahayu...");

        // 1. Cari Karyawan Tri Rahayu
        $karyawan = Karyawan::where('pin_absen', '22240404')
            ->orWhere('nama', 'like', '%Tri Rahayu%')
            ->first();

        if (!$karyawan) {
            $this->command->error("Karyawan Tri Rahayu tidak ditemukan!");
            return;
        }

        $karyawanId = $karyawan->id;

        // Hapus data cuti lama untuk Tri Rahayu di bulan Juni 2026 agar tidak duplikat
        SuratCuti::where('karyawan_id', $karyawanId)
            ->whereMonth('tgl_mulai', 6)
            ->whereYear('tgl_mulai', 2026)
            ->delete();

        $cutiTahunan = \App\Models\Surat\CutiJenis::firstOrCreate(
            ['nama' => 'Cuti Tahunan'],
            ['lama' => 12, 'periode' => 'Y']
        );
        $cutiAlasanPenting = \App\Models\Surat\CutiJenis::firstOrCreate(
            ['nama' => 'Cuti Alasan Penting'],
            ['lama' => 0, 'periode' => 'Y']
        );

        $cutiTahunanId = $cutiTahunan->id;
        $cutiAlasanPentingId = $cutiAlasanPenting->id;

        // 2. Buat Surat Cuti Tahunan (10 - 12 Juni 2026)
        $cuti = SuratCuti::create([
            'karyawan_id' => $karyawanId,
            'no_surat' => 'C-001',
            'urgensi_id' => $cutiTahunanId,
            'tgl_surat' => '2026-06-08',
            'tgl_mulai' => '2026-06-10',
            'tgl_akhir' => '2026-06-12',
            'tgl_cuti' => '2026-06-10',
            'lama_cuti' => 3,
            'keterangan' => 'Liburan keluarga',
            'status' => StatusApproval::APPROVED,
            'created_by' => \App\Models\User::first()?->id,
        ]);

        // 3. Buat Surat Izin / Alasan Penting (18 - 19 Juni 2026)
        $izin = SuratCuti::create([
            'karyawan_id' => $karyawanId,
            'no_surat' => 'I-002',
            'urgensi_id' => $cutiAlasanPentingId,
            'tgl_surat' => '2026-06-15',
            'tgl_mulai' => '2026-06-18',
            'tgl_akhir' => '2026-06-19',
            'tgl_cuti' => '2026-06-18',
            'lama_cuti' => 2,
            'keterangan' => 'Mengurus dokumen keluarga penting',
            'status' => StatusApproval::APPROVED,
            'created_by' => \App\Models\User::first()?->id,
        ]);

        // 4. Update Jadwal Kerja Detail untuk mencerminkan Cuti, Izin, dan Alfa
        // - Cuti (10, 11, 12 Juni)
        JadwalKerjaDetail::where('karyawan_id', $karyawanId)
            ->whereIn('tanggal', ['2026-06-10', '2026-06-11', '2026-06-12'])
            ->update([
                'status_kehadiran' => StatusKehadiran::CUTI,
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Cuti resmi (' . $cuti->no_surat . ')',
            ]);

        // - Izin (18, 19 Juni)
        JadwalKerjaDetail::where('karyawan_id', $karyawanId)
            ->whereIn('tanggal', ['2026-06-18', '2026-06-19'])
            ->update([
                'status_kehadiran' => StatusKehadiran::IZIN,
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Izin resmi (' . $izin->no_surat . ')',
            ]);

        // - Alfa / Mangkir (24 Juni 2026)
        JadwalKerjaDetail::where('karyawan_id', $karyawanId)
            ->where('tanggal', '2026-06-24')
            ->update([
                'status_kehadiran' => StatusKehadiran::TIDAK_HADIR, // Mangkir
                'absen_masuk_at' => null,
                'absen_keluar_at' => null,
                'catatan' => 'Mangkir / Tanpa keterangan (Alfa)',
            ]);

        $this->command->info("Selesai! Skenario absensi Tri Rahayu (3 Cuti, 2 Izin, 1 Alfa) berhasil dimasukkan.");
    }
}
