<?php

namespace App\Livewire\Asset;

use App\Models\Assets\AssetBarang;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class Title extends Component
{
    #[Locked]
    public $assetBarang;

    public function mount(?AssetBarang $assetBarang)
    {
        $this->assetBarang = $assetBarang;
    }

    // #[Computed]
    // public function getComponentsAsset(): AssetBarang
    // {
    //     return $this->assetBarang->load('child');
    // }

    public function render()
    {
        return view('livewire.asset.title');
    }
}
