<?php

namespace App\Observers;

use App\Models\Surat\SuratSp3Approval;
use App\Services\DocstoreSyncService;

class SuratSp3ApprovalObserver
{
    protected DocstoreSyncService $syncService;

    public function __construct(DocstoreSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saved(SuratSp3Approval $approval)
    {
        if ($approval->signature_hash && $approval->surat) {
            $this->syncService->syncSp3($approval->surat);
        }
    }
}
