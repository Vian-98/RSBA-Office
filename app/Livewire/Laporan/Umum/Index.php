<?php

namespace App\Livewire\Laporan\Umum;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Laporan Umum')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;

    public string $tab = "Pembelian";

    // public function mount()
    // {
    //     $this->tab = 'Pembelian';
    // }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.laporan.umum.index');
    }
}
