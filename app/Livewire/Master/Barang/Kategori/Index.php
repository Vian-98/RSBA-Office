<?php

namespace App\Livewire\Master\Barang\Kategori;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Kategori Barang')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();

        return view('livewire.master.barang.kategori.index');
    }
}
