<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Surat Balasan PKL')]
#[Lazy]
#[Isolate]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.surat.balasan-pkl.index');
    }
}
