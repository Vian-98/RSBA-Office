<?php

namespace App\Livewire\Asset;

use App\Models\Assets\AssetBarang;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Milon\Barcode\DNS1D;


class PrintLabel extends Component
{
    public ?AssetBarang $assetBarang;
    public $logo;

    public function mount($id)
    {
        // if ($id) {
        $this->assetBarang = AssetBarang::with(['barang', 'ruangan'])->find($id);
        // }

        // if (!$this->assetBarang) {
        // abort(404, 'Asset not found');
        // }
    }

    #[Computed]
    public function generateBarcode()
    {

        $barcode = new DNS1D();

        // QRCODE
        // return $barcode->getBarcodePNG(
        //     $this->assetBarang?->kode,
        //     'QRCODE',
        //     5,
        //     5
        if (empty($this->assetBarang?->kode)) {
            return null;
        }

        // Baris 
        return $barcode->getBarcodePNG(
            $this->assetBarang->kode,
            'C128',
            2,
            60
        );
    }

    public function render()
    {
        return view('livewire.asset.print-label');
    }
}
