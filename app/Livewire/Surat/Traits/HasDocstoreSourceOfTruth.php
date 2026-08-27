<?php

namespace App\Livewire\Surat\Traits;

use App\Services\DocstoreSyncService;
use App\Services\QrGeneratorService;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;

/**
 * Trait HasDocstoreSourceOfTruth
 *
 * Trait reusable untuk seluruh komponen Livewire cetak surat.
 * Mengutamakan data dari Bank Surat (Docstore Vault — Source of Truth)
 * dengan graceful fallback ke database lokal agar surat selalu dapat dicetak.
 */
trait HasDocstoreSourceOfTruth
{
    /**
     * Data dokumen dari bank surat (Docstore Vault — Source of Truth).
     */
    public ?array $docstoreData = null;

    /**
     * Penanda apakah data berhasil dimuat dari Docstore.
     */
    public bool $fromDocstore = false;

    /**
     * Pesan kesalahan jika koneksi Docstore bermasalah.
     */
    public ?string $docstoreError = null;

    /**
     * Ambil instance model surat yang sedang dicetak.
     */
    protected function resolveModel(): ?Model
    {
        return $this->suratBalasanPkl
            ?? $this->suratBalasanPenelitian
            ?? $this->suratPerintahTugas
            ?? $this->suratCuti
            ?? $this->suratSp3
            ?? null;
    }

    /**
     * Ambil docstore_key dari properti model yang ada di komponen.
     */
    protected function resolveDocstoreKey(): ?string
    {
        $model = $this->resolveModel();
        return $model?->docstore_key ?: null;
    }

    /**
     * Memuat data dokumen dari Bank Surat (Docstore) dengan on-demand sync.
     */
    public function loadFromDocstore(): void
    {
        $this->fromDocstore = false;
        $this->docstoreError = null;
        $this->docstoreData = null;

        $model = $this->resolveModel();
        if (!$model) {
            $this->docstoreError = 'Data surat tidak ditemukan.';
            return;
        }

        $docstoreKey = $this->resolveDocstoreKey();
        $syncService = app(DocstoreSyncService::class);

        // Jika surat belum memiliki docstore_key, lakukan on-demand sync ke Docstore
        if (empty($docstoreKey)) {
            try {
                $synced = $syncService->syncDocument($model);
                if ($synced) {
                    $model->refresh();
                    $docstoreKey = $model->docstore_key;
                }
            } catch (\Throwable $e) {
                // Jangan gagalkan proses jika server docstore offline
            }
        }

        // Jika memiliki docstore_key, ambil data resmi dari Docstore
        if (!empty($docstoreKey)) {
            try {
                $data = $syncService->fetchFromDocstore($docstoreKey);
                if ($data && ($data['success'] ?? false)) {
                    $this->docstoreData = $data;
                    $this->fromDocstore = true;
                    return;
                }
            } catch (\Throwable $e) {
                $this->docstoreError = 'Koneksi ke Docstore Vault lambat atau terputus: ' . $e->getMessage();
            }
        }

        // Jika belum tersinkron atau Docstore offline, mode fallback lokal aktif
        if (!$this->fromDocstore) {
            $this->docstoreError = 'Dokumen belum tersinkronisasi ke Bank Surat (Docstore). Menampilkan data pratinjau lokal.';
        }
    }

    /**
     * Refresh data dari Docstore dan bersihkan cache.
     */
    public function refreshDocstore(): void
    {
        $docstoreKey = $this->resolveDocstoreKey();
        if (!empty($docstoreKey)) {
            app(DocstoreSyncService::class)->invalidateCache($docstoreKey);
        }
        $this->loadFromDocstore();
    }

    /**
     * Generate QR Code verifikasi bank surat.
     */
    #[Computed]
    public function generateHeaderQrCode(): ?string
    {
        $docstoreKey = $this->resolveDocstoreKey();
        if (!empty($docstoreKey)) {
            return app(QrGeneratorService::class)->generateDocstoreQr($docstoreKey, 4, 4);
        }

        $model = $this->resolveModel();
        if ($model && !empty($model->qr_verification_hash)) {
            return app(QrGeneratorService::class)->generateDocstoreQr($model->qr_verification_hash, 4, 4);
        }

        return null;
    }

    /**
     * Cek apakah dokumen memenuhi syarat untuk dicetak (selalu true jika model ada).
     */
    #[Computed]
    public function canPrint(): bool
    {
        return $this->resolveModel() !== null;
    }
}
