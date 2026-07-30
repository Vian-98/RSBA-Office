<?php

namespace App\Livewire\Distribusi;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Isolate;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

#[Lazy]
#[Isolate]
class ViewDetail extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $distribusi;

    function mount($distribusi)
    {
        $this->distribusi = $distribusi;
    }

    function headers(): array
    {
        return [
            ['index' => 'nama', 'label' => 'Nama Barang'],
            ['index' => 'jml', 'label' => 'Jumlah'],
            ['index' => 'satuan', 'label' => 'Satuan'],
        ];
    }

    function rows()
    {
        return $this->distribusi
            ->load(['details', 'details.stoks'])
            ->details()
            ->paginate(5);
    }

    public function render()
    {
        $headers = $this->headers();

        // details
        $details = $this->rows();

        // rows => mapping headers index
        $rows = $details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'nama' => $detail->stoks->barang->nama,
                'jml' => $detail->jml,
                'satuan' => $detail->stoks->barang->satuan->nama,
            ];
        })->toArray();

        // paginator
        // $this->paginator = $details;

        return view('livewire.distribusi.view-detail', [
            'headers' => $headers,
            'rows' => $rows,
            'paginator' => $details
        ]);
    }
}
