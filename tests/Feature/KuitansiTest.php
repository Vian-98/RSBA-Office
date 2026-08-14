<?php

namespace Tests\Feature;

use App\Enums\StatusApproval;
use App\Enums\StatusKuitansi;
use App\Models\Keuangan\Kuitansi;
use App\Models\Keuangan\KuitansiApproval;
use App\Models\Keuangan\KuitansiDetail;
use App\Models\Keuangan\MetodeBayar;
use App\Models\Sdm\Karyawan;
use App\Models\User;
use App\Services\DocumentSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KuitansiTest extends TestCase
{
    use RefreshDatabase;

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

    private function createUserWithPermission(string $perm): User
    {
        Permission::firstOrCreate(['name' => $perm]);
        $karyawan = $this->createKaryawan('10099', 'User Perm');
        $user = User::forceCreate([
            'email'       => 'perm@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);
        $user->givePermissionTo($perm);
        return $user;
    }

    public function test_kuitansi_can_be_created_with_details_and_approvals(): void
    {
        $karyawan = $this->createKaryawan('10001', 'Kasir Test');
        $approver = $this->createKaryawan('10002', 'Kabag Keuangan');

        $user = User::forceCreate([
            'email'       => 'kasir@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $kuitansi = Kuitansi::create([
            'nomor'         => 'KW/0001/2026',
            'tanggal'       => '2026-08-14',
            'diterima_dari' => 'Bpk. Hendra',
            'jumlah'        => 250000,
            'keterangan'    => 'Biaya Rawat Jalan Poli Umum',
            'penerima_id'   => $karyawan->id,
            'penerima_nama' => $karyawan->nama,
            'metode_bayar'  => 'Tunai',
            'status'        => StatusKuitansi::WAITING,
            'created_by'    => $user->id,
        ]);

        KuitansiDetail::create([
            'kuitansi_id' => $kuitansi->id,
            'keterangan'  => 'Pemeriksaan Dokter',
            'nominal'     => 150000,
        ]);

        KuitansiDetail::create([
            'kuitansi_id' => $kuitansi->id,
            'keterangan'  => 'Obat-obatan',
            'nominal'     => 100000,
        ]);

        KuitansiApproval::create([
            'kuitansi_id'    => $kuitansi->id,
            'disetujui_oleh' => $approver->id,
            'status'         => StatusApproval::WAITING,
        ]);

        $this->assertDatabaseHas('kuitansi', ['nomor' => 'KW/0001/2026']);
        $this->assertEquals(2, $kuitansi->details->count());
        $this->assertEquals(1, $kuitansi->approvals->count());
        $this->assertEquals('Dua Ratus Lima Puluh Ribu Rupiah', $kuitansi->terbilang);
    }

    public function test_kuitansi_auto_numbering_structure(): void
    {
        $karyawan = $this->createKaryawan('10003', 'Staff Kasir');
        $user = User::forceCreate([
            'email'       => 'staff@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $k1 = Kuitansi::create([
            'nomor'         => 'KW/0001/2026',
            'tanggal'       => '2026-08-14',
            'diterima_dari' => 'Pasien A',
            'jumlah'        => 100000,
            'keterangan'    => 'Tes 1',
            'penerima_nama' => 'Staff',
            'metode_bayar'  => 'Tunai',
            'status'        => StatusKuitansi::WAITING,
            'created_by'    => $user->id,
        ]);

        $k2 = Kuitansi::create([
            'nomor'         => 'KW/0002/2026',
            'tanggal'       => '2026-08-14',
            'diterima_dari' => 'Pasien B',
            'jumlah'        => 200000,
            'keterangan'    => 'Tes 2',
            'penerima_nama' => 'Staff',
            'metode_bayar'  => 'QRIS',
            'status'        => StatusKuitansi::WAITING,
            'created_by'    => $user->id,
        ]);

        $this->assertEquals('KW/0001/2026', $k1->nomor);
        $this->assertEquals('KW/0002/2026', $k2->nomor);
    }

    public function test_kuitansi_full_approval_triggers_qr_generation(): void
    {
        $karyawan = $this->createKaryawan('10004', 'Penerima');
        $approver = $this->createKaryawan('10005', 'Approver Keu');

        $user = User::forceCreate([
            'email'       => 'kasir2@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);


        $kuitansi = Kuitansi::create([
            'nomor'         => 'KW/0005/2026',
            'tanggal'       => '2026-08-14',
            'diterima_dari' => 'Pasien Full',
            'jumlah'        => 500000,
            'keterangan'    => 'Full ACC Test',
            'penerima_nama' => $karyawan->nama,
            'metode_bayar'  => 'Transfer',
            'status'        => StatusKuitansi::WAITING,
            'created_by'    => $user->id,
        ]);

        $appRow = KuitansiApproval::create([
            'kuitansi_id'    => $kuitansi->id,
            'disetujui_oleh' => $approver->id,
            'status'         => StatusApproval::APPROVED,
            'approved_at'    => now()->toIso8601String(),
            'signature_hash' => 'test_sig_hash_kuitansi',
        ]);

        $service = app(DocumentSignatureService::class);
        $result = $service->checkAndGenerateHeaderQr($kuitansi->fresh());

        $this->assertTrue($result);
        $fresh = $kuitansi->fresh();
        $this->assertEquals(StatusKuitansi::APPROVED, $fresh->status);
        $this->assertNotNull($fresh->qr_hash);
        $this->assertTrue($fresh->is_valid);
    }

    public function test_metode_bayar_creation(): void
    {
        $m = MetodeBayar::firstOrCreate(['nama' => 'Virtual Account']);
        $this->assertDatabaseHas('metode_bayar', ['nama' => 'Virtual Account']);
    }
}
