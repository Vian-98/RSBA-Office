<?php

namespace App\Livewire\Surat\Cuti;

use App\Models\Surat\SuratCuti;
use App\Services\QrGeneratorService;
use App\Services\DocumentSignatureService;
use App\Services\DocstoreSyncService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Milon\Barcode\DNS2D;

class PrintCuti extends Component
{
    #[Locked]
    public SuratCuti $suratCuti;

    /**
     * Data surat dari docstore (bank surat — source of truth).
     * Null jika belum disync atau docstore tidak tersedia.
     */
    public ?array $docstoreData = null;

    /**
     * Apakah data berhasil diambil dari docstore.
     */
    public bool $fromDocstore = false;

    /**
     * Pesan error jika docstore tidak dapat diakses.
     */
    public ?string $docstoreError = null;

    public function mount(SuratCuti $suratCuti)
    {
        $this->suratCuti = SuratCuti::findOrFail($suratCuti->id);
        $this->loadFromDocstore();
    }

    #[On('surat-cuti-manual-approved')]
    #[On('surat-cuti-approved')]
    public function refreshData()
    {
        $this->suratCuti = SuratCuti::find($this->suratCuti->id);

        // Invalidate cache dan reload dari docstore
        if (!empty($this->suratCuti->docstore_key)) {
            app(DocstoreSyncService::class)->invalidateCache($this->suratCuti->docstore_key);
        }
        $this->loadFromDocstore();

        $this->dispatch('trigger-print', noSurat: $this->suratCuti->no_surat);
    }

    /**
     * Load data dari docstore.
     * Print WAJIB dari docstore — jika docstore tidak tersedia, tampilkan error.
     */
    protected function loadFromDocstore(): void
    {
        $this->fromDocstore = false;
        $this->docstoreError = null;
        $this->docstoreData = null;

        if (empty($this->suratCuti->docstore_key)) {
            $this->docstoreError = 'Surat ini belum tersinkronisasi ke bank surat (docstore). '
                . 'Lakukan approval terlebih dahulu atau hubungi administrator.';
            return;
        }

        try {
            $syncService = app(DocstoreSyncService::class);
            $data = $syncService->fetchFromDocstore($this->suratCuti->docstore_key);

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

    #[Computed]
    public function karyawan()
    {
        return $this->suratCuti->karyawan;
    }

    #[Computed]
    public function approvals()
    {
        // Ambil dari docstoreData jika tersedia (data lebih terpercaya)
        if ($this->fromDocstore && !empty($this->docstoreData['all_signatures'])) {
            return array_map(function ($sig) {
                return [
                    'nama'      => $sig['signer_name'],
                    'jabatan'   => $sig['signer_role'],
                    'status'    => $sig['status'],
                    'signature' => $sig['signature_hash'],
                    'is_manual' => false,
                ];
            }, $this->docstoreData['all_signatures']);
        }

        // Fallback ke data lokal (hanya untuk rendering UI, bukan untuk mencetak)
        return $this->suratCuti->approvals->map(
            function ($approval) {
                $jabatan = $approval->karyawan?->jabatan?->first();
                $isManual = $approval->status === \App\Enums\StatusApproval::MANUAL
                    || str_contains(strtolower($approval->keterangan ?? ''), 'manual');

                return [
                    'nama'      => $approval->karyawan?->full_nama,
                    'jabatan'   => $jabatan?->nama,
                    'status'    => $isManual ? 'Manual' : $approval->status->nama(),
                    'signature' => $approval?->signature_hash,
                    'is_manual' => $isManual,
                ];
            }
        )->toArray();
    }

    /**
     * Generate QR Code untuk surat.
     *
     * Prioritas QR:
     * 1. docstore_key (UUID) → QR berisi URL verify app dengan docstore_key
     *    Scan QR → verify app → fetch data langsung dari docstore
     * 2. qr_hash (SHA256) → fallback ke hash lokal (tidak direkomendasikan)
     */
    #[Computed]
    public function generateHeaderQrCode()
    {
        $qrService = app(QrGeneratorService::class);

        // Prioritas: gunakan docstore_key untuk QR (lebih aman dan langsung ke bank surat)
        if (!empty($this->suratCuti->docstore_key)) {
            return $qrService->generateDocstoreQr($this->suratCuti->docstore_key, 4, 4);
        }

        // Fallback: generate qr_hash dari sistem PKCS12 (jika belum ada docstore_key)
        $docSignService = app(DocumentSignatureService::class);
        $p12Hash = $docSignService->ensureP12SystemSignature($this->suratCuti);
        return $qrService->generateQrPngBase64($p12Hash, 4, 4);
    }

    #[Computed]
    public function generateBarcode($key)
    {
        if (!$key) {
            return $this->generateHeaderQrCode();
        }
        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($key, 'QRCODE');
    }

    /**
     * Apakah print dapat dilakukan (docstore_key tersedia dan data berhasil diambil).
     */
    #[Computed]
    public function canPrint(): bool
    {
        return $this->fromDocstore && !empty($this->docstoreData);
    }

    public function render()
    {
        if ($this->suratCuti && $this->suratCuti->exists) {
            $this->suratCuti = $this->suratCuti->fresh();
        }
        return view('livewire.surat.cuti.print-cuti');
    }
}
