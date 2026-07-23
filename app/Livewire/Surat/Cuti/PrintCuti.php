<?php

namespace App\Livewire\Surat\Cuti;

use App\Models\Surat\SuratCuti;
use App\Services\QrGeneratorService;
use App\Services\DocumentSignatureService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Milon\Barcode\DNS2D;

class PrintCuti extends Component
{
    #[Locked]
    public SuratCuti $suratCuti;

    public function mount(SuratCuti $suratCuti)
    {
        $this->suratCuti = SuratCuti::findOrFail($suratCuti->id);
    }

    #[On('surat-cuti-manual-approved')]
    #[On('surat-cuti-approved')]
    public function refreshData()
    {
        $this->suratCuti = SuratCuti::find($this->suratCuti->id);

        $this->dispatch('trigger-print', noSurat: $this->suratCuti->no_surat);
    }

    #[Computed]
    public function karyawan()
    {
        return $this->suratCuti->karyawan;
    }

    #[Computed]
    public function approvals()
    {
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

    #[Computed]
    public function generateHeaderQrCode()
    {
        $docSignService = app(DocumentSignatureService::class);
        $p12Hash = $docSignService->ensureP12SystemSignature($this->suratCuti);

        $qrService = app(QrGeneratorService::class);
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

    public function render()
    {
        if ($this->suratCuti && $this->suratCuti->exists) {
            $this->suratCuti = $this->suratCuti->fresh();
        }
        return view('livewire.surat.cuti.print-cuti');
    }
}
