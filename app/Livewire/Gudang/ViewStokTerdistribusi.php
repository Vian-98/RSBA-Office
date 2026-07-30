<?php

namespace App\Livewire\Gudang;

use App\Models\Gudang\DistribusiDetail;
use App\Models\Gudang\Stok;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

#[Lazy]
#[Isolate]
class ViewStokTerdistribusi extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Locked]
    public $stokId;
    #[Locked]
    public $stoks;

    function mount($stokId)
    {
        $this->stokId = $stokId;

        $this->stoks = Stok::with(['penerimaanDet', 'penerimaanDet.pembelianDet', 'penerimaanDet.penerimaan'])
            ->find($stokId);
    }


    function headers(): array
    {
        return [
            ['index' => 'distribusi_id', 'label' => 'Distribusi Id'],
            ['index' => 'distribusi_id', 'label' => 'Tgl'],
            ['index' => 'distribusi_id', 'label' => 'Jumlah'],
            ['index' => 'distribusi_id', 'label' => 'Ke'],
        ];
    }

    function rows()
    {
        return DistribusiDetail::with(
            ['distribusi', 'distribusi.ruangan']
        )
            ->when($this->stokId, function ($query, $stokId) {
                $query->where('stok_id', $stokId);
            })
            // ->where('stok_id', $this->stokId)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.gudang.view-stok-terdistribusi', [
            'distribusiDetail' => $this->rows()
        ]);
    }
}
