<?php

namespace App\Livewire\Gudang;

use App\Models\Master\Barang;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Milon\Barcode\DNS2D;

class PrintKartuStok extends Component
{
    #[Locked]
    public ?Barang $barang;

    public $barcode;

    public function mount(?Barang $barang)
    {
        $this->barang = $barang;
        // dd($barang);
    }

    #[Computed]
    public function generateBarcodeBarang()
    {
        $barcode = new DNS2D();

        return $barcode->getBarcodePNG(
            $this->barang?->sku,
            'QRCODE',
            5,
            5
        );
    }

    public function render()
    {
        return view('livewire.gudang.print-kartu-stok');
    }
}
