<?php

namespace Tests\Feature;

use App\Enums\StatusApproval;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Models\Surat\SuratBalasanPenelitianBiaya;
use App\Models\Surat\SuratBalasanPenelitianMahasiswa;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Surat\SuratBalasanPklMahasiswa;
use App\Models\Surat\SuratPerintahTugas;
use App\Models\Surat\SuratPerintahTugasKaryawan;
use App\Models\Surat\SuratTarifPkl;
use App\Models\Surat\SuratTemplateNomor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratBaruTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Karyawan $direkturKaryawan;
    protected Jabatan $direkturJabatan;

    private function createKaryawan(string $nip, string $nama): Karyawan
    {
        return Karyawan::forceCreate([
            'nip'         => $nip,
            'nik'         => '1234567890' . substr($nip, -4),
            'nama'        => $nama,
            'tgl_lahir'   => '1990-01-01',
            'hp'          => '08123456789',
            'prov'        => 'Lampung',
            'kab'         => 'Bandar Lampung',
            'kec'         => 'Kedaton',
            'desa'        => 'Penengahan',
            'alamat'      => 'Jl. Test',
            'agama'       => 'islam',
            'tgl_masuk'   => '2020-01-01',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $pembuatKaryawan = $this->createKaryawan('10001', 'Test Pembuat');

        $this->user = User::forceCreate([
            'email'       => 'testuser_' . uniqid() . '@rsba.test',
            'password'    => bcrypt('password'),
            'karyawan_id' => $pembuatKaryawan->id,
        ]);

        $this->direkturJabatan = Jabatan::firstOrCreate(
            ['nama' => 'Direktur'],
            ['tingkat_id' => 1, 'kode_surat' => 'DIR']
        );

        $this->direkturKaryawan = $this->createKaryawan('24170002', 'dr. Rachmawati, MPH');
    }


    public function test_surat_balasan_pkl_creation_and_snapshot_tariff()
    {
        $this->actingAs($this->user);

        // Pastikan ada tarif aktif
        $tarif = SuratTarifPkl::getAktif();

        $surat = SuratBalasanPkl::create([
            'no'                   => '1/S4/B-PKL/PBA-DIR/14.08.2026',
            'tahun'                => 2026,
            'tgl'                  => '2026-08-14',
            'tujuan_universitas'   => 'Universitas Malahayati',
            'prodi'                => 'Ilmu Keperawatan',
            'jumlah_mahasiswa'     => 2,
            'lama_praktik_bulan'   => 3,
            'tgl_mulai'            => '2026-09-01',
            'tgl_selesai'          => '2026-11-30',
            'snap_biaya_praktik'   => $tarif->biaya_praktik_per_bulan,
            'snap_biaya_orientasi' => $tarif->biaya_orientasi_per_orang,
            'snap_nomor_sk'        => $tarif->nomor_sk,
            'jabatan_id'           => $this->direkturJabatan->id,
            'disetujui_oleh'       => $this->direkturKaryawan->id,
            'status'               => StatusApproval::PENDING,
            'created_by'           => $this->user->id,
        ]);

        SuratBalasanPklMahasiswa::create([
            'surat_balasan_pkl_id' => $surat->id,
            'nama'                 => 'Ahmad Fadhil',
            'npm'                  => '202601001',
        ]);

        SuratBalasanPklMahasiswa::create([
            'surat_balasan_pkl_id' => $surat->id,
            'nama'                 => 'Siti Rahma',
            'npm'                  => '202601002',
        ]);

        $this->assertDatabaseHas('surat_balasan_pkl', [
            'id' => $surat->id,
            'tujuan_universitas' => 'Universitas Malahayati',
            'status' => 'pending',
        ]);

        $this->assertEquals(2, $surat->mahasiswa()->count());

        // Hitung total biaya: (150.000 x 2 mhs x 3 bln) + (50.000 x 2 mhs) = 900.000 + 100.000 = 1.000.000
        $expectedTotal = ($tarif->biaya_praktik_per_bulan * 2 * 3) + ($tarif->biaya_orientasi_per_orang * 2);
        $this->assertEquals($expectedTotal, $surat->grand_total_biaya);
    }

    public function test_surat_balasan_penelitian_creation_with_cost_items()
    {
        $this->actingAs($this->user);

        $surat = SuratBalasanPenelitian::create([
            'no'                  => '1/S4/B-PNL/PBA-DIR/14.08.2026',
            'tahun'               => 2026,
            'tgl'                 => '2026-08-14',
            'tujuan_fakultas'     => 'Fakultas Kedokteran',
            'tujuan_universitas'  => 'Universitas Lampung',
            'perihal_surat_masuk' => 'Izin Penelitian Skripsi',
            'jabatan_id'          => $this->direkturJabatan->id,
            'disetujui_oleh'      => $this->direkturKaryawan->id,
            'status'              => StatusApproval::PENDING,
            'created_by'          => $this->user->id,
        ]);

        SuratBalasanPenelitianMahasiswa::create([
            'surat_balasan_penelitian_id' => $surat->id,
            'nama'                        => 'Rian Pratama',
            'npm'                         => '2217001',
            'fakultas_pt'                 => 'FK Unila',
            'judul_penelitian'            => 'Analisis Efektivitas Pelayanan Farmasi RSBA',
        ]);

        SuratBalasanPenelitianBiaya::create([
            'surat_balasan_penelitian_id' => $surat->id,
            'keterangan'                  => 'Penelitian Skripsi',
            'jumlah_orang'                => 1,
            'jasa_sarana'                 => 100000,
            'jasa_pelayanan'              => 150000,
        ]);

        $this->assertDatabaseHas('surat_balasan_penelitian', [
            'id' => $surat->id,
            'tujuan_fakultas' => 'Fakultas Kedokteran',
        ]);

        $this->assertEquals(250000, $surat->total_biaya);
    }

    public function test_surat_perintah_tugas_creation_with_multi_karyawan()
    {
        $this->actingAs($this->user);

        $karyawan1 = $this->createKaryawan('90001', 'Staff Medis 1');
        $karyawan2 = $this->createKaryawan('90002', 'Staff Medis 2');

        $surat = SuratPerintahTugas::create([
            'no'             => '1/S4/SPT/PBA-DIR/14.08.2026',
            'tahun'          => 2026,
            'tgl'            => '2026-08-14',
            'perihal'        => 'Mengikuti Pelatihan Workshop Penanganan Pasien Kritis',
            'hari_tanggal'   => 'Senin / 18 Agustus 2026',
            'waktu'          => '08:00 WIB s.d Selesai',
            'tempat'         => 'Aula Utama RSBA',
            'jabatan_id'     => $this->direkturJabatan->id,
            'disetujui_oleh' => $this->direkturKaryawan->id,
            'status'         => StatusApproval::PENDING,
            'created_by'     => $this->user->id,
        ]);

        SuratPerintahTugasKaryawan::create([
            'surat_perintah_tugas_id' => $surat->id,
            'karyawan_id'             => $karyawan1->id,
        ]);

        SuratPerintahTugasKaryawan::create([
            'surat_perintah_tugas_id' => $surat->id,
            'karyawan_id'             => $karyawan2->id,
        ]);

        $this->assertDatabaseHas('surat_perintah_tugas', [
            'id' => $surat->id,
            'tempat' => 'Aula Utama RSBA',
        ]);

        $this->assertEquals(2, $surat->karyawanTugas()->count());
    }

    public function test_dynamic_numbering_template_generation()
    {
        $nomorPkl = SuratTemplateNomor::generateNomor(
            'balasan_pkl',
            $this->direkturJabatan->id,
            '2026-08-14',
            5
        );
        $this->assertEquals('5/S4/B-PKL/PBA-DIR/14.08.2026', $nomorPkl);

        $nomorPnl = SuratTemplateNomor::generateNomor(
            'balasan_penelitian',
            $this->direkturJabatan->id,
            '2026-08-14',
            12
        );
        $this->assertEquals('12/S4/B-PNL/PBA-DIR/14.08.2026', $nomorPnl);

        $nomorSpt = SuratTemplateNomor::generateNomor(
            'perintah_tugas',
            $this->direkturJabatan->id,
            '2026-08-14',
            8
        );
        $this->assertEquals('8/S4/SPT/PBA-DIR/14.08.2026', $nomorSpt);
    }
}
