<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratBalasanPkl;
use App\Services\QrGeneratorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintBalasanPkl extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratBalasanPkl $suratBalasanPkl = null;

    public function mount(?SuratBalasanPkl $suratBalasanPkl): void
    {
        $this->suratBalasanPkl = SuratBalasanPkl::findOrFail($suratBalasanPkl->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-balasan-pkl')]
    public function refreshData(): void
    {
        $this->suratBalasanPkl = SuratBalasanPkl::find($this->suratBalasanPkl->id);
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

        if (!empty($this->suratBalasanPkl->docstore_key)) {
            return $qrService->generateDocstoreQr($this->suratBalasanPkl->docstore_key, 4, 4);
        }

        if (!empty($this->suratBalasanPkl->qr_hash)) {
            return $qrService->generateQrPngBase64($qrService->getVerificationUrl($this->suratBalasanPkl->qr_hash), 4, 4);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.print-balasan-pkl', [
            'suratBalasanPkl' => $this->suratBalasanPkl,
            'fromDocstore' => $this->fromDocstore,
            'docstoreData' => $this->docstoreData,
            'qrCode' => $this->generateHeaderQrCode(),
        ]);
    }
}
