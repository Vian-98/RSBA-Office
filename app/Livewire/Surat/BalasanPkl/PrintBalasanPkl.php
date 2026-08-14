<?php

namespace App\Livewire\Surat\BalasanPkl;

use App\Models\Surat\SuratBalasanPkl;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PrintBalasanPkl extends Component
{
    #[Locked]
    public ?SuratBalasanPkl $suratBalasanPkl = null;

    public function mount(?SuratBalasanPkl $suratBalasanPkl)
    {
        $this->suratBalasanPkl = $suratBalasanPkl;
    }

    public function render()
    {
        return view('livewire.surat.balasan-pkl.print-balasan-pkl');
    }
}
