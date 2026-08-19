<?php

namespace App\Livewire\Surat\Traits;

use App\Services\DocstoreSyncService;
use App\Services\QrGeneratorService;
use Livewire\Attributes\Computed;

/**
 * Trait HasDocstoreSourceOfTruth
 *
 * Trait reusable untuk seluruh komponen Livewire cetak surat.
 * Memastikan data yang dicetak bersumber murni dari Bank Surat (Docstore Vault).
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
     * Pesan kesalahan jika koneksi atau data Docstore tidak tersedia.
     */
    public ?string $docstoreError = null;

    /**
     * Ambil docstore_key dari properti model yang ada di komponen.
     */
    protected function resolveDocstoreKey(): ?string
    {
        if (isset($this->suratBalasanPkl) && !empty($this->suratBalasanPkl->docstore_key)) {
            return $this->suratBalasanPkl->docstore_key;
        }
        if (isset($this->suratBalasanPenelitian) && !empty($this->suratBalasanPenelitian->docstore_key)) {
            return $this->suratBalasanPenelitian->docstore_key;
        }
        if (isset($this->suratPerintahTugas) && !empty($this->suratPerintahTugas->docstore_key)) {
            return $this->suratPerintahTugas->docstore_key;
        }
        if (isset($this->suratCuti) && !empty($this->suratCuti->docstore_key)) {
            return $this->suratCuti->docstore_key;
        }
        if (isset($this->suratSp3) && !empty($this->suratSp3->docstore_key)) {
            return $this->suratSp3->docstore_key;
        }

        return null;
    }

    /**
     * Memuat data dokumen langsung dari Bank Surat (Docstore).
     */
    public function loadFromDocstore(): void
    {
        $this->fromDocstore = false;
        $this->docstoreError = null;
        $this->docstoreData = null;

        $docstoreKey = $this->resolveDocstoreKey();

        if (empty($docstoreKey)) {
            $this->docstoreError = 'Surat ini belum tersinkronisasi ke bank surat (docstore). '
                . 'Lakukan approval terlebih dahulu agar surat terdaftar di Bank Surat.';
            return;
        }

        try {
            $syncService = app(DocstoreSyncService::class);
            $data = $syncService->fetchFromDocstore($docstoreKey);

            if ($data && ($data['success'] ?? false)) {
                $this->docstoreData = $data;
                $this->fromDocstore = true;
            } else {
                $this->docstoreError = 'Gagal mengambil data dari bank surat. '
                    . 'Pastikan server docstore aktif dan coba lagi.';
            }
        } catch (\Throwable $e) {
            $this->docstoreError = 'Koneksi ke bank surat gagal: ' . $e->getMessage();
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

        return null;
    }

    /**
     * Cek apakah dokumen memenuhi syarat untuk dicetak.
     */
    #[Computed]
    public function canPrint(): bool
    {
        return $this->fromDocstore && !empty($this->docstoreData);
    }
}
