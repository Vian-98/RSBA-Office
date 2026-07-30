<?php

namespace App\Observers;

use App\Models\Surat\SuratCutiApproval;
use App\Services\DocstoreSyncService;

class SuratCutiApprovalObserver
{
    protected DocstoreSyncService $syncService;

    public function __construct(DocstoreSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saved(SuratCutiApproval $approval)
    {
        if ($approval->signature_hash && $approval->cuti) {
            $this->syncService->syncCuti($approval->cuti);
        }
    }
}
