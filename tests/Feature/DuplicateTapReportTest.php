<?php

namespace Tests\Feature;

use App\Livewire\Kepegawaian\Absensi\DuplicateTapReport;
use App\Models\Sdm\AbsensiImportLog;
use App\Models\Sdm\AbsensiRawPunch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DuplicateTapReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Permission::findOrCreate('view-kepegawaian-absensi');
        $karyawan = \App\Models\Sdm\Karyawan::create([
            'nama' => 'Test Karyawan',
            'nip' => '12345' . rand(10,99),
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Bandar Lampung',
            'tgl_lahir' => '1995-01-01',
            'tgl_masuk' => '2020-01-01',
            'status' => 'tetap',
            'jk' => 'L',
            'agama' => 'Islam',
            'hp' => '08123456789',
            'prov' => '1', 'kab' => '1', 'kec' => '1', 'desa' => '1', 'alamat' => 'Test Address',
        ]);
        $user = \App\Models\User::create([
            'name'        => 'Test Admin',
            'email'       => 'testadmin' . rand(100,999) . '@rsba.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);
        $user->givePermissionTo('view-kepegawaian-absensi');
        $this->actingAs($user);
    }

    /** @test */
    public function it_renders_duplicate_tap_report_page_and_lists_discarded_taps()
    {
        $log = AbsensiImportLog::create([
            'nama_file'     => 'test_duplicate.csv',
            'periode_awal'  => '2026-07-01',
            'periode_akhir' => '2026-07-31',
            'diunggah_oleh' => 1,
        ]);

        $anchorTap = AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP100',
            'nama_mentah'    => 'Budi Test',
            'tanggal'        => '2026-07-20',
            'jam'            => '05:10:00',
            'is_discarded'   => false,
        ]);

        $discardedTap = AbsensiRawPunch::create([
            'import_log_id'          => $log->id,
            'employee_id'            => 'EMP100',
            'nama_mentah'            => 'Budi Test',
            'tanggal'                => '2026-07-20',
            'jam'                    => '05:13:00',
            'is_discarded'           => true,
            'discard_reason'         => 'Duplicate tap (<=10 menit dari tap anchor)',
            'duplicate_reference_id' => $anchorTap->id,
        ]);

        Livewire::test(DuplicateTapReport::class, ['logId' => $log->id])
            ->assertSee('EMP100')
            ->assertSee('Budi Test')
            ->assertSee('05:10:00')
            ->assertSee('05:13:00')
            ->assertSee('Duplicate tap');
    }

    /** @test */
    public function it_exports_csv_duplicate_report()
    {
        $log = AbsensiImportLog::create([
            'nama_file'     => 'test_export.csv',
            'periode_awal'  => '2026-07-01',
            'periode_akhir' => '2026-07-31',
            'diunggah_oleh' => 1,
        ]);

        $anchorTap = AbsensiRawPunch::create([
            'import_log_id'  => $log->id,
            'employee_id'    => 'EMP200',
            'nama_mentah'    => 'Siti Test',
            'tanggal'        => '2026-07-20',
            'jam'            => '08:00:00',
            'is_discarded'   => false,
        ]);

        AbsensiRawPunch::create([
            'import_log_id'          => $log->id,
            'employee_id'            => 'EMP200',
            'nama_mentah'            => 'Siti Test',
            'tanggal'                => '2026-07-20',
            'jam'                    => '08:05:00',
            'is_discarded'           => true,
            'discard_reason'         => 'Duplicate tap (<=10 menit)',
            'duplicate_reference_id' => $anchorTap->id,
        ]);

        Livewire::test(DuplicateTapReport::class, ['logId' => $log->id])
            ->call('exportCsv')
            ->assertFileDownloaded();
    }
}
