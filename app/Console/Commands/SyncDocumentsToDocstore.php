<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratCuti;
use App\Services\DocstoreSyncService;

class SyncDocumentsToDocstore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docstore:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all signed SP3 and Cuti letters to docstore';

    /**
     * Execute the console command.
     */
    public function handle(DocstoreSyncService $syncService)
    {
        $this->info('Starting sync of SP3 letters...');

        // 1. Sync SP3
        $sp3Count = 0;
        $sp3Success = 0;

        SuratSp3::chunk(100, function ($suratList) use ($syncService, &$sp3Count, &$sp3Success) {
            foreach ($suratList as $surat) {
                $sp3Count++;
                if ($syncService->syncSp3($surat)) {
                    $sp3Success++;
                }
            }
        });

        $this->info("SP3 synced: {$sp3Success}/{$sp3Count} successfully.");

        // 2. Sync Cuti
        $this->info('Starting sync of Cuti letters...');
        $cutiCount = 0;
        $cutiSuccess = 0;

        SuratCuti::chunk(100, function ($suratList) use ($syncService, &$cutiCount, &$cutiSuccess) {
            foreach ($suratList as $surat) {
                $cutiCount++;
                if ($syncService->syncCuti($surat)) {
                    $cutiSuccess++;
                }
            }
        });

        $this->info("Cuti synced: {$cutiSuccess}/{$cutiCount} successfully.");
        $this->info('Sync process completed!');

        return Command::SUCCESS;
    }
}
