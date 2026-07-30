<?php

namespace App\Livewire\Asset;

use Throwable;
use App\Models\Assets\AssetBarang;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Assets\AssetSpecs;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Specs extends Component
{
    use Interactions;

    public ?AssetBarang $assetBarang;
    public int $assetId;
    public array $specs = [];

    public function mount($id)
    {
        $this->assetId = $id;
        $this->assetBarang = AssetBarang::findOrFail($id);
        $specs = AssetSpecs::where('asset_id', $id)->get();
        // Check if the specs collection is empty
        if ($specs->isEmpty()) {
            $this->specs = [
                ['label' => '', 'value' => ''],
            ];
            return;
        }

        // Map the specs to a more usable format
        $this->specs = $specs->map(function ($item) {
            return [
                'label' => $item->label,
                'value' => $item->value,
            ];
        })->toArray();
    }

    public function addSpec()
    {
        $this->specs[] = ['label' => '', 'value' => ''];
    }

    public function removeSpec($index)
    {
        unset($this->specs[$index]);
        $this->specs = array_values($this->specs); // Re-index array
    }

    public function saveSpecs()
    {
        try {
            foreach ($this->specs as $spec) {
                AssetSpecs::updateOrCreate(
                    [
                        'asset_id' => $this->assetId,
                        'label' => $spec['label'],
                    ],
                    ['value' => $spec['value']]
                );
            }

            // send toast notifications
            $this->toast()->success(
                'Berhasil',
                'Spesifikasi berhasil disimpan.'
            )->send();
        } catch (Throwable $e) {

            // send toast notifications error
            $this->toast()->error(
                'Error',
                'Tidak dapat menyimpan, Error : ' . $e->getMessage()
            )->send();
        }
    }

    public function render()
    {
        return view('livewire.asset.specs');
    }
}
