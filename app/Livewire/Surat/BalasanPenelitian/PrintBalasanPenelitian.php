<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratBalasanPenelitian;
use App\Services\QrGeneratorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintBalasanPenelitian extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratBalasanPenelitian $suratBalasanPenelitian = null;

    public function mount(?SuratBalasanPenelitian $suratBalasanPenelitian): void
    {
        $this->suratBalasanPenelitian = SuratBalasanPenelitian::findOrFail($suratBalasanPenelitian->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-balasan-penelitian')]
    public function refreshData(): void
    {
        $this->suratBalasanPenelitian = SuratBalasanPenelitian::find($this->suratBalasanPenelitian->id);
        $this->refreshDocstore();
    }

    /**
     * Generate QR Code verifikasi untuk surat.
     * Prioritas: docstore_key -> qr_hash
     */
    #[Computed]
    public function generateHeaderQrCode(): ?string
    {
        $qrService = app(QrGeneratorService::class);

        if (!empty($this->suratBalasanPenelitian->docstore_key)) {
            return $qrService->generateDocstoreQr($this->suratBalasanPenelitian->docstore_key, 4, 4);
        }

        if (!empty($this->suratBalasanPenelitian->qr_hash)) {
            return $qrService->generateQrPngBase64($qrService->getVerificationUrl($this->suratBalasanPenelitian->qr_hash), 4, 4);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.print-balasan-penelitian', [
            'suratBalasanPenelitian' => $this->suratBalasanPenelitian,
            'fromDocstore'           => $this->fromDocstore,
            'docstoreData'           => $this->docstoreData,
            'qrCode'                 => $this->generateHeaderQrCode(),
        ]);
    }
}
