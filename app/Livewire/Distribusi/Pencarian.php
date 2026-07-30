<?php

namespace App\Livewire\Distribusi;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Isolate;
use App\Models\Gudang\Distribusi;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

#[Lazy]
#[Isolate]
class Pencarian extends Component
{
    public ?Distribusi $distribusi;

    function mount($distribusi)
    {
        $this->distribusi = $distribusi->load('ruangan');
    }

    private function details()
    {
        $data = $this->distribusi->load(['details', 'details.stoks']);

        return $data->details;
    }

    function pdf()
    {
        $this->dispatch('printNow');
        $distribusi = $this->distribusi;

        $pdf = PDF::loadView('livewire.distribusi.print-distribusi', ['distribusi' => $distribusi]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'invoice-distribusi.pdf');
    }

    public function render()
    {
        return view('livewire.distribusi.pencarian', ['details' => $this->details()]);
    }
}
