<?php

namespace App\Livewire\Surat\BalasanPenelitian;

use App\Models\Surat\SuratBalasanPenelitian;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Details extends Component
{
    #[Locked]
    public ?SuratBalasanPenelitian $suratBalasanPenelitian = null;

    public function mount(?SuratBalasanPenelitian $suratBalasanPenelitian)
    {
        $this->suratBalasanPenelitian = $suratBalasanPenelitian;
    }

    public function render()
    {
        return view('livewire.surat.balasan-penelitian.details');
    }
}
