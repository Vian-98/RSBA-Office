<?php

namespace App\Livewire\Gudang;

use App\Models\Gudang\StokMutasi;
use App\Models\Master\Barang;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Milon\Barcode\DNS2D;

class PrintKartuStokByTrans extends Component
{
    #[Locked]
    public ?Barang $barang;

    #[Locked]
    public ?StokMutasi $stokMutasi;

    public function mount(?Barang $barang)
    {
        $this->barang = $barang;
    }

    #[Computed]
    public function mutasi_stok()
    {
        $runningTotal = 0;
        return StokMutasi::with([
            'stoks',
            'stoks.penerimaanDet'
        ])
            ->where('barang_id', $this->barang->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item, $index) use (&$runningTotal) {
                $runningTotal += $item->jumlah_bersih;
                $item->akhir = $runningTotal;
                return $item;
            })
        ;
    }


    #[Computed]
    public function stokAwal(): int
    {
        if ($this->mutasi_stok->isEmpty()) {
            return 0;
        }

        $awal = $this->mutasi_stok[0]->stoks->penerimaanDet->jumlah ?? 0;
        return $awal;
    }                       // Sum the jumlah field


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
        return view('livewire.gudang.print-kartu-stok-by-trans');
    }
}
