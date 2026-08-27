<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Surat Perintah Tugas')]
#[Lazy]
#[Isolate]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.surat.perintah-tugas.index');
    }
}
