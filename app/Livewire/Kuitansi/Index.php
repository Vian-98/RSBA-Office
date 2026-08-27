<?php

namespace App\Livewire\Kuitansi;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Kuitansi')]
#[Lazy]
#[Isolate]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.kuitansi.index');
    }
}
