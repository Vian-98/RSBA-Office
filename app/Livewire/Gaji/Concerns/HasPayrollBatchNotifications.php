<?php

namespace App\Livewire\Gaji\Concerns;

use App\Models\Sdm\Karyawan;
use App\Livewire\Gaji\Services\PayrollNotificationService;

trait HasPayrollBatchNotifications
{
    // Modal state for Auto-Send Configuration
    public bool $isAutoSendModalOpen = false;
    public bool $autoSendEnabled = false;
    public string $autoSendDay = '25';
    public string $autoSendTime = '08:00';
    public int $autoSendChunkSize = 10;
    public int $autoSendDelaySeconds = 3;
    public ?array $autoSendLastRun = null;

    // Modal & Progress state for Instant Batch Sending
    public bool $isBatchSendModalOpen = false;
    public bool $isBatchSending = false;
    public int $batchTotalCount = 0;
    public int $batchProcessedCount = 0;
    public int $batchSuccessCount = 0;
    public int $batchFailedCount = 0;
    public string $currentSendingStatus = '';

    public function sendEmail(int $karyawanId, PayrollNotificationService $notifService): void
    {
        $res = $notifService->sendSingleEmail($karyawanId, $this->periode);
        if ($res['status'] === 'success') {
            $this->toast()->success('Berhasil !', $res['message'])->send();
        } elseif ($res['status'] === 'warning') {
            $this->toast()->warning('Peringatan !', $res['message'])->send();
        } else {
            $this->toast()->error('Gagal !', $res['message'])->send();
        }
    }

    public function sendSingleEmail(int $karyawanId, PayrollNotificationService $notifService): void
    {
        $this->sendEmail($karyawanId, $notifService);
    }

    public function getEmailSendStatus(int $karyawanId): ?array
    {
        $log = \App\Models\Sdm\PayrollSendLog::where('periode', $this->periode)
            ->where('karyawan_id', $karyawanId)
            ->first();

        if (!$log) {
            return null;
        }

        return [
            'status'  => $log->status,
            'sent_at' => $log->sent_at ? $log->sent_at->format('d/m/Y H:i') : null,
            'error'   => $log->error_message,
        ];
    }

    public function openAutoSendModal(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();
        $settings = $notifService->getAutoSendSettings();

        $this->autoSendEnabled = $settings['autoSendEnabled'];
        $this->autoSendDay = $settings['autoSendDay'];
        $this->autoSendTime = $settings['autoSendTime'];
        $this->autoSendChunkSize = $settings['autoSendChunkSize'];
        $this->autoSendDelaySeconds = $settings['autoSendDelaySeconds'];
        $this->autoSendLastRun = $settings['autoSendLastRun'];
        $this->isAutoSendModalOpen = true;
    }

    public function closeAutoSendModal(): void
    {
        $this->isAutoSendModalOpen = false;
    }

    public function saveAutoSendSettings(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();
        $notifService->saveAutoSendSettings([
            'enabled' => $this->autoSendEnabled,
            'day' => $this->autoSendDay,
            'time' => $this->autoSendTime,
            'chunkSize' => $this->autoSendChunkSize,
            'delaySeconds' => $this->autoSendDelaySeconds,
        ]);
        $this->isAutoSendModalOpen = false;
        $this->toast()->success('Berhasil !', 'Pengaturan jadwal pengiriman otomatis slip gaji berhasil disimpan.')->send();
    }

    public function openBatchSendModal(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();
        $employees = Karyawan::whereNull('resign_at')->get();
        $this->batchTotalCount = $employees->count();
        $this->refreshBatchProgress($notifService);
        $this->isBatchSendModalOpen = true;
    }

    public function closeBatchSendModal(): void
    {
        $this->isBatchSendModalOpen = false;
        $this->isBatchSending = false;
    }

    public function dispatchBulkQueue(PayrollNotificationService $notifService): void
    {
        $this->authorizeFromRoute();
        $dispatchedCount = $notifService->dispatchBulkQueue($this->periode);
        $this->isBatchSending = true;
        $this->refreshBatchProgress($notifService);

        if ($dispatchedCount > 0) {
            $this->toast()->success('Antrean Dimulai !', "{$dispatchedCount} slip gaji telah dimasukkan ke dalam antrean pengiriman background.")->send();
        } else {
            $this->toast()->info('Selesai', 'Semua slip gaji karyawan periode ini telah terkirim.')->send();
        }
    }

    public function refreshBatchProgress(PayrollNotificationService $notifService): void
    {
        $progress = $notifService->refreshBatchProgress($this->periode, $this->batchTotalCount);
        $this->batchSuccessCount = $progress['batchSuccessCount'];
        $this->batchFailedCount = $progress['batchFailedCount'];
        $this->batchProcessedCount = $progress['batchProcessedCount'];
        $this->isBatchSending = $progress['isBatchSending'];
        $this->currentSendingStatus = $progress['currentSendingStatus'];
    }
}
