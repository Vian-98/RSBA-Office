<?php

namespace App\Livewire\Surat\Cuti;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Izin dan Cuti')]
class Index extends Component
{
    use AuthorizesFromRoute;

    #[Url]
    public string $tab = 'izin-cuti';

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.surat.cuti.index');
    }
}
