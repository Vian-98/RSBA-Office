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
    public ?AssetBarang $assetBarang = null;
    public ?Jadwal $jadwal = null;

    public $tanggal_mulai;

    public function mount($jadwalId)
    {
        $this->jadwal = Jadwal::with(['request.asset.barang', 'request.asset.ruangan', 'request.ruangan', 'work'])->findOrFail($jadwalId);
        $this->assetBarang = $this->jadwal->request?->asset;
        $this->tanggal_mulai = $this->jadwal->work?->mulai; //tanggal work order dimulai
    }

    public function render()
    {
        return view('livewire.maintenance.work.index');
    }
}
