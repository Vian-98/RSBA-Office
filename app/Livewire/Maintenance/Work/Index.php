<?php

namespace App\Livewire\Maintenance\Work;

use App\Models\Assets\AssetBarang;
use App\Models\Maintenance\Jadwal;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
#[Isolate]
class Index extends Component
{
    public ?AssetBarang $assetBarang;

    public $tanggal_mulai;

    public function mount($jadwalId)
    {
        $jadwal = Jadwal::findOrFail($jadwalId);
        $this->assetBarang = $jadwal->request->asset;
        $this->tanggal_mulai = $jadwal->work->mulai; //tanggal work order dimulai
    }

    public function render()
    {
        return view('livewire.maintenance.work.index');
    }
}
