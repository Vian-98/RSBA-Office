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
        // return $approved->approvals;
        $data = $this->suratSp3->approvals->map(function ($item) {
            $isManual = $item->status === \App\Enums\StatusApproval::MANUAL || str_contains(strtolower($item->keterangan ?? ''), 'manual');
            return [
                'status' => $isManual ? 'Manual' : $item->status->nama(),
                'nama' => $item->users->karyawan->nama ?? $item->users->nama,
                'approved_at' => $item->approved_at,
                'signature' => $item->signature_hash,
                'is_manual' => $isManual,
            ];
        });

        return $data;
    }

    #[Computed]
    public function generateBarcode()
    {
        $key = $this->approvals();

        $barcode = new DNS2D();
        return $barcode->getBarcodePNG($key[0]['signature'], 'QRCODE');
    }

    public function render()
    {
        return view('livewire.surat.sp3.details');
    }
}
