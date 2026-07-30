<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use App\Models\Gudang\Pembelian;
use Livewire\Attributes\Isolate;

#[Lazy]
#[Isolate]
class Cari extends Component
{
    public ?Pembelian $pembelian = null;

    public string $modalPreffix;

    public $search;

    public function mount()
    {
        $this->modalPreffix = 'modal-search-' . Str::random(5);
    }

    public function updatedSearch($value)
    {
        $this->pembelian = Pembelian::where('no', $value)->first();

        if (!$this->pembelian) {
            return;
        }

        if ($this->pembelian->status === 'selesai') {
            $this->dispatch('open-modal', id: $this->modalPreffix . '-detail');
        } else {
            $this->dispatch('open-modal', id: $this->modalPreffix . '-terima');
        }

        // reset modal open
    }

    function placeholder()
    {
        return <<<HTML
        <div class="flex w-11/12 flex-col">
            <div class="flex animate-pulse flex-col gap-3">
            
                <div class="space-y-3">
                    <div class="h-5 w-8/12 rounded-full bg-neutral-300"></div> 
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.pembelian.cari');
    }
}
