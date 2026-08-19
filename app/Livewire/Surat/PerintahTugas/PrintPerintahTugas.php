<?php

namespace App\Livewire\Surat\PerintahTugas;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratPerintahTugas;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintPerintahTugas extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratPerintahTugas $suratPerintahTugas = null;

    public function mount(?SuratPerintahTugas $suratPerintahTugas): void
    {
        $this->suratPerintahTugas = SuratPerintahTugas::findOrFail($suratPerintahTugas->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-perintah-tugas')]
    public function refreshData(): void
    {
        $this->suratPerintahTugas = SuratPerintahTugas::find($this->suratPerintahTugas->id);
        $this->refreshDocstore();
    }

    public function render()
    {
        return view('livewire.surat.perintah-tugas.print-perintah-tugas');
    }
}
