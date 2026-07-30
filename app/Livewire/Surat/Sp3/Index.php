<?php

namespace App\Livewire\Surat\Sp3;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Surat SP3')]
#[Lazy]
#[Isolate]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.surat.sp3.index');
    }
}
