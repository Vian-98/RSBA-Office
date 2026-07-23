<?php

namespace App\Livewire\Surat\Sp3;

use Livewire\Component;
use Milon\Barcode\DNS2D;
use App\Models\Surat\SuratSp3;
use App\Services\QrGeneratorService;
use App\Services\DocumentSignatureService;
use Livewire\Attributes\Computed;

class PrintSp3 extends Component
{
    public $suratSp3;

    public function mount(?SuratSp3 $suratSp3)
    {
        $this->suratSp3 = $suratSp3;
    }

    #[Computed]
    function approvals(): array
    {
        $data = $this->suratSp3->approvals->map(function ($item): array {
            $isManual = $item->status === \App\Enums\StatusApproval::MANUAL || str_contains(strtolower($item->keterangan ?? ''), 'manual');
            return [
                'status' => $isManual ? 'Manual' : $item->status->nama(),
                'nama' => $item->users->karyawan->full_nama ?? $item->users->nama,
                'jabatan' => $item->users->karyawan?->jabatan ?? null,
                'approved_at' => $item->approved_at,
                'signature' => $item->signature_hash,
                'is_manual' => $isManual,
            ];
        })->toArray();

        return $data;
    }

    #[Computed]
    public function generateHeaderQrCode()
    {
        $docSignService = app(DocumentSignatureService::class);
        $p12Hash = $docSignService->ensureP12SystemSignature($this->suratSp3);

        $qrService = app(QrGeneratorService::class);
        return $qrService->generateQrPngBase64($p12Hash, 4, 4);
    }

    #[Computed]
    public function generateBarcode()
    {
        $key = $this->approvals();
        if (empty($key) || empty($key[0]['signature'])) {
            return $this->generateHeaderQrCode();
        }

        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($key[0]['signature'], 'QRCODE');
    }

    public function render()
    {
        return view('livewire.surat.sp3.print-sp3');
    }
}
