<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Models\Surat\SuratPerintahTugas;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Details extends Component
{
    #[Locked]
    public ?SuratPerintahTugas $suratPerintahTugas = null;

    public function mount(?SuratPerintahTugas $suratPerintahTugas)
    {
        $this->suratPerintahTugas = $suratPerintahTugas;
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.details');
    }
}
