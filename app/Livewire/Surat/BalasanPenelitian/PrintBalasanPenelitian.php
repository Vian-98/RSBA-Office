<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratBalasanPenelitian;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintBalasanPenelitian extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratBalasanPenelitian $suratBalasanPenelitian = null;

    public function mount(?SuratBalasanPenelitian $suratBalasanPenelitian): void
    {
        $this->suratBalasanPenelitian = SuratBalasanPenelitian::findOrFail($suratBalasanPenelitian->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-balasan-penelitian')]
    public function refreshData(): void
    {
        $this->suratBalasanPenelitian = SuratBalasanPenelitian::find($this->suratBalasanPenelitian->id);
        $this->refreshDocstore();
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.print-balasan-penelitian');
    }
}
