<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Livewire\Surat\Traits\HasDocstoreSourceOfTruth;
use App\Models\Surat\SuratBalasanPkl;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintBalasanPkl extends Component
{
    use HasDocstoreSourceOfTruth;

    #[Locked]
    public ?SuratBalasanPkl $suratBalasanPkl = null;

    public function mount(?SuratBalasanPkl $suratBalasanPkl): void
    {
        $this->suratBalasanPkl = SuratBalasanPkl::findOrFail($suratBalasanPkl->id);
        $this->loadFromDocstore();
    }

    #[On('refresh-table-balasan-pkl')]
    public function refreshData(): void
    {
        $this->suratBalasanPkl = SuratBalasanPkl::find($this->suratBalasanPkl->id);
        $this->refreshDocstore();
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.print-balasan-pkl');
    }
}
