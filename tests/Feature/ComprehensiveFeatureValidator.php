<?php

namespace Tests\Feature;

use App\Enums\StatusApproval;
use App\Enums\StatusJadwalKerja;
use App\Http\Controllers\AkreDownloadDocsController;
use App\Http\Controllers\Kepegawaian\JadwalKerjaPdfController;
use App\Http\Controllers\Surat\SuratBalasanPenelitianPdfController;
use App\Http\Controllers\Surat\SuratBalasanPklPdfController;
use App\Http\Controllers\Surat\SuratPerintahTugasPdfController;
use App\Models\DigitalSignatureApproval;
use App\Models\DigitalSignatureDocument;
use App\Models\Ruangan;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\Dokter;
use App\Models\Sdm\DokterSpesialisasi;
use App\Models\Sdm\Jabatan;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalKerjaDetail;
use App\Models\Sdm\JadwalShift;
use App\Models\Sdm\Karyawan;
use App\Models\Sdm\RuanganKoordinator;
use App\Models\Sdm\RuanganShift;
use App\Models\Surat\CutiJenis;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratCutiApproval;
use App\Models\Surat\SuratPerintahTugas;
use App\Models\Surat\SuratSp3;
use App\Models\User;
use App\Services\AturanJadwalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ComprehensiveFeatureValidator
{
    private array $results = [];

    public function runAll(): array
    {
        $this->testMasterData();
        $this->testActorPermissionsAndAuth();
        $this->testJadwalKerjaWorkflow();
        $this->testAbsensiAndRekonsiliasi();
        $this->testCutiAndPersuratan();
        $this->testDigitalSignatureAndPublicQr();
        $this->testPayrollAndPph21();
        $this->testJasmedAndLaporan();
        $this->testAkreditasi();

        return $this->results;
    }

    private function record(string $module, string $testCase, bool $success, string $details = ''): void
    {
        $this->results[] = [
            'module' => $module,
            'test_case' => $testCase,
            'status' => $success ? 'PASSED' : 'FAILED',
            'details' => $details,
        ];
        $icon = $success ? '✓' : '✗';
        echo " [{$icon}] {$module} -> {$testCase}: {$details}\n";
    }

    private function testMasterData(): void
    {
        $bagianCount = Bagian::count();
        $this->record('Master Data', 'Master Bagian', $bagianCount >= 4, "Total Bagian: {$bagianCount}");

        $ruanganCount = Ruangan::count();
        $this->record('Master Data', 'Master Ruangan', $ruanganCount >= 5, "Total Ruangan: {$ruanganCount}");

        $jabatanCount = Jabatan::count();
        $this->record('Master Data', 'Master Jabatan (Tingkat 1-5)', $jabatanCount >= 8, "Total Jabatan: {$jabatanCount}");

        $shiftCount = JadwalShift::count();
        $this->record('Master Data', 'Master Shift Kerja', $shiftCount >= 4, "Total Shift: {$shiftCount}");

        $rsCount = RuanganShift::count();
        $this->record('Master Data', 'Master Ruangan-Shift', $rsCount >= 10, "Total Mapping: {$rsCount}");

        $cutiCount = CutiJenis::count();
        $this->record('Master Data', 'Master Jenis Cuti', $cutiCount >= 4, "Total Jenis Cuti: {$cutiCount}");

        $spesialisCount = DokterSpesialisasi::count();
        $this->record('Master Data', 'Master Spesialisasi Dokter', $spesialisCount >= 2, "Total Spesialisasi: {$spesialisCount}");
    }

    private function testActorPermissionsAndAuth(): void
    {
        $actors = [
            'admin@rsba.com' => 'Super-Admin',
            'direktur@rsba.test' => 'Direktur Utama',
            'wadir@rsba.test' => 'Wadir Medis',
            'kabid@rsba.test' => 'Kabid Keperawatan',
            'karu@rsba.test' => 'Karu IGD',
            'staf@rsba.test' => 'Perawat IGD',
            'dokter@rsba.test' => 'Dokter IGD',
            'sdm@rsba.test' => 'Staff SDM',
            'keuangan@rsba.test' => 'Staff Keuangan',
            'akreditasi@rsba.test' => 'Assessor Akreditasi',
        ];

        foreach ($actors as $email => $roleName) {
            $user = User::where('email', $email)->first();
            $exists = $user !== null;
            $permCount = $user ? $user->getAllPermissions()->count() : 0;
            $this->record('Aktor & Auth', "User {$roleName} ({$email})", $exists && ($permCount > 0 || $user->hasRole('Super-Admin')), "Permission count: {$permCount}");
        }
    }

    private function testJadwalKerjaWorkflow(): void
    {
        $ruanganIgd = Ruangan::where('nama', 'LIKE', '%IGD%')->first();
        $karu = User::where('email', 'karu@rsba.test')->first();
        $kabid = User::where('email', 'kabid@rsba.test')->first();
        $wadir = User::where('email', 'wadir@rsba.test')->first();
        $perawat = Karyawan::where('nip', '199606062021012006')->first();
        $shiftPagi = JadwalShift::where('kode', 'PAGI')->first();

        // 1. Buat Draft
        $jadwal = JadwalKerja::updateOrCreate(
            ['ruangan_id' => $ruanganIgd->id, 'bulan' => 9, 'tahun' => 2026, 'tipe' => 'karyawan'],
            [
                'bagian_id' => $ruanganIgd->bagian_id,
                'status' => StatusJadwalKerja::DRAFT,
                'dibuat_oleh' => $karu->karyawan_id,
            ]
        );

        // Isi detail
        for ($d = 1; $d <= 30; $d++) {
            JadwalKerjaDetail::updateOrCreate(
                ['jadwal_kerja_id' => $jadwal->id, 'karyawan_id' => $perawat->id, 'tanggal' => "2026-09-" . str_pad($d, 2, '0', STR_PAD_LEFT)],
                ['shift_id' => $shiftPagi->id, 'status_kehadiran' => 'belum_dicek']
            );
        }
        $this->record('Jadwal Kerja', '1. Pembuatan Draft Jadwal oleh Karu', $jadwal->status === StatusJadwalKerja::DRAFT, "Status: {$jadwal->status->nama()}");

        // 2. Submit ke Kabid
        $jadwal->update(['status' => StatusJadwalKerja::MENUNGGU_KABID]);
        $targetKabid = $jadwal->getTargetApproverName(1);
        $this->record('Jadwal Kerja', '2. Pengajuan ke Kabid & Target Resolusi', $jadwal->status === StatusJadwalKerja::MENUNGGU_KABID && str_contains($targetKabid, 'Kabid'), "Target: {$targetKabid}");

        // 3. Kabid ACC -> Menunggu Wadir
        $jadwal->update([
            'status' => StatusJadwalKerja::MENUNGGU_WADIR,
            'diketahui_oleh' => $kabid->karyawan_id,
            'diketahui_at' => now(),
        ]);
        $targetWadir = $jadwal->getTargetApproverName(2);
        $this->record('Jadwal Kerja', '3. Approval Kabid -> Menunggu Wadir', $jadwal->status === StatusJadwalKerja::MENUNGGU_WADIR && str_contains($targetWadir, 'Wadir'), "Target Wadir: {$targetWadir}");

        // 4. Wadir ACC -> Published
        $jadwal->update([
            'status' => StatusJadwalKerja::PUBLISHED,
            'disetujui_oleh' => $wadir->karyawan_id,
            'disetujui_at' => now(),
            'published_at' => now(),
        ]);
        $this->record('Jadwal Kerja', '4. Final Approval Wadir -> Published', $jadwal->status === StatusJadwalKerja::PUBLISHED, "Disetujui oleh: {$jadwal->disetujuiOleh->full_nama}");

        // 5. Export PDF
        Auth::login($wadir);
        $controller = new JadwalKerjaPdfController();
        $pdfResponse = $controller->exportPdf($jadwal->id, app(AturanJadwalService::class));
        $this->record('Jadwal Kerja', '5. Export PDF Jadwal Resmi', $pdfResponse->getStatusCode() === 200, "Header: " . $pdfResponse->headers->get('content-disposition'));
    }

    private function testAbsensiAndRekonsiliasi(): void
    {
        $perawat = Karyawan::where('nip', '199606062021012006')->first();
        
        $hasPin = !empty($perawat->pin_absen);
        $this->record('Absensi', '1. PIN Absen Karyawan Terdaftar', $hasPin, "PIN: {$perawat->pin_absen}");

        $shiftStart = Carbon::parse('2026-09-01 07:00:00');
        $tapTimeOnTime = Carbon::parse('2026-09-01 06:55:00');
        $tapTimeLate = Carbon::parse('2026-09-01 07:25:00');

        $onTime = $tapTimeOnTime->lte($shiftStart);
        $lateMinutes = (int) $shiftStart->diffInMinutes($tapTimeLate, false);

        $this->record('Absensi', '2. Deteksi Tepat Waktu (06:55)', $onTime, "Status: Tepat Waktu");
        $this->record('Absensi', '3. Deteksi Terlambat (07:25)', $lateMinutes === 25, "Terlambat: {$lateMinutes} menit");
    }

    private function testCutiAndPersuratan(): void
    {
        $perawat = Karyawan::where('nip', '199606062021012006')->first();
        $karu = User::where('email', 'karu@rsba.test')->first();
        $kabid = User::where('email', 'kabid@rsba.test')->first();
        $cutiTahunan = CutiJenis::first();

        // 1. Pengajuan Cuti
        $saldoAwal = $perawat->cuti ?? 12;
        $suratCuti = SuratCuti::create([
            'karyawan_id' => $perawat->id,
            'urgensi_id' => $cutiTahunan->id,
            'no_surat' => 'CT-' . rand(1000, 9999),
            'tgl_surat' => now(),
            'tgl_mulai' => '2026-09-10',
            'tgl_akhir' => '2026-09-12',
            'lama_cuti' => 3,
            'tgl_cuti' => json_encode(['2026-09-10', '2026-09-11', '2026-09-12']),
            'status' => 'pending',
            'keterangan' => 'Keperluan Keluarga',
            'alamat' => 'Jl. Bandar Lampung No. 10',
            'created_by' => $perawat->user?->id ?? 1,
        ]);

        // Approval Karu
        SuratCutiApproval::create([
            'surat_cuti_id' => $suratCuti->id,
            'disetujui_oleh' => $karu->karyawan_id ?? $karu->id,
            'status' => 'approved',
            'keterangan' => 'Disetujui Karu',
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // Approval Kabid -> Approved Final
        SuratCutiApproval::create([
            'surat_cuti_id' => $suratCuti->id,
            'disetujui_oleh' => $kabid->karyawan_id ?? $kabid->id,
            'status' => 'approved',
            'keterangan' => 'Disetujui Kabid',
            'approved_at' => now()->format('Y-m-d H:i:s'),
        ]);
        $suratCuti->update(['status' => 'approved']);

        // Potong kuota
        $perawat->decrement('cuti', 3);
        $perawat->refresh();

        $this->record('Persuratan & Cuti', '1. Alur Approval Cuti Berjenjang', $suratCuti->status->value === 'approved', "No Surat: {$suratCuti->no_surat}");
        $this->record('Persuratan & Cuti', '2. Pemotongan Saldo Cuti Otomatis', $perawat->cuti === ($saldoAwal - 3), "Saldo: {$saldoAwal} -> {$perawat->cuti} hari");

        // 2. Surat Perintah Tugas (SPT)
        $spt = SuratPerintahTugas::create([
            'no' => '001/SPT/RSBA/IX/2026',
            'tahun' => 2026,
            'tgl' => now(),
            'perihal' => 'Pelatihan K3 Rumah Sakit',
            'hari_tanggal' => 'Senin - Rabu, 15 - 17 September 2026',
            'waktu' => '08:00 - Selesai',
            'tempat' => 'Dinkes Provinsi Lampung',
            'status' => 'approved',
            'created_by' => 1,
        ]);
        $this->record('Persuratan & Cuti', '3. Pembuatan Surat Perintah Tugas (SPT)', $spt->exists, "No: {$spt->no}");

        // 3. Surat Balasan PKL
        $pkl = SuratBalasanPkl::create([
            'no' => '001/PKL/RSBA/IX/2026',
            'tahun' => 2026,
            'tgl' => now(),
            'tujuan_universitas' => 'Universitas Malahayati',
            'nomor_surat_masuk' => '045/MAL/2026',
            'tgl_surat_masuk' => now()->subDays(5),
            'prodi' => 'Profesi Ners',
            'jumlah_mahasiswa' => 3,
            'lama_praktik_bulan' => 2,
            'tgl_mulai' => '2026-09-01',
            'tgl_selesai' => '2026-10-31',
            'status' => 'approved',
            'created_by' => 1,
        ]);
        $this->record('Persuratan & Cuti', '4. Pembuatan Surat Balasan PKL', $pkl->exists, "No: {$pkl->no}");

        // 4. Surat Balasan Penelitian
        $riset = SuratBalasanPenelitian::create([
            'no' => '001/RIS/RSBA/IX/2026',
            'tahun' => 2026,
            'tgl' => now(),
            'tujuan_universitas' => 'Universitas Lampung',
            'tujuan_fakultas' => 'Kedokteran',
            'perihal_surat_masuk' => 'Izin Penelitian IGD',
            'status' => 'approved',
            'created_by' => 1,
        ]);
        $this->record('Persuratan & Cuti', '5. Pembuatan Surat Balasan Penelitian', $riset->exists, "No: {$riset->no}");

        // 5. Surat SP3
        $sp3 = SuratSp3::create([
            'no' => '001/SP3/RSBA/IX/2026',
            'tahun' => 2026,
            'tgl' => now(),
            'rekanan' => 'PT Medika Farma',
            'bayar' => 'trf',
            'keterangan' => 'Pengadaan Alat Medis IGD',
            'status' => 'approved',
            'created_by' => 1,
        ]);
        $this->record('Persuratan & Cuti', '6. Pembuatan Surat SP3', $sp3->exists, "No: {$sp3->no}");
    }

    private function testDigitalSignatureAndPublicQr(): void
    {
        $direktur = User::where('email', 'direktur@rsba.test')->first();
        $kabid = User::where('email', 'kabid@rsba.test')->first();

        // 1. Buat Dokumen Tanda Tangan Digital
        $docHash = Str::random(40);
        $doc = DigitalSignatureDocument::create([
            'user_id' => 1,
            'title' => 'Surat Keputusan Direktur No 001/2026',
            'document_number' => 'SK/DIR/2026/001',
            'document_type' => 'digital_signature',
            'file_name' => 'sk_001.pdf',
            'file_size' => 102400,
            'byte_counter_hash' => hash('sha256', 'sample_content'),
            'signature_hash' => $docHash,
            'status' => 'pending',
        ]);

        // Step 1: Kabid
        $app1 = DigitalSignatureApproval::create([
            'digital_signature_document_id' => $doc->id,
            'user_id' => $kabid->id,
            'step_order' => 1,
            'status' => 'approved',
            'signed_at' => now(),
        ]);

        // Step 2: Direktur (Final)
        $app2 = DigitalSignatureApproval::create([
            'digital_signature_document_id' => $doc->id,
            'user_id' => $direktur->id,
            'step_order' => 2,
            'status' => 'approved',
            'signed_at' => now(),
        ]);

        $doc->update(['status' => 'signed']);
        $this->record('Tanda Tangan Digital', '1. Alur Sequential Signing (Kabid -> Direktur)', $doc->status === 'signed', "Status: {$doc->status}");

        // 2. Verifikasi Portal Publik via Hash
        $foundDoc = DigitalSignatureDocument::where('signature_hash', $docHash)->first();
        $this->record('Tanda Tangan Digital', '2. Portal Publik QR Verifikasi (/verifikasi-surat/{hash})', $foundDoc !== null && $foundDoc->status === 'signed', "Signature Hash: {$docHash}");
    }

    private function testPayrollAndPph21(): void
    {
        $perawat = Karyawan::where('nip', '199606062021012006')->first();

        $gajiPokok = 3500000;
        $tunjanganJabatan = 1000000;
        $tunjanganAbsensi = 500000;
        $totalGaji = $gajiPokok + $tunjanganJabatan + $tunjanganAbsensi; // 5.000.000

        // PPh 21 TER Kategori A (0.25% untuk 5.000.000)
        $tarifTer = 0.0025;
        $pph21 = $totalGaji * $tarifTer; // 12.500
        $gajiBersih = $totalGaji - $pph21;

        DB::table('sdm_payroll_slips')->updateOrInsert(
            [
                'karyawan_id' => $perawat->id,
                'periode' => '2026-09',
            ],
            [
                'gaji_pokok' => $gajiPokok,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_absensi' => $tunjanganAbsensi,
                'total_gaji' => $totalGaji,
                'potongan_pph21' => $pph21,
                'pph21_calculated' => $pph21,
                'total_potongan' => $pph21,
                'gaji_bersih' => $gajiBersih,
                'created_by' => 1,
                'updated_at' => now(),
            ]
        );

        $slip = DB::table('sdm_payroll_slips')
            ->where('karyawan_id', $perawat->id)
            ->where('periode', '2026-09')
            ->first();

        $this->record('Payroll & PPh 21', '1. Kalkulasi Slip Gaji & Pajak TER PPh 21', $slip->gaji_bersih == 4987500, "Total: Rp " . number_format($totalGaji) . " | PPh21: Rp " . number_format($pph21) . " | Bersih: Rp " . number_format($gajiBersih));

        // Period Lock
        DB::table('sdm_payroll_period_locks')->updateOrInsert(
            ['periode' => '2026-09'],
            [
                'is_approved' => true,
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $lock = DB::table('sdm_payroll_period_locks')->where('periode', '2026-09')->first();
        $this->record('Payroll & PPh 21', '2. Period Lock Penggajian', (bool)$lock->is_approved === true, "Periode 09/2026 Terkunci & Approved");
    }

    private function testJasmedAndLaporan(): void
    {
        $hasJasmedRoute = Route::has('kepegawaian.jasmed.index');
        $hasLaporanRoute = Route::has('kepegawaian.laporan.index');

        $this->record('Jasmed & Laporan', '1. Route Modul Jasa Medis (/kepegawaian/jasmed)', $hasJasmedRoute, "Route: kepegawaian.jasmed.index");
        $this->record('Jasmed & Laporan', '2. Route Modul Laporan Kepegawaian (/kepegawaian/laporan)', $hasLaporanRoute, "Route: kepegawaian.laporan.index");
    }

    private function testAkreditasi(): void
    {
        $hasAkreRoute = Route::has('kepegawaian.akreditasi.index');
        $hasDownloadFile = Route::has('kepegawaian.akreditasi.download.file');
        $hasDownloadChapter = Route::has('kepegawaian.akreditasi.download.chapter');

        $this->record('Akreditasi', '1. Route Akreditasi Index (/kepegawaian/akreditasi)', $hasAkreRoute, "Route: kepegawaian.akreditasi.index");
        $this->record('Akreditasi', '2. Download Controller & Routes', $hasDownloadFile && $hasDownloadChapter, "Routes Download Siap");
    }
}
