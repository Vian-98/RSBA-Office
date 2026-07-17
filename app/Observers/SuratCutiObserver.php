<?php

namespace App\Observers;

use App\Models\Surat\SuratCuti;
use App\Services\DocstoreSyncService;

class SuratCutiObserver
{
    protected DocstoreSyncService $syncService;

    public function __construct(DocstoreSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saved(SuratCuti $surat)
    {
        $this->syncService->syncCuti($surat);
    }
}
