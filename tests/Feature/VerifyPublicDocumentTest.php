<?php

namespace Tests\Feature;

use App\Enums\StatusApproval;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Models\User;
use App\Services\DocumentSignatureService;
use App\Services\SystemCertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyPublicDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function createKaryawan(string $nip, string $nama): Karyawan
    {
        return Karyawan::forceCreate([
            'nip' => $nip,
            'nik' => '1234567890' . substr($nip, -4),
            'nama' => $nama,
            'tgl_lahir' => '1990-01-01',
            'hp' => '08123456789',
            'prov' => 'Lampung',
            'kab' => 'Bandar Lampung',
            'kec' => 'Kedaton',
            'desa' => 'Penengahan',
            'alamat' => 'Jl. Test',
            'agama' => 'islam',
            'tgl_masuk' => '2020-01-01',
        ]);
    }

    public function test_system_certificate_can_be_retrieved_or_created()
    {
        $service = app(SystemCertificateService::class);
        $systemUser = $service->getOrCreateSystemUser();

        $this->assertNotNull($systemUser);
        $this->assertDatabaseHas('users', ['id' => $systemUser->id]);
    }

    public function test_qr_hash_is_not_generated_if_approval_is_partial()
    {
        $karyawan = $this->createKaryawan('10001', 'Karyawan Test 1');
        $user = User::forceCreate([
            'email' => 'approver2@test.com',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
        ]);

        $surat = SuratCuti::create([
            'karyawan_id' => $karyawan->id,
            'no_surat'    => 'CUTI/001/TEST',
            'tgl_surat'   => now()->format('Y-m-d'),
            'tgl_mulai'   => now()->format('Y-m-d'),
            'tgl_akhir'   => now()->addDays(2)->format('Y-m-d'),
            'tgl_cuti'    => json_encode([now()->format('Y-m-d')]),
            'lama_cuti'   => 2,
            'status'      => StatusApproval::PENDING,
        ]);

        SuratCutiApproval::create([
            'surat_cuti_id'  => $surat->id,
            'disetujui_oleh' => $karyawan->id,
            'status'         => StatusApproval::APPROVED,
        ]);

        SuratCutiApproval::create([
            'surat_cuti_id'  => $surat->id,
            'disetujui_oleh' => $user->id,
            'status'         => StatusApproval::WAITING,
        ]);

        $service = app(DocumentSignatureService::class);
        $result = $service->checkAndGenerateHeaderQr($surat->fresh());

        $this->assertFalse($result);
        $this->assertNull($surat->fresh()->qr_hash);
    }

    public function test_qr_hash_is_automatically_generated_when_full_acc_achieved()
    {
        $karyawan = $this->createKaryawan('10002', 'Karyawan Test Full');

        $surat = SuratCuti::create([
            'karyawan_id' => $karyawan->id,
            'no_surat'    => 'CUTI/002/FULL',
            'tgl_surat'   => now()->format('Y-m-d'),
            'tgl_mulai'   => now()->format('Y-m-d'),
            'tgl_akhir'   => now()->addDays(2)->format('Y-m-d'),
            'tgl_cuti'    => json_encode([now()->format('Y-m-d')]),
            'lama_cuti'   => 2,
            'status'      => StatusApproval::PENDING,
        ]);

        SuratCutiApproval::create([
            'surat_cuti_id'  => $surat->id,
            'disetujui_oleh' => $karyawan->id,
            'status'         => StatusApproval::APPROVED,
            'approved_at'    => now(),
        ]);

        $service = app(DocumentSignatureService::class);
        $result = $service->checkAndGenerateHeaderQr($surat->fresh());

        $this->assertTrue($result);
        $suratFresh = $surat->fresh();

        $this->assertNotNull($suratFresh->qr_hash);
        $this->assertEquals(StatusApproval::APPROVED, $suratFresh->status);
        $this->assertTrue((bool)$suratFresh->is_valid);
    }

    public function test_public_verification_endpoint_renders_successfully_for_valid_hash()
    {
        $karyawan = $this->createKaryawan('10003', 'Budi Dokter Test');

        $surat = SuratCuti::create([
            'karyawan_id' => $karyawan->id,
            'no_surat'    => 'CUTI/003/VERIFY',
            'tgl_surat'   => now()->format('Y-m-d'),
            'tgl_mulai'   => now()->format('Y-m-d'),
            'tgl_akhir'   => now()->addDays(2)->format('Y-m-d'),
            'tgl_cuti'    => json_encode([now()->format('Y-m-d')]),
            'lama_cuti'   => 2,
            'status'      => StatusApproval::APPROVED,
            'qr_hash'     => 'test_system_hash_1234567890abcdef',
            'signed_at'   => now(),
            'is_valid'    => true,
        ]);

        $response = $this->get('/verifikasi-surat/test_system_hash_1234567890abcdef');

        $response->assertStatus(200);
        $response->assertSee('DOKUMEN RESMI');
        $response->assertSee('Budi Dokter Test');
        $response->assertSee('CUTI/003/VERIFY');
    }

    public function test_public_verification_endpoint_resolves_fallback_calculated_hash()
    {
        $karyawan = $this->createKaryawan('10004', 'Siti Test Fallback');

        $surat = SuratCuti::create([
            'karyawan_id' => $karyawan->id,
            'no_surat'    => 'CUTI/004/FALLBACK',
            'tgl_surat'   => now()->format('Y-m-d'),
            'tgl_mulai'   => now()->format('Y-m-d'),
            'tgl_akhir'   => now()->addDays(2)->format('Y-m-d'),
            'tgl_cuti'    => json_encode([now()->format('Y-m-d')]),
            'lama_cuti'   => 2,
            'status'      => StatusApproval::APPROVED,
            'qr_hash'     => null,
        ]);

        $fallbackHash = hash('sha256', 'cuti-' . $surat->id . '-' . config('app.key'));
        $response = $this->get('/verifikasi-surat/' . $fallbackHash);

        $response->assertStatus(200);
        $response->assertSee('DOKUMEN RESMI');
        $response->assertSee('Siti Test Fallback');
        $this->assertEquals($fallbackHash, $surat->fresh()->qr_hash);
    }
}
