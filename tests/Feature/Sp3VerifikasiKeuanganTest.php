<?php

namespace Tests\Feature;

use App\Enums\StatusApproval;
use App\Enums\TahapApprovalSp3;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\Karyawan;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratSp3Approval;
use App\Models\User;
use App\Services\DocumentSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sp3VerifikasiKeuanganTest extends TestCase
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

    public function test_sp3_created_with_pending_status_and_verifikator_assigned(): void
    {
        $pembuat = $this->createKaryawan('20001', 'Pembuat SP3');
        $verifikator = $this->createKaryawan('20002', 'Verifikator Keuangan');
        $jabatan = Jabatan::forceCreate(['nama' => 'Wadir Keuangan', 'kode_surat' => 'A10']);

        $user = User::forceCreate([
            'email'       => 'pembuat@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $pembuat->id,
        ]);

        $sp3 = SuratSp3::create([
            'no'                      => '1/S4/SP.3/PBA-A10/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Supplier Medis',
            'bayar'                   => 'trf',
            'keterangan'              => 'Pembayaran Alat Kesehatan',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'created_by'              => $user->id,
            'status'                  => StatusApproval::PENDING,
        ]);

        $this->assertEquals(StatusApproval::PENDING, $sp3->status);
        $this->assertEquals($verifikator->id, $sp3->verifikator_keuangan_id);
        $this->assertNull($sp3->verifikasiKeuangan);
        $this->assertNull($sp3->ttdAtasan);
    }

    public function test_sp3_header_qr_not_generated_after_finance_verification_alone(): void
    {
        $pembuat = $this->createKaryawan('20003', 'Pembuat');
        $verifikator = $this->createKaryawan('20004', 'Verifikator');
        $jabatan = Jabatan::forceCreate(['nama' => 'Wadir Keuangan', 'kode_surat' => 'A10']);

        $userVerif = User::forceCreate([
            'email'       => 'verif@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $verifikator->id,
        ]);

        $sp3 = SuratSp3::create([
            'no'                      => '2/S4/SP.3/PBA-A10/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Vendor',
            'bayar'                   => 'tunai',
            'keterangan'              => 'Pengadaan ATK',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'created_by'              => $userVerif->id,
            'status'                  => StatusApproval::PENDING,
        ]);

        // 1. Finance approves verification stage
        SuratSp3Approval::create([
            'surat_sp3_id'   => $sp3->id,
            'tahap'          => TahapApprovalSp3::VERIFIKASI_KEUANGAN,
            'disetujui'      => $userVerif->id,
            'status'         => StatusApproval::APPROVED,
            'signature_hash' => 'sig_verif_hash_123',
            'approved_at'    => now()->toIso8601String(),
        ]);

        $sp3->update(['status' => StatusApproval::WAITING]);

        $service = app(DocumentSignatureService::class);
        $result = $service->checkAndGenerateHeaderQr($sp3->fresh());

        // Must return false because ttd_atasan is still missing
        $this->assertFalse($result);
        $this->assertNull($sp3->fresh()->qr_hash);
        $this->assertEquals(StatusApproval::WAITING, $sp3->fresh()->status);
    }

    public function test_sp3_full_two_tier_approval_generates_system_qr(): void
    {
        $pembuat = $this->createKaryawan('20005', 'Pembuat');
        $verifikator = $this->createKaryawan('20006', 'Verifikator');
        $atasan = $this->createKaryawan('20007', 'Atasan Pejabat');
        $jabatan = Jabatan::forceCreate(['nama' => 'Wadir Keuangan', 'kode_surat' => 'A10']);

        $userVerif = User::forceCreate([
            'email'       => 'verif2@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $verifikator->id,
        ]);

        $userAtasan = User::forceCreate([
            'email'       => 'atasan@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $atasan->id,
        ]);


        $sp3 = SuratSp3::create([
            'no'                      => '3/S4/SP.3/PBA-A10/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Vendor 2',
            'bayar'                   => 'trf',
            'keterangan'              => 'Pengadaan Servis',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'created_by'              => $userVerif->id,
            'status'                  => StatusApproval::WAITING,
        ]);

        // Stage 1: Verifikasi Keuangan
        SuratSp3Approval::create([
            'surat_sp3_id'   => $sp3->id,
            'tahap'          => TahapApprovalSp3::VERIFIKASI_KEUANGAN,
            'disetujui'      => $userVerif->id,
            'status'         => StatusApproval::APPROVED,
            'signature_hash' => 'sig_verif_hash_1',
            'approved_at'    => now()->toIso8601String(),
        ]);

        // Stage 2: Tanda Tangan Atasan
        SuratSp3Approval::create([
            'surat_sp3_id'   => $sp3->id,
            'tahap'          => TahapApprovalSp3::TTD_ATASAN,
            'disetujui'      => $userAtasan->id,
            'status'         => StatusApproval::APPROVED,
            'signature_hash' => 'sig_atasan_hash_2',
            'approved_at'    => now()->toIso8601String(),
        ]);

        $service = app(DocumentSignatureService::class);
        $result = $service->checkAndGenerateHeaderQr($sp3->fresh());

        $this->assertTrue($result);
        $fresh = $sp3->fresh();
        $this->assertEquals(StatusApproval::APPROVED, $fresh->status);
        $this->assertNotNull($fresh->qr_hash);
        $this->assertTrue($fresh->is_valid);
    }

    public function test_cuti_approval_flow_not_affected_by_sp3_two_tier_logic(): void
    {
        $karyawan = $this->createKaryawan('20008', 'Karyawan Cuti Single');

        $surat = SuratCuti::create([
            'karyawan_id' => $karyawan->id,
            'no_surat'    => 'CUTI/SINGLE/001',
            'tgl_surat'   => now()->format('Y-m-d'),
            'tgl_mulai'   => now()->format('Y-m-d'),
            'tgl_akhir'   => now()->addDays(2)->format('Y-m-d'),
            'tgl_cuti'    => json_encode([now()->format('Y-m-d')]),
            'lama_cuti'   => 1,
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

        // SuratCuti should complete in 1 tier
        $this->assertTrue($result);
        $this->assertEquals(StatusApproval::APPROVED, $surat->fresh()->status);
        $this->assertNotNull($surat->fresh()->qr_hash);
    }

    public function test_sp3_verifikasi_keuangan_rejection_flow(): void
    {
        $pembuat = $this->createKaryawan('20009', 'Pembuat Reject');
        $verifikator = $this->createKaryawan('20010', 'Verifikator Reject');
        $jabatan = Jabatan::forceCreate(['nama' => 'Wadir Keuangan', 'kode_surat' => 'A10']);

        $userVerif = User::forceCreate([
            'email'       => 'verif_reject@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $verifikator->id,
        ]);

        $sp3 = SuratSp3::create([
            'no'                      => '2/S4/SP.3/PBA-A10/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Supplier Reject',
            'bayar'                   => 'trf',
            'keterangan'              => 'SP3 yang ditolak',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'status'                  => StatusApproval::PENDING,
            'created_by'              => $userVerif->id,
        ]);



        $this->actingAs($userVerif);

        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\VerifikasiKeuangan::class, ['suratSp3' => $sp3])
            ->set('status', 'rejected')
            ->set('keterangan', 'Nominal tidak sesuai kwitansi')
            ->call('submit');

        $this->assertEquals(StatusApproval::REJECTED, $sp3->fresh()->status);
        $approval = SuratSp3Approval::where('surat_sp3_id', $sp3->id)
            ->where('tahap', TahapApprovalSp3::VERIFIKASI_KEUANGAN->value)
            ->first();

        $this->assertNotNull($approval);
        $this->assertEquals(StatusApproval::REJECTED, $approval->status);
        $this->assertNotNull($approval->signature_hash);
    }

    public function test_sp3_edit_and_resubmit_flow(): void
    {
        $pembuat = $this->createKaryawan('20011', 'Pembuat Edit');
        $verifikator = $this->createKaryawan('20012', 'Verifikator Edit');
        $jabatan = Jabatan::forceCreate(['nama' => 'Wadir Keuangan', 'kode_surat' => 'A10']);

        $userPembuat = User::forceCreate([
            'email'       => 'pembuat_edit@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $pembuat->id,
        ]);

        $sp3 = SuratSp3::create([
            'no'                      => '3/S4/SP.3/PBA-A10/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Supplier Awal',
            'bayar'                   => 'trf',
            'keterangan'              => 'SP3 yang mau diedit',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'status'                  => StatusApproval::REJECTED,
            'created_by'              => $userPembuat->id,
        ]);

        \App\Models\Surat\SuratSp3Detail::create([
            'sp3_id'     => $sp3->id,
            'nominal'    => 100000,
            'keterangan' => 'Item Awal',
        ]);

        SuratSp3Approval::create([
            'surat_sp3_id'   => $sp3->id,
            'tahap'          => TahapApprovalSp3::VERIFIKASI_KEUANGAN->value,
            'disetujui'      => $userPembuat->id,
            'status'         => StatusApproval::REJECTED,
            'keterangan'     => 'Ditolak untuk revisi',
            'signature_hash' => 'REJECTED_TEST',
            'approved_at'    => now()->toIso8601String(),
        ]);

        $this->actingAs($userPembuat);

        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\Edit::class, ['suratSp3' => $sp3])
            ->set('rekanan', 'PT Supplier Baru')
            ->set('keterangan', 'SP3 sudah diperbaiki')
            ->set('listSp3', [
                ['nominal' => '250.000', 'keterangan' => 'Item Revisi 1'],
                ['nominal' => '500.000', 'keterangan' => 'Item Revisi 2'],
            ])
            ->call('submit');

        $fresh = $sp3->fresh();
        $this->assertEquals(StatusApproval::PENDING, $fresh->status);
        $this->assertEquals('PT Supplier Baru', $fresh->rekanan);
        $this->assertEquals('SP3 sudah diperbaiki', $fresh->keterangan);
        $this->assertEquals(2, $fresh->details()->count());
        $this->assertEquals(750000, $fresh->details()->sum('nominal'));
        $this->assertEquals(0, $fresh->approvals()->count());
    }

    public function test_direktur_cannot_acc_when_sp3_not_yet_verified_by_finance(): void
    {
        $pembuat = $this->createKaryawan('20013', 'Pembuat Belum Verif');
        $verifikator = $this->createKaryawan('20014', 'Verifikator Belum Verif');
        $atasan = $this->createKaryawan('20015', 'Direktur Belum Verif');
        $jabatan = Jabatan::forceCreate(['nama' => 'Direktur Utama', 'kode_surat' => 'DIR']);

        $userDirektur = User::forceCreate([
            'email'       => 'direktur_test@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $atasan->id,
        ]);

        $sp3 = SuratSp3::create([
            'no'                      => '4/S4/SP.3/PBA-DIR/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Vendor Pending',
            'bayar'                   => 'trf',
            'keterangan'              => 'SP3 masih pending finance',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'status'                  => StatusApproval::PENDING,
            'created_by'              => $userDirektur->id,
        ]);

        $this->actingAs($userDirektur);

        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\Approval::class, ['suratSp3' => $sp3])
            ->set('status', 'approved')
            ->call('submit');

        // Status must remain PENDING because finance verification has not approved it yet
        $this->assertEquals(StatusApproval::PENDING, $sp3->fresh()->status);
        $this->assertNull($sp3->fresh()->ttdAtasan);
    }

    public function test_sp3_approval_logs_history_records_all_stages(): void
    {
        $pembuat = $this->createKaryawan('20016', 'Pembuat Log');
        $verifikator = $this->createKaryawan('20017', 'Verifikator Log');
        $atasan = $this->createKaryawan('20018', 'Direktur Log');
        $jabatan = Jabatan::forceCreate(['nama' => 'Direktur Utama', 'kode_surat' => 'DIR']);

        $userPembuat = User::forceCreate([
            'email'       => 'pembuat_log@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $pembuat->id,
        ]);

        $userVerif = User::forceCreate([
            'email'       => 'verif_log@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $verifikator->id,
        ]);

        $userDirektur = User::forceCreate([
            'email'       => 'direktur_log@test.com',
            'password'    => bcrypt('password'),
            'karyawan_id' => $atasan->id,
        ]);

        $this->actingAs($userPembuat);

        // 1. Pembuatan SP3
        $sp3 = SuratSp3::create([
            'no'                      => '5/S4/SP.3/PBA-DIR/14.08.2026',
            'tahun'                   => 2026,
            'tgl'                     => '2026-08-14',
            'rekanan'                 => 'PT Log Test',
            'bayar'                   => 'trf',
            'keterangan'              => 'SP3 untuk tes riwayat log',
            'jabatan_id'              => $jabatan->id,
            'verifikator_keuangan_id' => $verifikator->id,
            'status'                  => StatusApproval::PENDING,
            'created_by'              => $userPembuat->id,
        ]);

        \App\Models\Surat\SuratSp3Log::create([
            'surat_sp3_id'   => $sp3->id,
            'user_id'        => $userPembuat->id,
            'nama_pelaku'    => $pembuat->nama,
            'jabatan_pelaku' => 'Staf',
            'aksi'           => 'Dibuat',
            'status'         => 'pending',
            'catatan'        => 'Surat SP3 dibuat dan diteruskan ke Bagian Keuangan.',
        ]);

        // 2. Verifikasi Keuangan Tolak
        $this->actingAs($userVerif);
        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\VerifikasiKeuangan::class, ['suratSp3' => $sp3])
            ->set('status', 'rejected')
            ->set('keterangan', 'Kuitansi belum lengkap')
            ->call('submit');

        $this->assertEquals(StatusApproval::REJECTED, $sp3->fresh()->status);

        // 3. Pembuat Edit & Ajukan Ulang
        $this->actingAs($userPembuat);
        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\Edit::class, ['suratSp3' => $sp3->fresh()])
            ->set('rekanan', 'PT Log Test Revisi')
            ->set('listSp3', [['nominal' => '100.000', 'keterangan' => 'Item revisi']])
            ->call('submit');

        $this->assertEquals(StatusApproval::PENDING, $sp3->fresh()->status);

        // 4. Verifikasi Keuangan Setujui
        $this->actingAs($userVerif);
        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\VerifikasiKeuangan::class, ['suratSp3' => $sp3->fresh()])
            ->set('status', 'approved')
            ->set('keterangan', 'Dokumen sudah lengkap')
            ->call('submit');

        $this->assertEquals(StatusApproval::WAITING, $sp3->fresh()->status);

        // 5. Direktur ACC Setujui
        $this->actingAs($userDirektur);
        \Livewire\Livewire::test(\App\Livewire\Surat\Sp3\Approval::class, ['suratSp3' => $sp3->fresh()])
            ->set('status', 'approved')
            ->call('submit');

        $this->assertEquals(StatusApproval::APPROVED, $sp3->fresh()->status);

        // Verifikasi seluruh riwayat log tercatat secara kronologis
        $logs = $sp3->fresh()->logs;
        $this->assertCount(5, $logs);
        $this->assertEquals('Dibuat', $logs[0]->aksi);
        $this->assertEquals('Verifikasi Keuangan - Ditolak', $logs[1]->aksi);
        $this->assertEquals('Diedit & Diajukan Ulang', $logs[2]->aksi);
        $this->assertEquals('Verifikasi Keuangan - Disetujui', $logs[3]->aksi);
        $this->assertEquals('ACC Direktur / Atasan - Disetujui', $logs[4]->aksi);
    }
}




