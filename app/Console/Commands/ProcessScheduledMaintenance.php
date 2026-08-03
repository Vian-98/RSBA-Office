<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assets\AssetBarang;
use App\Models\Assets\AssetMaintenanceSchedule;
use App\Models\Maintenance\Request as MaintenanceRequest;
use App\Models\Maintenance\TicketComment;
use Illuminate\Support\Facades\DB;

class ProcessScheduledMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses jadwal maintenance berkala (bulanan/tahunan) barang aset dan membuat tiket inspeksi otomatis.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa jadwal maintenance berkala aset yang jatuh tempo...');

        $dueSchedules = AssetMaintenanceSchedule::due()->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('Tidak ada jadwal maintenance yang jatuh tempo hari ini.');
            return Command::SUCCESS;
        }

        $processedCount = 0;

        foreach ($dueSchedules as $schedule) {
            DB::beginTransaction();
            try {
                $asset = AssetBarang::find($schedule->asset_barang_id);

                if (!$asset) {
                    $this->warn("Aset dengan ID {$schedule->asset_barang_id} tidak ditemukan. Melewati...");
                    DB::rollBack();
                    continue;
                }

                $hasActiveTicket = MaintenanceRequest::where('asset_id', $asset->id)
                    ->active()
                    ->exists();

                if ($hasActiveTicket || $asset->status === 'diperbaiki') {
                    $namaBarang = $asset->barang?->nama ?? 'Aset';
                    $this->warn("Aset {$asset->kode} ({$namaBarang}) sedang dalam perbaikan/maintenance aktif. Melewati pemicuan tiket berkala...");
                    DB::rollBack();
                    continue;
                }

                $systemUserId = \App\Models\User::first()?->id ?? 1;

                // Buat tiket perbaikan/inspeksi rutin otomatis
                $maintReq = MaintenanceRequest::create([
                    'asset_id' => $asset->id,
                    'user_req_id' => $systemUserId,
                    'priority' => 'normal',
                    'ket_priority' => 'Inspeksi / Service Berkala',
                    'note' => '[JADWAL OTOMATIS] ' . $schedule->judul . ($schedule->catatan ? ' - ' . $schedule->catatan : ''),
                    'status' => 'pending',
                ]);

                TicketComment::create([
                    'request_id' => $maintReq->id,
                    'user_id'    => $systemUserId,
                    'body'       => 'Tiket Inspeksi/Service Rutin Berkala dibuat otomatis oleh Sistem (Cron Scheduler) untuk ' . ($asset->barang->nama ?? 'Asset'),
                    'type'       => 'log',
                ]);

                $asset->update(['status' => 'diperbaiki']);

                // Hitung jadwal berikutnya
                $nextDueDate = $schedule->calculateNextDueDate(now());

                $schedule->update([
                    'terakhir_dilakukan' => now()->toDateString(),
                    'tgl_berikutnya' => $nextDueDate->toDateString(),
                ]);

                DB::commit();

                $this->info("✅ Berhasil membuat tiket maintenance untuk aset: {$asset->kode} ({$asset->barang->nama}) - {$schedule->judul}. Service berikutnya: {$nextDueDate->format('d M Y')}");
                $processedCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("❌ Gagal memproses jadwal ID {$schedule->id}: " . $e->getMessage());
            }
        }

        $this->info("Proses selesai. Total {$processedCount} tiket maintenance berkala berhasil dibuat.");

        return Command::SUCCESS;
    }
}
