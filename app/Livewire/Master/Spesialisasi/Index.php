<?php

namespace App\Livewire\Master\Spesialisasi;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Spesialisasi Dokter')]
#[Lazy(isolate: false)]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.spesialisasi.index');
    }
}
