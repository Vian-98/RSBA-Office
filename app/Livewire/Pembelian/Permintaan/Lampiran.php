<?php

namespace App\Livewire\Pembelian\Permintaan;

use Livewire\Component;

class Lampiran extends Component
{
    public ?array $lampirans;

    public function mount($lampiranRequest)
    {
        $this->lampirans = $lampiranRequest;
    }

    public function render()
    {
        return view('livewire.pembelian.permintaan.lampiran');
    }
}
