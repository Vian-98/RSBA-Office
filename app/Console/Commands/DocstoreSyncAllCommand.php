<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratBalasanPkl;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Models\Surat\SuratPerintahTugas;
use App\Services\DocstoreSyncService;
use App\Services\DocumentSignatureService;

class DocstoreSyncAllCommand extends Command
{
    /**
     * Nama dan deskripsi command.
     *
     * @var string
     */
    protected $signature = 'docstore:sync-all {--force : Paksa sync ulang semua dokumen meski sudah pernah disync}';

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi seluruh data Surat (Cuti, SP3, Balasan PKL, Balasan Penelitian, Perintah Tugas) dari office ke bank surat (docstore)';

    /**
     * Eksekusi command.
     */
    public function handle(DocstoreSyncService $syncService, DocumentSignatureService $signatureService): int
    {
        $this->info('Starting full sync to bank surat (docstore)...');

        $force = $this->option('force');

        // 1. Sync Surat Cuti
        $cutis = SuratCuti::with(['approvals', 'karyawan', 'jenis'])->get();
        $this->info("Found {$cutis->count()} Surat Cuti records.");
        $cutiSuccess = 0; $cutiFail = 0;

        foreach ($cutis as $cuti) {
            $signatureService->checkAndGenerateHeaderQr($cuti);
            $this->output->write("Syncing Cuti #{$cuti->id} ({$cuti->no_surat})... ");
            $success = $syncService->syncCuti($cuti->fresh());
            if ($success) {
                $cutiSuccess++;
                $this->line(" <fg=green>SUCCESS</> (key: {$cuti->fresh()->docstore_key})");
            } else {
                $cutiFail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        // 2. Sync Surat SP3
        $sp3s = SuratSp3::with(['approvals', 'details', 'jabatans'])->get();
        $this->info("\nFound {$sp3s->count()} Surat SP3 records.");
        $sp3Success = 0; $sp3Fail = 0;

        foreach ($sp3s as $sp3) {
            $signatureService->checkAndGenerateHeaderQr($sp3);
            $this->output->write("Syncing SP3 #{$sp3->id} ({$sp3->no})... ");
            $success = $syncService->syncSp3($sp3->fresh());
            if ($success) {
                $sp3Success++;
                $this->line(" <fg=green>SUCCESS</> (key: {$sp3->fresh()->docstore_key})");
            } else {
                $sp3Fail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        // 3. Sync Surat Balasan PKL
        $pkls = SuratBalasanPkl::with(['direktur'])->get();
        $this->info("\nFound {$pkls->count()} Surat Balasan PKL records.");
        $pklSuccess = 0; $pklFail = 0;

        foreach ($pkls as $pkl) {
            $signatureService->checkAndGenerateHeaderQr($pkl);
            $this->output->write("Syncing Balasan PKL #{$pkl->id} ({$pkl->no})... ");
            $success = $syncService->syncBalasanPkl($pkl->fresh());
            if ($success) {
                $pklSuccess++;
                $this->line(" <fg=green>SUCCESS</> (key: {$pkl->fresh()->docstore_key})");
            } else {
                $pklFail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        // 4. Sync Surat Balasan Penelitian
        $penelitians = SuratBalasanPenelitian::with(['direktur', 'mahasiswa', 'biaya'])->get();
        $this->info("\nFound {$penelitians->count()} Surat Balasan Penelitian records.");
        $penelitianSuccess = 0; $penelitianFail = 0;

        foreach ($penelitians as $penelitian) {
            $signatureService->checkAndGenerateHeaderQr($penelitian);
            $this->output->write("Syncing Balasan Penelitian #{$penelitian->id} ({$penelitian->no})... ");
            $success = $syncService->syncBalasanPenelitian($penelitian->fresh());
            if ($success) {
                $penelitianSuccess++;
                $this->line(" <fg=green>SUCCESS</> (key: {$penelitian->fresh()->docstore_key})");
            } else {
                $penelitianFail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        // 5. Sync Surat Perintah Tugas
        $tugasList = SuratPerintahTugas::with(['direktur', 'karyawanTugas'])->get();
        $this->info("\nFound {$tugasList->count()} Surat Perintah Tugas records.");
        $tugasSuccess = 0; $tugasFail = 0;

        foreach ($tugasList as $tugas) {
            $signatureService->checkAndGenerateHeaderQr($tugas);
            $this->output->write("Syncing Perintah Tugas #{$tugas->id} ({$tugas->no})... ");
            $success = $syncService->syncPerintahTugas($tugas->fresh());
            if ($success) {
                $tugasSuccess++;
                $this->line(" <fg=green>SUCCESS</> (key: {$tugas->fresh()->docstore_key})");
            } else {
                $tugasFail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        $this->info("\n=========================================");
        $this->info("Sync completed summary:");
        $this->info("Surat Cuti              : {$cutiSuccess} success, {$cutiFail} failed.");
        $this->info("Surat SP3               : {$sp3Success} success, {$sp3Fail} failed.");
        $this->info("Surat Balasan PKL       : {$pklSuccess} success, {$pklFail} failed.");
        $this->info("Surat Balasan Penelitian: {$penelitianSuccess} success, {$penelitianFail} failed.");
        $this->info("Surat Perintah Tugas    : {$tugasSuccess} success, {$tugasFail} failed.");
        $this->info("=========================================");

        return Command::SUCCESS;
    }
}
