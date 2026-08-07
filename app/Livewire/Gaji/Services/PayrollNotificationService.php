<?php

namespace App\Livewire\Gaji\Services;

use App\Models\Sdm\Karyawan;
use App\Models\Sdm\PayrollSendLog;
use Illuminate\Support\Facades\DB;

class PayrollNotificationService
{
    public function triggerQueueWorker(): void
    {
        if (str_contains(PHP_OS_FAMILY, 'Windows')) {
            pclose(popen("start /B php artisan queue:work --stop-when-empty", "r"));
        } else {
            exec("php artisan queue:work --stop-when-empty > /dev/null 2>&1 &");
        }
    }

    public function sendSingleEmail(int $karyawanId, string $periode): array
    {
        $karyawan = Karyawan::with(['user'])->find($karyawanId);

        if (!$karyawan) {
            return ['status' => 'error', 'message' => 'Karyawan tidak ditemukan.'];
        }

        $email = $karyawan->email ?: optional($karyawan->user)->email;
        if (!$email) {
            return ['status' => 'warning', 'message' => 'Karyawan ini tidak memiliki alamat email terdaftar.'];
        }

        try {
            \App\Jobs\SendPayrollSlipJob::dispatchSync($karyawanId, $periode, 'manual');

            $log = PayrollSendLog::where('periode', $periode)->where('karyawan_id', $karyawanId)->first();
            if ($log && $log->status === 'failed') {
                return ['status' => 'error', 'message' => 'Gagal mengirim email ke ' . $email . ': ' . ($log->error_message ?: 'Terjadi kesalahan pengiriman SMTP.')];
            }

            return ['status' => 'success', 'message' => 'Email slip gaji berhasil dikirimkan ke ' . $email];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Gagal mengirim email ke ' . $email . ': ' . $e->getMessage()];
        }
    }

    public function dispatchBulkQueue(string $periode): int
    {
        $employees = Karyawan::whereNull('resign_at')->get();
        $dispatchedCount = 0;

        foreach ($employees as $karyawan) {
            $log = PayrollSendLog::where('periode', $periode)
                ->where('karyawan_id', $karyawan->id)
                ->first();

            if (!$log || $log->status !== 'sent') {
                $email = $karyawan->email ?: optional($karyawan->user)->email;
                if (!$email) {
                    PayrollSendLog::updateOrCreate(
                        [
                            'periode' => $periode,
                            'karyawan_id' => $karyawan->id,
                        ],
                        [
                            'email' => '-',
                            'status' => 'failed',
                            'tipe_pengiriman' => 'instant_batch',
                            'error_message' => 'Email karyawan belum terdaftar/kosong di sistem.',
                        ]
                    );
                } else {
                    PayrollSendLog::updateOrCreate(
                        [
                            'periode' => $periode,
                            'karyawan_id' => $karyawan->id,
                        ],
                        [
                            'email' => $email,
                            'status' => 'pending',
                            'tipe_pengiriman' => 'instant_batch',
                        ]
                    );
                    \App\Jobs\SendPayrollSlipJob::dispatch($karyawan->id, $periode, 'instant_batch');
                    $dispatchedCount++;
                }
            }
        }

        $this->triggerQueueWorker();
        return $dispatchedCount;
    }

    public function getAutoSendSettings(): array
    {
        $lastRunRaw = DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_last_run')->value('value');
        
        return [
            'autoSendEnabled' => DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_enabled')->value('value') === '1',
            'autoSendDay' => DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_day')->value('value') ?: '25',
            'autoSendTime' => DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_time')->value('value') ?: '08:00',
            'autoSendChunkSize' => (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_chunk_size')->value('value') ?: 10),
            'autoSendDelaySeconds' => (int) (DB::table('sdm_payroll_settings')->where('key', 'auto_send_email_delay_seconds')->value('value') ?: 3),
            'autoSendLastRun' => $lastRunRaw ? json_decode($lastRunRaw, true) : null,
        ];
    }

    public function saveAutoSendSettings(array $settings): void
    {
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_enabled'], ['value' => !empty($settings['enabled']) ? '1' : '0', 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_day'], ['value' => (string) ($settings['day'] ?? '25'), 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_time'], ['value' => (string) ($settings['time'] ?? '08:00'), 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_chunk_size'], ['value' => (string) ($settings['chunkSize'] ?? 10), 'updated_at' => now()]);
        DB::table('sdm_payroll_settings')->updateOrInsert(['key' => 'auto_send_email_delay_seconds'], ['value' => (string) ($settings['delaySeconds'] ?? 3), 'updated_at' => now()]);
    }

    public function refreshBatchProgress(string $periode, int $batchTotalCount): array
    {
        $successCount = PayrollSendLog::where('periode', $periode)->where('status', 'sent')->count();
        $failedCount = PayrollSendLog::where('periode', $periode)->where('status', 'failed')->count();
        $processedCount = $successCount + $failedCount;

        $isSending = true;
        $statusText = '';

        if ($batchTotalCount > 0 && $processedCount >= $batchTotalCount) {
            $isSending = false;
            $statusText = 'Pengiriman antrean background selesai!';
        } else {
            $pendingCount = DB::table('jobs')->count();
            $statusText = "Memproses antrean background... ({$processedCount}/{$batchTotalCount} selesai, {$pendingCount} dalam antrean)";
        }

        return [
            'batchSuccessCount' => $successCount,
            'batchFailedCount' => $failedCount,
            'batchProcessedCount' => $processedCount,
            'isBatchSending' => $isSending,
            'currentSendingStatus' => $statusText,
        ];
    }
}
