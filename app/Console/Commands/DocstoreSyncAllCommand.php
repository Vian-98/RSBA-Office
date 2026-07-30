<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Surat\SuratCuti;
use App\Models\Surat\SuratSp3;
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
    protected $description = 'Sinkronisasi seluruh data Surat Cuti dan SP3 dari office ke bank surat (docstore)';

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

        $cutiSuccess = 0;
        $cutiFail = 0;

        foreach ($cutis as $cuti) {
            // Pastikan system signature (P12) ter-generate jika sudah full approved
            $signatureService->checkAndGenerateHeaderQr($cuti);

            $this->output->write("Syncing Cuti #{$cuti->id} ({$cuti->no_surat})... ");
            $success = $syncService->syncCuti($cuti->fresh());

            if ($success) {
                $cutiSuccess++;
                $key = $cuti->fresh()->docstore_key;
                $this->line(" <fg=green>SUCCESS</> (key: {$key})");
            } else {
                $cutiFail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        // 2. Sync Surat SP3
        $sp3s = SuratSp3::with(['approvals', 'details', 'jabatans'])->get();
        $this->info("\nFound {$sp3s->count()} Surat SP3 records.");

        $sp3Success = 0;
        $sp3Fail = 0;

        foreach ($sp3s as $sp3) {
            // Pastikan system signature (P12) ter-generate jika sudah full approved
            $signatureService->checkAndGenerateHeaderQr($sp3);

            $this->output->write("Syncing SP3 #{$sp3->id} ({$sp3->no})... ");
            $success = $syncService->syncSp3($sp3->fresh());

            if ($success) {
                $sp3Success++;
                $key = $sp3->fresh()->docstore_key;
                $this->line(" <fg=green>SUCCESS</> (key: {$key})");
            } else {
                $sp3Fail++;
                $this->line(" <fg=red>FAILED</>");
            }
        }

        $this->info("\n=========================================");
        $this->info("Sync completed summary:");
        $this->info("Surat Cuti : {$cutiSuccess} success, {$cutiFail} failed.");
        $this->info("Surat SP3  : {$sp3Success} success, {$sp3Fail} failed.");
        $this->info("=========================================");

        return Command::SUCCESS;
    }
}
