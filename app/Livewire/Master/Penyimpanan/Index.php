<?php

namespace App\Livewire\Master\Penyimpanan;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lokasi Penyimpanan')]
#[Lazy(isolate: false)]
class Index extends Component
{
    use AuthorizesFromRoute;


    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.penyimpanan.index');
    }
}
