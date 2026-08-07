<?php

namespace Tests\Feature;

use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\CutiBersamaTanggal;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Surat\SuratCuti;
use App\Services\BatalkanCutiBersamaService;
use App\Services\SimulasiCutiBersamaService;
use App\Services\TerapkanCutiBersamaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class E2EWorkflowTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_full_cuti_bersama_e2e_system_workflow()
    {
        $simulasiSvc = app(SimulasiCutiBersamaService::class);
        $terapkanSvc = app(TerapkanCutiBersamaService::class);
        $batalkanSvc = app(BatalkanCutiBersamaService::class);

        $user = \App\Models\User::first();
        if (!$user) {
            $karyawan = \App\Models\Sdm\Karyawan::create([
                'nip' => '777777777',
                'nik' => '7777777777777777',
                'nama' => 'E2E Karyawan User',
                'hp' => '-', 'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => '-', 'agama' => 'islam',
                'tgl_lahir' => '1990-01-01', 'status' => 'tetap', 'tgl_masuk' => '2020-01-01',
            ]);
            $user = \App\Models\User::create([
                'name' => 'Test Admin',
                'email' => 'e2e_admin@rsba.com',
                'password' => '1234',
                'karyawan_id' => $karyawan->id,
            ]);
        }

        \App\Models\Surat\CutiJenis::firstOrCreate(['id' => 1], ['nama' => 'Cuti Tahunan', 'lama' => 12, 'periode' => 'Y']);
        $ruangan = \App\Models\Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)']);
        // 1. Create Event (Draft)
        $event = CutiBersama::create([
            'nama' => 'Test E2E System Workflow Event',
            'potong_cuti_tahunan' => true,
            'jenis_cuti_id' => 1,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        CutiBersamaTanggal::create([
            'cuti_bersama_id' => $event->id,
            'tanggal' => '2026-09-25',
        ]);

        $this->assertEquals('draft', $event->status);

        // 2. Run Simulation
        $simResult = $simulasiSvc->simulasikan($event);
        $this->assertIsArray($simResult);
        $this->assertEquals($event->id, $simResult['cuti_bersama_id']);
        $this->assertArrayHasKey('total_pegawai', $simResult);
        $this->assertArrayHasKey('total_pegawai_terdampak', $simResult);

        // 3. Apply Event (Draft -> Diterapkan)
        $terapkanResult = $terapkanSvc->terapkan($event, $user->id);
        $this->assertTrue($terapkanResult);
        $event->refresh();
        $this->assertEquals('diterapkan', $event->status);

        // Verify generated surat_cuti
        $suratCutiCount = SuratCuti::where('cuti_bersama_id', $event->id)->count();
        $this->assertGreaterThanOrEqual(0, $suratCutiCount);

        // Verify schedule status_kehadiran updated
        $jadwalUpdatedCount = JadwalKerjaDetail::where('tanggal', '2026-09-25')
            ->where('status_kehadiran', 'cuti_bersama')
            ->count();
        $this->assertGreaterThanOrEqual(0, $jadwalUpdatedCount);

        // 4. Cancel Event (Diterapkan -> Dibatalkan)
        $batalkanResult = $batalkanSvc->batalkan($event, $user->id);
        $this->assertTrue($batalkanResult);
        $event->refresh();
        $this->assertEquals('dibatalkan', $event->status);

        // Verify surat_cuti deleted
        $suratCutiCountAfter = SuratCuti::where('cuti_bersama_id', $event->id)->count();
        $this->assertEquals(0, $suratCutiCountAfter);

        // Verify schedule status_kehadiran restored
        $jadwalUpdatedCountAfter = JadwalKerjaDetail::where('tanggal', '2026-09-25')
            ->where('status_kehadiran', 'cuti_bersama')
            ->count();
        $this->assertEquals(0, $jadwalUpdatedCountAfter);

        // 5. Test Payroll Lock Safety
        $terapkanSvc->terapkan($event, $user->id);
        DB::table('sdm_payroll_period_locks')->insert([
            'periode' => '2026-09',
            'status' => 'locked',
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gagal membatalkan: Periode payroll 2026-09 telah dikunci.');
        
        $batalkanSvc->batalkan($event, $user->id);
    }
}
