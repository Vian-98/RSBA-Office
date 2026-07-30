<?php

namespace App\Livewire\Gudang;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ViewFastMoving extends Component
{


    private function headers(): array
    {
        return [
            ['index' => 'id', 'label' => 'ID'],
            ['index' => 'barang', 'label' => 'Barang'],
            ['index' => 'jumlah_beli', 'label' => 'Jumlah Beli'],
            ['index' => 'jumlah_keluar', 'label' => 'Jumlah Keluar'],
        ];
    }

    private function rows()
    {
        return [];
    }


    public function render()
    {
        $rows = $this->rows();


        return view('livewire.gudang.view-fast-moving', [
            'headers' => $this->headers(),
            'rows' => $rows,
            'paginator' => $rows
        ]);
    }
}
