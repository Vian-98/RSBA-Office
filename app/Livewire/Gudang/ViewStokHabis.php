<?php

namespace App\Livewire\Gudang;

use App\Models\Gudang\Stok;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

#[Lazy]
class ViewStokHabis extends Component
{
    use WithPagination, WithoutUrlPagination;

    private function headers(): array
    {
        return [
            ['index' => 'barang', 'label' => 'Nama Barang'],
            ['index' => 'min_stok', 'label' => 'Stok Minimal'],
            ['index' => 'sisa_stok', 'label' => 'Sisa Stok'],
        ];
    }

    public function rows()
    {
        return Stok::all()
            ->groupBy('barang_id')
            ->filter(function ($group) {
                $totalStok = $group->sum('stok');
                return $totalStok < $group->first()->barang->min_stok;
            });

        // return $stok->values()->paginate(10);
    }


    public function render()
    {
        $dataRows = $this->rows();
        $rows = $dataRows->map(
            function ($group) {
                $firstItem = $group->first();
                return [
                    'barang' => $firstItem->barang->nama,
                    'min_stok' => $firstItem->barang->min_stok,
                    'sisa_stok' => $group->sum('stok')
                ];
            }
        )->toArray();

        return view('livewire.gudang.view-stok-habis', [
            'headers' => $this->headers(),
            'rows' => $rows,
            'paginator' => $dataRows
        ]);
    }
}
