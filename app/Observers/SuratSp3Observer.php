<?php

namespace App\Observers;

use App\Models\Surat\SuratSp3;
use App\Services\DocstoreSyncService;

class SuratSp3Observer
{
    protected DocstoreSyncService $syncService;

    public function __construct(DocstoreSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saved(SuratSp3 $surat)
    {
        $this->syncService->syncSp3($surat);
    }
}
