<?php

namespace App\Livewire\Asset;

use App\Models\Assets\AssetBarang;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Logs extends Component
{
    public ?AssetBarang $assetBarang;
    public $logs;

    public function mount($id)
    {
        $this->assetBarang = AssetBarang::findOrFail($id);
        $this->logs = $this->assetBarang->logs()->latest()->get();
    }

    public function render()
    {
        return view('livewire.asset.logs');
    }
}
