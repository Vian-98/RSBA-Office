<?php

namespace App\Livewire\Pembelian\Penerimaan;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class  Hibah extends Component
{
    public function render()
    {
        return view('livewire.pembelian.penerimaan.hibah');
    }
}
