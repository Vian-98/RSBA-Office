<?php

namespace App\Livewire\Maintenance\Work;

use App\Models\Maintenance\Work;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
#[Isolate]
class Report extends Component
{
    public ?object $work;

    public function mount($jadwalId)
    {
        $this->work = Work::with('jadwal', 'jadwal.asset', 'jadwal.teknisi.user')
            ->whereHas('jadwal', function ($query) use ($jadwalId) {
                $query->where('id', $jadwalId);
            })
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.maintenance.work.report');
    }
}
