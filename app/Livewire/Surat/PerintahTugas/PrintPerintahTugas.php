<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratPerintahTugas;
use App\Services\QrGeneratorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintPerintahTugas extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratPerintahTugas $suratPerintahTugas = null;

    public function mount(?SuratPerintahTugas $suratPerintahTugas): void
    {
        $this->suratPerintahTugas = SuratPerintahTugas::findOrFail($suratPerintahTugas->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-perintah-tugas')]
    public function refreshData(): void
    {
        $this->suratPerintahTugas = SuratPerintahTugas::find($this->suratPerintahTugas->id);
        $this->refreshDocstore();
    }

    /**
     * Generate QR Code verifikasi untuk surat perintah tugas.
     * Prioritas: docstore_key -> qr_hash
     */
    #[Computed]
    public function generateHeaderQrCode(): ?string
    {
        $qrService = app(QrGeneratorService::class);

        if (!empty($this->suratPerintahTugas->docstore_key)) {
            return $qrService->generateDocstoreQr($this->suratPerintahTugas->docstore_key, 4, 4);
        }

        if (!empty($this->suratPerintahTugas->qr_hash)) {
            return $qrService->generateQrPngBase64($qrService->getVerificationUrl($this->suratPerintahTugas->qr_hash), 4, 4);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.print-perintah-tugas', [
            'suratPerintahTugas' => $this->suratPerintahTugas,
            'fromDocstore'       => $this->fromDocstore,
            'docstoreData'       => $this->docstoreData,
            'qrCode'             => $this->generateHeaderQrCode(),
        ]);
    }
}
