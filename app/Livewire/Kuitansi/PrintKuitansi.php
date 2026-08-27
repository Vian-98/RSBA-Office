<?php

namespace App\Livewire\Kuitansi;

use App\Models\Keuangan\Kuitansi;
use App\Models\Perusahaan;
use App\Services\DocstoreSyncService;
use App\Services\DocumentSignatureService;
use App\Services\QrGeneratorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Milon\Barcode\DNS2D;

#[Lazy]
class PrintKuitansi extends Component
{
    #[Locked]
    public ?Kuitansi $kuitansi = null;

    public ?array $docstoreData = null;
    public bool $fromDocstore = false;
    public ?string $docstoreError = null;

    public function mount(?Kuitansi $kuitansi = null)
    {
        $this->kuitansi = $kuitansi;
        if ($this->kuitansi) {
            $this->loadFromDocstore();
        }
    }

    #[On('buka-modal-kuitansi')]
    public function setKuitansi(int $id, string $modal): void
    {
        if ($modal === 'modal-print-kuitansi') {
            $this->kuitansi = Kuitansi::with(['createdBy.karyawan', 'penerima.jabatan', 'approvals.disetujuiOleh.jabatan', 'details'])->find($id);
            $this->loadFromDocstore();
        }
    }

    #[On('update-approval-kuitansi')]
    public function refreshData()
    {
        if ($this->kuitansi) {
            $this->kuitansi = Kuitansi::find($this->kuitansi->id);
            if (!empty($this->kuitansi->docstore_key)) {
                app(DocstoreSyncService::class)->invalidateCache($this->kuitansi->docstore_key);
            }
            $this->loadFromDocstore();
        }
    }

    protected function loadFromDocstore(): void
    {
        $this->fromDocstore = false;
        $this->docstoreError = null;
        $this->docstoreData = null;

        if (!$this->kuitansi || empty($this->kuitansi->docstore_key)) {
            // Jika belum di docstore, tetap izinkan cetak draft jika ada izin
            return;
        }

        try {
            $syncService = app(DocstoreSyncService::class);
            $data = $syncService->fetchFromDocstore($this->kuitansi->docstore_key);

            if ($data && ($data['success'] ?? false)) {
                $this->docstoreData = $data;
                $this->fromDocstore = true;
            }
        } catch (\Throwable $e) {
            $this->docstoreError = 'Gagal mengambil data dari bank surat: ' . $e->getMessage();
        }
    }

    #[Computed]
    public function perusahaan()
    {
        return Perusahaan::first();
    }

    #[Computed]
    public function generateQrCode()
    {
        if (!$this->kuitansi) {
            return null;
        }

        $qrService = app(QrGeneratorService::class);

        if (!empty($this->kuitansi->docstore_key)) {
            return $qrService->generateDocstoreQr($this->kuitansi->docstore_key, 3, 3);
        }

        if (!empty($this->kuitansi->qr_hash)) {
            return $qrService->generateQrPngBase64($this->kuitansi->qr_hash, 3, 3);
        }

        // Fallback: generate system p12 signature
        $docSignService = app(DocumentSignatureService::class);
        $p12Hash = $docSignService->ensureP12SystemSignature($this->kuitansi);
        return $qrService->generateQrPngBase64($p12Hash, 3, 3);
    }

    public function render()
    {
        return view('livewire.kuitansi.print-kuitansi');
    }
}
