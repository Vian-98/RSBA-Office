<?php

namespace App\Services\Docstore\Contracts;

use Illuminate\Database\Eloquent\Model;

interface DocumentSynchronizerInterface
{
    /**
     * Cek apakah synchronizer mendukung model ini.
     */
    public function supports(Model $model): bool;

    /**
     * Eksekusi sinkronisasi model ke Docstore.
     */
    public function sync(Model $model): bool;

    /**
     * Susun payload content dan metadata untuk Docstore.
     */
    public function buildPayload(Model $model): array;

    /**
     * Susun data tanda tangan digital untuk Docstore.
     */
    public function buildSignatures(Model $model): array;
}
