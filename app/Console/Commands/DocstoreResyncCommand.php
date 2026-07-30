<?php

namespace App\Console\Commands;

use App\Models\Surat\SuratSp3;
use App\Models\Surat\SuratCuti;
use App\Services\DocstoreSyncService;
use Illuminate\Console\Command;

class DocstoreResyncCommand extends Command
{
    protected $signature = 'docstore:resync
                            {--type= : Tipe dokumen (sp3, cuti, atau kosongkan untuk semua)}
                            {--id= : ID spesifik dokumen yang ingin di-resync}';

    protected $description = 'Re-sync dokumen yang sudah approved ke Docstore (untuk memperbaiki data signature yang sudah tersimpan)';

    public function handle(DocstoreSyncService $syncService): int
    {
        $type = $this->option('type');
        $specificId = $this->option('id');

        if ($type && !in_array($type, ['sp3', 'cuti'])) {
            $this->error("Tipe tidak valid. Gunakan: sp3, cuti");
            return self::FAILURE;
        }

        $types = $type ? [$type] : ['sp3', 'cuti'];

        foreach ($types as $docType) {
            if ($docType === 'sp3') {
                $this->resyncSp3($syncService, $specificId);
            } elseif ($docType === 'cuti') {
                $this->resyncCuti($syncService, $specificId);
            }
        }

        $this->newLine();
        $this->info('Resync selesai.');
        return self::SUCCESS;
    }

    private function resyncSp3(DocstoreSyncService $syncService, ?string $specificId): void
    {
        $this->info('=== Resync Surat SP3 ===');

        $query = SuratSp3::whereHas('approvals', function ($q) {
            $q->whereNotNull('signature_hash');
        });

        if ($specificId) {
            $query->where('id', $specificId);
        }

        $surats = $query->get();

        if ($surats->isEmpty()) {
            $this->warn('Tidak ada SP3 dengan signature untuk di-resync.');
            return;
        }

        $this->info("Ditemukan {$surats->count()} SP3 untuk di-resync.");
        $bar = $this->output->createProgressBar($surats->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($surats as $surat) {
            try {
                $result = $syncService->syncSp3($surat);
                if ($result) {
                    $success++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("  Gagal sync SP3 #{$surat->id} ({$surat->no})");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  Error SP3 #{$surat->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("SP3: {$success} berhasil, {$failed} gagal.");
    }

    private function resyncCuti(DocstoreSyncService $syncService, ?string $specificId): void
    {
        $this->info('=== Resync Surat Cuti ===');

        $query = SuratCuti::whereHas('approvals', function ($q) {
            $q->whereNotNull('signature_hash');
        });

        if ($specificId) {
            $query->where('id', $specificId);
        }

        $surats = $query->get();

        if ($surats->isEmpty()) {
            $this->warn('Tidak ada Surat Cuti dengan signature untuk di-resync.');
            return;
        }

        $this->info("Ditemukan {$surats->count()} Surat Cuti untuk di-resync.");
        $bar = $this->output->createProgressBar($surats->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($surats as $surat) {
            try {
                $result = $syncService->syncCuti($surat);
                if ($result) {
                    $success++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("  Gagal sync Cuti #{$surat->id} ({$surat->no_surat})");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  Error Cuti #{$surat->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Cuti: {$success} berhasil, {$failed} gagal.");
    }
}
