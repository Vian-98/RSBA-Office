<?php

namespace Tests\Unit;

use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiRawPunch;
use App\Models\Sdm\Karyawan;
use App\Services\AbsensiClearingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class AbsensiClearingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AbsensiClearingService $service;
    protected AbsensiImportLog $importLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AbsensiClearingService();
        $this->importLog = AbsensiImportLog::create([
            'nama_file'        => 'test.csv',
            'periode_awal'     => '2026-07-01',
            'periode_akhir'    => '2026-07-31',
            'diunggah_oleh'    => 1,
        ]);
    }

    /** @test */
    public function normal_two_taps_pairing()
    {
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP001',
            'nama_mentah'    => 'Budi',
            'tanggal'        => '2026-07-20',
            'jam'            => '07:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 07:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP001',
            'nama_mentah'    => 'Budi',
            'tanggal'        => '2026-07-20',
            'jam'            => '16:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 16:00:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertEquals(0, $result->duplicateCount);
        $this->assertEquals(0, $result->anomalyCount);
        $this->assertCount(1, $result->paired);
        $this->assertEquals('2026-07-20 07:00:00', $result->paired[0]['clock_in_aktual']);
        $this->assertEquals('2026-07-20 16:00:00', $result->paired[0]['clock_out_aktual']);
        $this->assertNull($result->paired[0]['catatan_mesin']);
    }

    /** @test */
    public function single_punch_flag()
    {
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP002',
            'nama_mentah'    => 'Siti',
            'tanggal'        => '2026-07-20',
            'jam'            => '07:30:00',
            'punch_datetime' => Carbon::parse('2026-07-20 07:30:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertEquals(1, $result->anomalyCount);
        $this->assertCount(1, $result->paired);
        $this->assertNull($result->paired[0]['clock_out_aktual']);
        $this->assertStringContainsString('SINGLE_PUNCH', $result->paired[0]['catatan_mesin']);
    }

    /** @test */
    public function anchor_strategy_deduplication()
    {
        // 00:00 (anchor), 00:08 (diff 8 <= 10 -> discard), 00:16 (diff 16 > 10 from 00:00 -> valid anchor baru)
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP003',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP003',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:08:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:08:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP003',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:16:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:16:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertEquals(1, $result->duplicateCount); // 00:08 discarded
        $this->assertCount(1, $result->paired);
        $this->assertEquals('2026-07-20 00:00:00', $result->paired[0]['clock_in_aktual']);
        $this->assertEquals('2026-07-20 00:16:00', $result->paired[0]['clock_out_aktual']);
    }

    /** @test */
    public function chain_taps_within_ten_minutes_all_discarded()
    {
        // 00:00 (anchor), 00:05 (discard), 00:08 (discard - since anchor remains 00:00 and diff is 8 <= 10)
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP004',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP004',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:05:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:05:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP004',
            'tanggal'        => '2026-07-20',
            'jam'            => '00:08:00',
            'punch_datetime' => Carbon::parse('2026-07-20 00:08:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertEquals(2, $result->duplicateCount);
        $this->assertNull($result->paired[0]['clock_out_aktual']);
        $this->assertStringContainsString('SINGLE_PUNCH', $result->paired[0]['catatan_mesin']);
    }

    /** @test */
    public function extra_punch_takes_min_and_max()
    {
        // 06:00, 12:00, 18:00
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP005',
            'tanggal'        => '2026-07-20',
            'jam'            => '06:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 06:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP005',
            'tanggal'        => '2026-07-20',
            'jam'            => '12:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 12:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP005',
            'tanggal'        => '2026-07-20',
            'jam'            => '18:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 18:00:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertEquals('2026-07-20 06:00:00', $result->paired[0]['clock_in_aktual']);
        $this->assertEquals('2026-07-20 18:00:00', $result->paired[0]['clock_out_aktual']);
        $this->assertStringContainsString('EXTRA_PUNCH', $result->paired[0]['catatan_mesin']);
    }

    /** @test */
    public function night_shift_cross_midnight_pairing()
    {
        $log = AbsensiImportLog::create([
            'nama_file' => 'test_night.csv',
            'periode_awal' => '2026-07-20',
            'periode_akhir' => '2026-07-22',
            'diunggah_oleh' => 1,
        ]);

        $karyawan = Karyawan::create([
            'nip'       => 'EMP_NIGHT',
            'nik'       => '3301000000000001',
            'nama'      => 'Night Worker',
            'pin_absen' => 'EMP_NIGHT',
            'status'    => 'tetap',
            'tgl_lahir' => '1995-01-01',
            'tgl_masuk' => '2020-01-01',
            'jk'        => 'L',
            'hp'        => '08123456789',
            'prov'      => 'Lampung',
            'kab'       => 'Bandar Lampung',
            'kec'       => 'Kedaton',
            'desa'      => 'Sidodadi',
            'alamat'    => 'Jl. Test',
            'agama'     => 'islam',
        ]);




        $shiftMalam = \App\Models\Sdm\JadwalShift::create([
            'kode' => 'MALAM_TEST',
            'nama' => 'Shift Malam Test',
            'jam_masuk' => '21:00:00',
            'jam_keluar' => '05:00:00',
            'lintas_hari' => true,
        ]);

        $jadwalHeader = \App\Models\Sdm\JadwalKerja::create([
            'bulan' => 7,
            'tahun' => 2026,
            'status' => 'published',
        ]);

        \App\Models\Sdm\JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalHeader->id,
            'karyawan_id' => $karyawan->id,
            'shift_id' => $shiftMalam->id,
            'tanggal' => '2026-07-20',
        ]);

        \App\Models\Sdm\JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalHeader->id,
            'karyawan_id' => $karyawan->id,
            'shift_id' => $shiftMalam->id,
            'tanggal' => '2026-07-21',
        ]);

        // Day 1 Evening tap: 20:48
        AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP_NIGHT',
            'tanggal'        => '2026-07-20',
            'jam'            => '20:48:00',
            'punch_datetime' => Carbon::parse('2026-07-20 20:48:00'),
        ]);

        // Day 2 Morning tap: 08:18 & Evening tap: 20:53
        AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP_NIGHT',
            'tanggal'        => '2026-07-21',
            'jam'            => '08:18:00',
            'punch_datetime' => Carbon::parse('2026-07-21 08:18:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP_NIGHT',
            'tanggal'        => '2026-07-21',
            'jam'            => '20:53:00',
            'punch_datetime' => Carbon::parse('2026-07-21 20:53:00'),
        ]);

        // Day 3 Morning tap: 08:11
        AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP_NIGHT',
            'tanggal'        => '2026-07-22',
            'jam'            => '08:11:00',
            'punch_datetime' => Carbon::parse('2026-07-22 08:11:00'),
        ]);

        $result = $this->service->clear($log->id);

        $this->assertCount(2, $result->paired);

        $this->assertEquals('2026-07-20', $result->paired[0]['tanggal']);
        $this->assertEquals('20:48', Carbon::parse($result->paired[0]['clock_in_aktual'])->format('H:i'));
        $this->assertEquals('08:18', Carbon::parse($result->paired[0]['clock_out_aktual'])->format('H:i'));

        $this->assertEquals('2026-07-21', $result->paired[1]['tanggal']);
        $this->assertEquals('20:53', Carbon::parse($result->paired[1]['clock_in_aktual'])->format('H:i'));
        $this->assertEquals('08:11', Carbon::parse($result->paired[1]['clock_out_aktual'])->format('H:i'));
    }



    /** @test */
    public function short_duration_sanity_check()
    {
        // 07:00 and 07:10 (diff 10 minutes, but wait: 10 minutes <= 10 gets deduplicated if dedup runs)
        // Let's use 07:00 and 07:12 (diff 12 minutes > 10 min dedup window, but < 15 min sanity check)
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP006',
            'tanggal'        => '2026-07-20',
            'jam'            => '07:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 07:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP006',
            'tanggal'        => '2026-07-20',
            'jam'            => '07:12:00',
            'punch_datetime' => Carbon::parse('2026-07-20 07:12:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertStringContainsString('DURASI_SANGAT_PENDEK', $result->paired[0]['catatan_mesin']);
    }

    /** @test */
    public function long_duration_sanity_check()
    {
        // 06:00:00 to 23:00:00 (17 hours = 1020 minutes > 960 minutes = 16 hours)
        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP007',
            'tanggal'        => '2026-07-20',
            'jam'            => '06:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 06:00:00'),
        ]);

        AbsensiRawPunch::create([
            'import_log_id'  => $this->importLog->id,
            'employee_id'    => 'EMP007',
            'tanggal'        => '2026-07-20',
            'jam'            => '23:00:00',
            'punch_datetime' => Carbon::parse('2026-07-20 23:00:00'),
        ]);

        $result = $this->service->clear($this->importLog->id);

        $this->assertStringContainsString('DURASI_SANGAT_PANJANG', $result->paired[0]['catatan_mesin']);
    }
}

