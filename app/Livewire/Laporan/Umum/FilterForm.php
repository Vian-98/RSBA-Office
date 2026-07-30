<?php

namespace App\Livewire\Laporan\Umum;

use Livewire\Component;

class FilterForm extends Component
{
    public string $type;
    public array $periode = [];
    public ?array $items = [];
    public $vendor = null;
    public $jenis = null;
    public $ruangan = null;
    public $optionsFaktur = [
        ['label' => 'Alat Kesehatan', 'value' => 'alkes'],
        ['label' => 'BHP', 'value' => 'bhp'],
        ['label' => 'Umum', 'value' => 'umum'],
    ];

    public function mount(

        string $type,

    ): void {
        $this->type = $type;
        $this->periode = [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString(),
        ];
    }

    public function cari(): void
    {
        $this->validate([
            'periode' => 'required|array',
        ]);

        $event = $this->type === 'pembelian' ? 'filterPembelianLaporan' : 'filterDistribusiLaporan';

        $data = [
            'periode' => $this->periode,
            'items' => $this->items,
        ];

        if ($this->type === 'pembelian') {
            $data['vendor'] = $this->vendor;
            $data['jenis'] = $this->jenis;
        } else {
            $data['ruangan'] = $this->ruangan;
        }
        // Dispatch event to component (Pembelian / Distibusi)
        $this->dispatch($event, data: $data);
    }


    public function render()
    {
        return view('livewire.laporan.umum.filter-form');
    }
}
