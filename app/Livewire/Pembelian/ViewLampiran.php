<?php

namespace App\Livewire\Pembelian;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ViewLampiran extends Component
{
    public ?array $lampirans;

    public function mount($lampirans)
    {
        $this->lampirans = $lampirans;
    }

    public function render()
    {
        return view('livewire.pembelian.view-lampiran');
    }
}
