<?php

namespace Tests\Feature;

use App\Enums\StatusTukarJadwal;
use App\Models\Ruangan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\TukarJadwalDokter;
use App\Models\User;
use App\Services\TukarJadwalDokterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TukarJadwalDokterTest extends TestCase
{
    use RefreshDatabase;

    protected Karyawan $dokterA;
    protected Karyawan $dokterB;
    protected JadwalKerjaDetail $detailA;
    protected JadwalKerjaDetail $detailB;
    protected JadwalShift $shiftPagi;
    protected JadwalShift $shiftMalam;
    protected User $wadirUser;
    protected TukarJadwalDokterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TukarJadwalDokterService();

        $ruangan = Ruangan::firstOrCreate(['nama' => 'IGD (Instalasi Gawat Darurat)']);

        // 1. Shift Pagi & Shift Malam
        $this->shiftPagi = JadwalShift::firstOrCreate(
            ['kode' => 'PAGI'],
            ['nama' => 'Shift Pagi', 'jam_masuk' => '07:00:00', 'jam_keluar' => '14:00:00']
        );
        $this->shiftMalam = JadwalShift::firstOrCreate(
            ['kode' => 'MALAM'],
            ['nama' => 'Shift Malam', 'jam_masuk' => '21:00:00', 'jam_keluar' => '07:00:00']
        );

        // 2. Dokter A & Dokter B
        $this->dokterA = Karyawan::create([
            'nip'            => 'DOK-A-001',
            'nik'            => '3374000000000001',
            'nama'           => 'dr. Andi S.',
            'gelar_depan'    => 'dr.',
            'jk'             => 'L',
            'tgl_lahir'      => '1985-01-01',
            'hp'             => '081234567890',
            'prov'           => 'Jateng',
            'kab'            => 'Semarang',
            'kec'            => 'Semarang',
            'desa'           => 'Desa',
            'alamat'         => 'Alamat',
            'agama'          => 'islam',
            'status'         => 'tetap',
            'tgl_masuk'      => '2020-01-01',
            'kategori_kerja' => 'shift',
            'ruangan_id'     => $ruangan->id,
        ]);

        $this->dokterB = Karyawan::create([
            'nip'            => 'DOK-B-002',
            'nik'            => '3374000000000002',
            'nama'           => 'dr. Budi T.',
            'gelar_depan'    => 'dr.',
            'jk'             => 'L',
            'tgl_lahir'      => '1987-02-02',
            'hp'             => '081234567891',
            'prov'           => 'Jateng',
            'kab'            => 'Semarang',
            'kec'            => 'Semarang',
            'desa'           => 'Desa',
            'alamat'         => 'Alamat',
            'agama'          => 'islam',
            'status'         => 'tetap',
            'tgl_masuk'      => '2020-01-01',
            'kategori_kerja' => 'shift',
            'ruangan_id'     => $ruangan->id,
        ]);

        // 3. Jadwal Kerja
        $jadwalKerja = JadwalKerja::firstOrCreate([
            'ruangan_id' => $ruangan->id,
            'bulan'      => 9,
            'tahun'      => 2026,
        ], [
            'status' => \App\Enums\StatusJadwalKerja::PUBLISHED,
        ]);

        // Detail Dokter A: Tanggal 2026-09-10 -> Shift Pagi
        $this->detailA = JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalKerja->id,
            'karyawan_id'     => $this->dokterA->id,
            'tanggal'         => '2026-09-10',
            'shift_id'        => $this->shiftPagi->id,
        ]);

        // Detail Dokter B: Tanggal 2026-09-10 -> Shift Malam
        $this->detailB = JadwalKerjaDetail::create([
            'jadwal_kerja_id' => $jadwalKerja->id,
            'karyawan_id'     => $this->dokterB->id,
            'tanggal'         => '2026-09-10',
            'shift_id'        => $this->shiftMalam->id,
        ]);

        // 4. Wadir Karyawan & User
        $wadirKaryawan = Karyawan::create([
            'nip'            => 'WADIR-001',
            'nik'            => '3374000000000003',
            'nama'           => 'dr. Wadir Test',
            'gelar_depan'    => 'dr.',
            'jk'             => 'L',
            'tgl_lahir'      => '1975-01-01',
            'hp'             => '081234567892',
            'prov'           => 'Jateng',
            'kab'            => 'Semarang',
            'kec'            => 'Semarang',
            'desa'           => 'Desa',
            'alamat'         => 'Alamat',
            'agama'          => 'islam',
            'status'         => 'tetap',
            'tgl_masuk'      => '2010-01-01',
            'kategori_kerja' => 'reguler',
        ]);

        $this->wadirUser = User::create([
            'name'        => 'Wadir Test',
            'email'       => 'wadir_test@rsba.com',
            'password'    => '1234',
            'karyawan_id' => $wadirKaryawan->id,
        ]);
    }

    #[Test]
    public function dokter_a_dapat_mengajukan_tukar_jadwal_ke_dokter_b(): void
    {
        $tukar = $this->service->ajukanTukar(
            $this->dokterA,
            $this->detailA->id,
            $this->dokterB,
            $this->detailB->id,
            'Dinas Luar Kota'
        );

        $this->assertEquals(StatusTukarJadwal::MENUNGGU_KONFIRMASI_DOKTER, $tukar->status);
        $this->assertEquals($this->dokterA->id, $tukar->dokter_pengaju_id);
        $this->assertEquals($this->dokterB->id, $tukar->dokter_pengganti_id);
    }

    #[Test]
    public function dokter_b_dapat_menyetujui_pengajuan_dan_meneruskan_ke_wadir(): void
    {
        $tukar = $this->service->ajukanTukar(
            $this->dokterA,
            $this->detailA->id,
            $this->dokterB,
            $this->detailB->id
        );

        $this->service->konfirmasiDokter($tukar, true);

        $tukar->refresh();
        $this->assertEquals(StatusTukarJadwal::MENUNGGU_WADIR, $tukar->status);
        $this->assertNotNull($tukar->konfirmasi_dokter_at);
    }

    #[Test]
    public function dokter_b_dapat_menolak_pengajuan_tukar_jadwal(): void
    {
        $tukar = $this->service->ajukanTukar(
            $this->dokterA,
            $this->detailA->id,
            $this->dokterB,
            $this->detailB->id
        );

        $this->service->konfirmasiDokter($tukar, false);

        $tukar->refresh();
        $this->assertEquals(StatusTukarJadwal::DITOLAK_DOKTER, $tukar->status);
    }

    #[Test]
    public function wadir_dapat_menyetujui_dan_sistem_otomatis_tukar_shift(): void
    {
        $tukar = $this->service->ajukanTukar(
            $this->dokterA,
            $this->detailA->id,
            $this->dokterB,
            $this->detailB->id
        );

        $this->service->konfirmasiDokter($tukar, true);
        $this->service->approveWadir($tukar, true, $this->wadirUser, 'Disetujui Wadir');

        $tukar->refresh();
        $this->assertEquals(StatusTukarJadwal::DISETUJUI, $tukar->status);
        $this->assertEquals('Disetujui Wadir', $tukar->catatan_wadir);
        $this->assertEquals($this->wadirUser->id, $tukar->disetujui_wadir_oleh);

        // Verifikasi shift_id tertukar secara otomatis di database
        $this->detailA->refresh();
        $this->detailB->refresh();

        $this->assertEquals($this->shiftMalam->id, $this->detailA->shift_id);
        $this->assertEquals($this->shiftPagi->id, $this->detailB->shift_id);
    }

    #[Test]
    public function wadir_dapat_menolak_pengajuan_tukar_jadwal(): void
    {
        $tukar = $this->service->ajukanTukar(
            $this->dokterA,
            $this->detailA->id,
            $this->dokterB,
            $this->detailB->id
        );

        $this->service->konfirmasiDokter($tukar, true);
        $this->service->approveWadir($tukar, false, $this->wadirUser, 'Tolak, kuota shift tidak seimbang');

        $tukar->refresh();
        $this->assertEquals(StatusTukarJadwal::DITOLAK_WADIR, $tukar->status);

        // Verifikasi shift_id TIDAK berubah
        $this->detailA->refresh();
        $this->detailB->refresh();

        $this->assertEquals($this->shiftPagi->id, $this->detailA->shift_id);
        $this->assertEquals($this->shiftMalam->id, $this->detailB->shift_id);
    }
}
