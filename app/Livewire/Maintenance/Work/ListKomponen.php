<?php

namespace App\Livewire\Maintenance\Work;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ListKomponen extends Component
{

    public $assetBarang;

    public $headers = [
        [
            'index' => 'nama',
            'label' => 'Nama'
        ],
        [
            'index' => 'kode',
            'label' => 'Kode Asset'
        ],
        [
            'index' => 'tanggal_catat',
            'label' => 'Tanggal Dicatat'
        ],
        [
            'index' => 'status',
            'label' => 'Status'
        ],
    ];

    public function mount($assetBarang)
    {
        $this->assetBarang = $assetBarang;
    }

    // #[Computed]
    public function komponens()
    {
        return $this->assetBarang->child->map(function ($item) {
            return [
                'nama' => $item->barang->nama,
                'kode' => $item->kode,
                'tanggal_catat' => $item->tanggal_catat,
                'status' => ucwords($item->status),
            ];
        });
        // return $this->assetBarang->komponen;
    }

    public function render()
    {
        return view('livewire.maintenance.work.list-komponen');
    }
}
