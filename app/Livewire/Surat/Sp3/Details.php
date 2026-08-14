<?php

namespace App\Livewire\Surat\Sp3;

use Livewire\Component;
use Milon\Barcode\DNS2D;
use App\Enums\StatusApproval;
use Livewire\Attributes\Lazy;
use App\Models\Surat\SuratSp3;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use App\Models\Surat\SuratSp3Detail;
use Illuminate\Support\Facades\Crypt;

#[Lazy]
class Details extends Component
{
    #[Locked]
    public ?SuratSp3 $suratSp3;

    public $headers = [
        ['index' => 'keterangan', 'label' => 'Keterangan'],
        ['index' => 'nominal', 'label' => 'Nominal'],
    ];

    public function mount($suratSp3)
    {
        // dd($suratSp3);
        $this->suratSp3 = $suratSp3->load(['details', 'approvals']);
    }

    #[Computed]
    public function rows()
    {
        return $this->suratSp3->details->map(function ($detail) {
            return [
                'keterangan' => $detail->keterangan,
                'nominal' => formatRupiah($detail->nominal, true, false),
            ];
        });

        dd($this->suratSp3);
    }

    #[Computed]
    public function approvals()
    {
        $barcode = new DNS2D();

        $data = $this->suratSp3->approvals->map(function ($item) use ($barcode) {
            $isManual = $item->status === \App\Enums\StatusApproval::MANUAL || str_contains(strtolower($item->keterangan ?? ''), 'manual');
            $tahapLabel = is_object($item->tahap) ? $item->tahap->nama() : ($item->tahap === 'verifikasi_keuangan' ? 'Verifikasi Keuangan' : 'Tanda Tangan Atasan');
            $sigBarcode = null;
            if ($item->signature_hash) {
                $sigBarcode = $barcode->getBarcodePNG($item->signature_hash, 'QRCODE');
            }

            return [
                'tahap'       => $tahapLabel,
                'status'      => $isManual ? 'Manual' : (is_object($item->status) ? $item->status->nama() : ucfirst($item->status)),
                'nama'        => $item->users->karyawan->full_nama ?? $item->users->karyawan->nama ?? $item->users->name,
                'approved_at' => $item->approved_at,
                'signature'   => $item->signature_hash,
                'barcode'     => $sigBarcode,
                'is_manual'   => $isManual,
            ];
        });

        return $data;
    }

    #[Computed]
    public function generateBarcode()
    {
        $ttdAtasan = $this->suratSp3->approvals->firstWhere('tahap', \App\Enums\TahapApprovalSp3::TTD_ATASAN);
        $sig = $ttdAtasan?->signature_hash ?? $this->suratSp3->qr_hash ?? $this->suratSp3->approvals->first()?->signature_hash;

        if (!$sig) {
            return '';
        }

        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($sig, 'QRCODE');
    }

    public function render()
    {
        return view('livewire.surat.sp3.details');
    }
}

