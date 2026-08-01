<?php

namespace Tests\Feature;

use App\Enums\KategoriKerja;
use App\Enums\StatusApproval;
use App\Enums\StatusKehadiran;
use App\Models\Sdm\AbsensiStaging;
use App\Models\Sdm\CutiBersama;
use App\Models\Sdm\CutiBersamaTanggal;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use App\Models\User;
use App\Services\BatalkanCutiBersamaService;
use App\Services\SimulasiCutiBersamaService;
use App\Services\TerapkanCutiBersamaService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Exception;

class CutiBersamaTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function loginUser(): User
    {
        \App\Models\Surat\CutiJenis::firstOrCreate(['id' => 1], ['nama' => 'Cuti Tahunan', 'lama' => 12, 'periode' => 'Y']);

        $user = User::first();
        if (!$user) {
            $karyawan = Karyawan::create([
                'nip' => '888888888',
                'nik' => '8888888888888888',
                'nama' => 'Test Karyawan User',
                'hp' => '-', 'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => '-', 'agama' => 'islam',
                'tgl_lahir' => '1990-01-01', 'status' => 'tetap', 'tgl_masuk' => '2020-01-01',
            ]);
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'admin_test@rsba.com',
                'password' => '1234',
                'karyawan_id' => $karyawan->id,
            ]);
        }
        $this->actingAs($user);
        return $user;
    }

    protected function createTestKaryawanReguler($ruanganId, $nip = '111111111', $nama = 'Reguler User Test')
    {
        return Karyawan::create([
            'nip' => $nip,
            'nik' => $nip . $nip,
            'nama' => $nama,
            'hp' => '-', 'prov' => '-', 'kab' => '-', 'kec' => '-', 'desa' => '-', 'alamat' => '-', 'agama' => 'islam',
            'tgl_lahir' => '1990-01-01', 'status' => 'tetap', 'tgl_masuk' => '2020-01-01',
            'ruangan_id' => $ruanganId,
            'kategori_kerja' => KategoriKerja::REGULER,
        ]);
    }

    #[Test]
    public function simulasi_cuti_bersama_memotongan_kuota_reguler_dan_membatasi_piket_dan_absensi(): void
    {
        $user = $this->loginUser();
        $ruangan = \App\Models\Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)']);

        // 1. Setup Event Cuti Bersama 1 Hari
        $tglTest = '2026-09-15';
        $event = CutiBersama::create([
            'nama' => 'Test Cuti Bersama UAT',
            'keterangan' => 'Testing Simulasi Cuti Bersama',
            'jenis_cuti_id' => 1,
            'potong_cuti_tahunan' => true,
            'status' => 'draft',
        ]);

        CutiBersamaTanggal::create([
            'cuti_bersama_id' => $event->id,
            'tanggal' => $tglTest,
        ]);

        $shift = \App\Models\Sdm\JadwalShift::firstOrCreate(['kode' => 'REGULER'], ['nama' => 'Reguler Pagi', 'jam_masuk' => '07:30:00', 'jam_keluar' => '16:00:00', 'created_by' => 1]);
        $jadwalKerja = \App\Models\Sdm\JadwalKerja::firstOrCreate(['bulan' => 9, 'tahun' => 2026, 'ruangan_id' => $ruangan->id], ['created_by' => 1]);
        
        $karyawanReguler = $this->createTestKaryawanReguler($ruangan->id, '111111111', 'Reguler User 1');
        JadwalKerjaDetail::updateOrCreate(
            ['karyawan_id' => $karyawanReguler->id, 'tanggal' => $tglTest],
            ['jadwal_kerja_id' => $jadwalKerja->id, 'shift_id' => $shift->id, 'status_kehadiran' => StatusKehadiran::BELUM_DICEK]
        );

        // 3. Setup Pegawai Reguler YANG MASUK / TAP ABSEN (harus TETAP_HADIR, TIDAK dipotong)
        $karyawanAbsen = $this->createTestKaryawanReguler($ruangan->id, '222222222', 'Reguler User 2');
        JadwalKerjaDetail::updateOrCreate(
            ['karyawan_id' => $karyawanAbsen->id, 'tanggal' => $tglTest],
            ['jadwal_kerja_id' => $jadwalKerja->id, 'shift_id' => $shift->id, 'status_kehadiran' => StatusKehadiran::BELUM_DICEK]
        );

        AbsensiStaging::updateOrCreate(
            ['karyawan_id' => $karyawanAbsen->id, 'tanggal' => $tglTest],
            ['import_batch_id' => 1, 'employee_id_mentah' => 'TEST01', 'clock_in_aktual' => '07:30', 'clock_out_aktual' => '16:00', 'status_matching' => 'matched']
        );

        // 4. Jalankan Simulasi
        $simulasiService = app(SimulasiCutiBersamaService::class);
        $hasil = $simulasiService->simulasikan($event);

        $this->assertEquals($event->id, $hasil['cuti_bersama_id']);
        $this->assertGreaterThanOrEqual(1, $hasil['total_pegawai']);

        // Verifikasi Pegawai Reguler Tanpa Absen -> DIPOTONG_CUTI
        $rowReguler = collect($hasil['details'])->firstWhere('karyawan_id', $karyawanReguler->id);
        $this->assertEquals('DIPOTONG_CUTI', $rowReguler['status_aksi']);
        $this->assertTrue($rowReguler['potong_cuti']);

        // Verifikasi Pegawai Reguler dengan Absen -> TETAP_HADIR
        $rowAbsen = collect($hasil['details'])->firstWhere('karyawan_id', $karyawanAbsen->id);
        $this->assertEquals('TETAP_HADIR', $rowAbsen['status_aksi']);
        $this->assertFalse($rowAbsen['potong_cuti']);
    }

    #[Test]
    public function terapkan_cuti_bersama_membangkitkan_surat_cuti_dan_update_status_kehadiran(): void
    {
        $user = $this->loginUser();

        $tglTest = '2026-09-16';
        $event = CutiBersama::create([
            'nama' => 'Test Terapkan Event',
            'jenis_cuti_id' => 1,
            'potong_cuti_tahunan' => true,
            'status' => 'draft',
        ]);

        CutiBersamaTanggal::create([
            'cuti_bersama_id' => $event->id,
            'tanggal' => $tglTest,
        ]);

        $ruangan = \App\Models\Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)']);
        $shift = \App\Models\Sdm\JadwalShift::firstOrCreate(['kode' => 'REGULER'], ['nama' => 'Reguler Pagi', 'jam_masuk' => '07:30:00', 'jam_keluar' => '16:00:00', 'created_by' => 1]);
        $jadwalKerja = \App\Models\Sdm\JadwalKerja::firstOrCreate(['bulan' => 9, 'tahun' => 2026, 'ruangan_id' => $ruangan->id], ['created_by' => 1]);
        $karyawanReguler = $this->createTestKaryawanReguler($ruangan->id, '333333333', 'Reguler User 3');

        JadwalKerjaDetail::updateOrCreate(
            ['karyawan_id' => $karyawanReguler->id, 'tanggal' => $tglTest],
            ['jadwal_kerja_id' => $jadwalKerja->id, 'shift_id' => $shift->id, 'status_kehadiran' => StatusKehadiran::BELUM_DICEK]
        );

        $terapkanService = app(TerapkanCutiBersamaService::class);
        $success = $terapkanService->terapkan($event, $user->id);

        $this->assertTrue($success);
        $this->assertEquals('diterapkan', $event->fresh()->status);

        // Verifikasi surat_cuti otomatis dibuat
        $suratCuti = SuratCuti::where('karyawan_id', $karyawanReguler->id)
            ->where('cuti_bersama_id', $event->id)
            ->first();

        $this->assertNotNull($suratCuti);
        $this->assertEquals('cuti_bersama', $suratCuti->sumber);
        $this->assertEquals(StatusApproval::APPROVED, $suratCuti->status);

        // Verifikasi status_kehadiran berubah jadi CUTI_BERSAMA
        $detail = JadwalKerjaDetail::where('karyawan_id', $karyawanReguler->id)
            ->whereDate('tanggal', $tglTest)
            ->first();

        $this->assertEquals(StatusKehadiran::CUTI_BERSAMA, $detail->status_kehadiran);
    }

    #[Test]
    public function batalkan_cuti_bersama_menghapus_surat_cuti_dan_restore_jadwal(): void
    {
        $user = $this->loginUser();

        $tglTest = '2026-09-17';
        $event = CutiBersama::create([
            'nama' => 'Test Cancel Event',
            'jenis_cuti_id' => 1,
            'potong_cuti_tahunan' => true,
            'status' => 'draft',
        ]);

        CutiBersamaTanggal::create([
            'cuti_bersama_id' => $event->id,
            'tanggal' => $tglTest,
        ]);

        $ruangan = \App\Models\Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)']);
        $shift = \App\Models\Sdm\JadwalShift::firstOrCreate(['kode' => 'REGULER'], ['nama' => 'Reguler Pagi', 'jam_masuk' => '07:30:00', 'jam_keluar' => '16:00:00', 'created_by' => 1]);
        $jadwalKerja = \App\Models\Sdm\JadwalKerja::firstOrCreate(['bulan' => 9, 'tahun' => 2026, 'ruangan_id' => $ruangan->id], ['created_by' => 1]);
        $karyawanReguler = $this->createTestKaryawanReguler($ruangan->id, '444444444', 'Reguler User 4');

        JadwalKerjaDetail::updateOrCreate(
            ['karyawan_id' => $karyawanReguler->id, 'tanggal' => $tglTest],
            ['jadwal_kerja_id' => $jadwalKerja->id, 'shift_id' => $shift->id, 'status_kehadiran' => StatusKehadiran::BELUM_DICEK]
        );

        // Terapkan dulu
        app(TerapkanCutiBersamaService::class)->terapkan($event, $user->id);

        // Batalkan
        $batalkanService = app(BatalkanCutiBersamaService::class);
        $success = $batalkanService->batalkan($event, $user->id);

        $this->assertTrue($success);
        $this->assertEquals('dibatalkan', $event->fresh()->status);

        if ($karyawanReguler) {
            // Verifikasi surat_cuti terhapus
            $suratCuti = SuratCuti::where('karyawan_id', $karyawanReguler->id)
                ->where('cuti_bersama_id', $event->id)
                ->first();

            $this->assertNull($suratCuti);

            // Verifikasi status_kehadiran dikembalikan
            $detail = JadwalKerjaDetail::where('karyawan_id', $karyawanReguler->id)
                ->whereDate('tanggal', $tglTest)
                ->first();

            $this->assertEquals(StatusKehadiran::BELUM_DICEK, $detail->status_kehadiran);
        }
    }

    #[Test]
    public function batalkan_cuti_bersama_gagal_jika_payroll_locked(): void
    {
        $user = $this->loginUser();

        $tglTest = '2026-09-18';
        $event = CutiBersama::create([
            'nama' => 'Test Locked Payroll Event',
            'jenis_cuti_id' => 1,
            'potong_cuti_tahunan' => true,
            'status' => 'diterapkan',
        ]);

        CutiBersamaTanggal::create([
            'cuti_bersama_id' => $event->id,
            'tanggal' => $tglTest,
        ]);

        // Lock payroll periode 2026-09
        DB::table('sdm_payroll_period_locks')->updateOrInsert(
            ['periode' => '2026-09'],
            ['is_approved' => true, 'approved_by' => $user->id, 'approved_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('payroll');

        app(BatalkanCutiBersamaService::class)->batalkan($event, $user->id);
    }
}
