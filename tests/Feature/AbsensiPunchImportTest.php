<?php

namespace Tests\Feature;

use App\Imports\AbsensiPunchImport;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiRawPunch;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\Karyawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsensiPunchImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_imports_csv_raw_punch_file_and_creates_staging_records()
    {
        $karyawan = Karyawan::create([
            'nama'        => 'Abdul Rohmat',
            'nip'         => '21080003',
            'pin_absen'   => '21080003',
            'nik'         => '123456789',
            'status'      => 'tetap',
            'tgl_lahir'   => '1990-01-01',
            'tgl_masuk'   => '2020-01-01',
            'jk'          => 'L',
            'hp'          => '08123456789',
            'prov'        => 'Lampung',
            'kab'         => 'Bandar Lampung',
            'kec'         => 'Kedaton',
            'desa'        => 'Sidodadi',
            'alamat'      => 'Jl. Test No. 1',
            'agama'       => 'islam',
        ]);

        $importLog = AbsensiImportLog::create([
            'nama_file'     => 'Transaction_test.csv',
            'periode_awal'  => '2026-07-01',
            'periode_akhir' => '2026-07-31',
            'diunggah_oleh' => 1,
        ]);

        $csvContent = "No.,Employee ID,First Name,Last Name,Department,Date,Time,Punch State,Verification Method,Card No.,Device Name,Device SN,Data Sources,Reserved,Reserved 2,Reserved 3\n" .
            "1,21080003,Abdul,Rohmat,IT,17-06-2026,05:10,Check In,Fingerprint,123,Device1,SN1,Machine,,,\n" .
            "2,21080003,Abdul,Rohmat,IT,17-06-2026,05:13,Check In,Fingerprint,123,Device1,SN1,Machine,,,\n" . // duplicate <= 10 min
            "3,21080003,Abdul,Rohmat,IT,17-06-2026,14:00,Check Out,Fingerprint,123,Device1,SN1,Machine,,,\n" .
            "4,99999999,Unknown,User,HR,17-06-2026,08:00,Check In,Fingerprint,456,Device1,SN1,Machine,,,\n"; // unmatched

        $file = UploadedFile::fake()->createWithContent('Transaction_test.csv', $csvContent);

        $importer = new AbsensiPunchImport();
        $result = $importer->import($file, $importLog->id);

        $this->assertEquals(4, $result['totalRawRows']);
        $this->assertEquals(2, $result['totalPaired']); // 1 for Abdul, 1 for Unknown
        $this->assertEquals(1, $result['duplicateCount']); // 05:13 tap

        // Assert Raw Punch table
        $this->assertDatabaseCount('sdm_absensi_raw_punch', 4);
        $this->assertDatabaseHas('sdm_absensi_raw_punch', [
            'employee_id'  => '21080003',
            'is_discarded' => true,
        ]);

        // Assert Staging table
        $this->assertDatabaseHas('sdm_absensi_staging', [
            'employee_id_mentah' => '21080003',
            'clock_in_aktual'    => '05:10',
            'clock_out_aktual'   => '14:00',
            'status_matching'    => 'matched',
            'karyawan_id'        => $karyawan->id,
        ]);

        $this->assertDatabaseHas('sdm_absensi_staging', [
            'employee_id_mentah' => '99999999',
            'status_matching'    => 'unmatched',
            'karyawan_id'        => null,
        ]);

        // Assert Import Log update
        $importLog->refresh();
        $this->assertEquals(2, $importLog->total_baris);
        $this->assertEquals(1, $importLog->baris_matched);
        $this->assertEquals(1, $importLog->baris_unmatched);
    }

    /** @test */
    public function it_handles_long_catatan_mesin_exceeding_100_characters()
    {
        $karyawan = Karyawan::create([
            'nama'        => 'Seve Sinta',
            'nip'         => '22240416',
            'pin_absen'   => '22240416',
            'nik'         => '987654321',
            'status'      => 'tetap',
            'tgl_lahir'   => '1990-01-01',
            'tgl_masuk'   => '2020-01-01',
            'jk'          => 'P',
            'hp'          => '08123456780',
            'prov'        => 'Lampung',
            'kab'         => 'Bandar Lampung',
            'kec'         => 'Kedaton',
            'desa'        => 'Sidodadi',
            'alamat'      => 'Jl. Test No. 2',
            'agama'       => 'islam',
        ]);

        $importLog = AbsensiImportLog::create([
            'nama_file'     => 'Transaction_long_notes.csv',
            'periode_awal'  => '2026-07-01',
            'periode_akhir' => '2026-07-31',
            'diunggah_oleh' => 1,
        ]);

        $longNote = 'KONFLIK_JADWAL_VS_TAP (Jadwal REGULER tapi Tap Shift Sore/Malam); EXTRA_PUNCH (3 rekaman); DURASI_SANGAT_PANJANG (> 16 jam)';
        $this->assertGreaterThan(100, strlen($longNote));

        $staging = AbsensiStaging::create([
            'import_batch_id'    => $importLog->id,
            'employee_id_mentah' => '22240416',
            'nama_mentah'        => 'Seve Sinta',
            'tanggal'            => '2026-06-20',
            'clock_in_aktual'    => '14:20',
            'clock_out_aktual'   => '07:51',
            'catatan_mesin'      => $longNote,
            'karyawan_id'        => $karyawan->id,
            'status_matching'    => 'matched',
        ]);

        $fetched = AbsensiStaging::find($staging->id);
        $this->assertNotNull($fetched);
        $this->assertEquals($longNote, $fetched->catatan_mesin);
        $this->assertGreaterThan(100, strlen($fetched->catatan_mesin));
    }
}
