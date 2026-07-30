<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Gudang\Pembelian;

#[Lazy]
class Detail extends Component
{

    public array $headers, $rows;

    public ?Pembelian $pembelian;
    public $penerimaan;

    function mount($id)
    {
        $this->pembelian = Pembelian::with([
            'supplier',
            'details',
            'details.barang.satuan',
            'details.terimas.stoks',
            'details.terimas.penerimaan.user'
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.pembelian.detail');
    }
}
