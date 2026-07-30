<?php

namespace App\Livewire\Distribusi;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Isolate;
use App\Models\Gudang\Distribusi;

#[Isolate]
class PrintDistribusi extends Component
{
    #[Locked]
    public ?Distribusi $distribusi;

    function mount($distribusi)
    {
        $this->distribusi = $distribusi;
    }

    public function render()
    {
        return view('livewire.distribusi.print-distribusi');
    }
}
