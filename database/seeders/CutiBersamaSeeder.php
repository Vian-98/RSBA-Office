<?php

namespace Database\Seeders;

use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\CutiBersamaTanggal;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\CutiJenis;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CutiBersamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cutiTahunan = CutiJenis::where('nama', 'like', '%tahunan%')->first();
        $jenisCutiId = $cutiTahunan ? $cutiTahunan->id : 1;

        // Pastikan detail jadwal kerja untuk pegawai di bulan Agustus 2026 sudah ter-generate
        $tahun = 2026;
        $bulan = 8;
        $karyawans = Karyawan::all();

        foreach ($karyawans as $karyawan) {
            JadwalKerja::ensureEmployeeDetailsExist($karyawan->id, $bulan, $tahun);
        }

        // 1. Event Cuti Bersama Idul Fitri 2026 (status disimulasikan)
        $event1 = CutiBersama::firstOrCreate(
            ['nama' => 'Cuti Bersama Idul Fitri 2026'],
            [
                'keterangan' => 'Sesuai SKB 3 Menteri Nomor 1022 Tahun 2026 tentang Hari Libur Nasional & Cuti Bersama',
                'jenis_cuti_id' => $jenisCutiId,
                'potong_cuti_tahunan' => true,
                'status' => 'disimulasikan',
                'created_by' => \App\Models\User::first()?->id,
            ]
        );

        $tglEvent1 = ['2026-08-11', '2026-08-12'];
        foreach ($tglEvent1 as $tgl) {
            CutiBersamaTanggal::firstOrCreate([
                'cuti_bersama_id' => $event1->id,
                'tanggal' => $tgl,
            ]);
        }

        // 2. Event Cuti Bersama Natal 2026 (status draft)
        $event2 = CutiBersama::firstOrCreate(
            ['nama' => 'Cuti Bersama Natal 2026'],
            [
                'keterangan' => 'Cuti Bersama Hari Raya Natal 2026',
                'jenis_cuti_id' => $jenisCutiId,
                'potong_cuti_tahunan' => true,
                'status' => 'draft',
                'created_by' => \App\Models\User::first()?->id,
            ]
        );

        $tglEvent2 = ['2026-08-26'];
        foreach ($tglEvent2 as $tgl) {
            CutiBersamaTanggal::firstOrCreate([
                'cuti_bersama_id' => $event2->id,
                'tanggal' => $tgl,
            ]);
        }
    }
}
