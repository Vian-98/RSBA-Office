<?php

namespace App\Livewire\Asset;

use App\Models\Assets\AssetBarang;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Details extends Component
{
    public AssetBarang $assetBarang;

    public function mount($id): void
    {
        $this->assetBarang = AssetBarang::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.asset.details');
    }
}
