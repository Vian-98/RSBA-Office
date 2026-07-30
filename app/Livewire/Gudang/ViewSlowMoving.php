<?php

namespace App\Livewire\Gudang;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ViewSlowMoving extends Component
{

    private function headers(): array
    {
        return [
            ['index' => 'id', 'label' => 'Id'],
            ['index' => 'barang', 'label' => 'Barang'],
            ['index' => 'masuk', 'label' => 'Total Masuk'],
            ['index' => 'keluar', 'label' => 'Total Keluar'],
            ['index' => 'last_keluar', 'label' => 'Terkhir Keluar'],
        ];
    }

    private function rows(): array
    {
        return [];
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.gudang.view-slow-moving', [
            'headers' => $this->headers(),
            'rows' => $rows,
            'paginator' => $rows,
        ]);
    }
}
