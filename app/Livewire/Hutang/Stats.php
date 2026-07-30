<?php

namespace App\Livewire\Hutang;

use App\Models\Gudang\Pembelian;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
#[Isolate]
class Stats extends Component
{
    public $periode;
    public $vendor;

    public float $hutang, $dibayar, $belumDibayar;


    public function mount($periode, $vendor)
    {
        $this->periode = $periode;
        $this->vendor = $vendor;

        $this->stats();
    }

    function getData()
    {
        return Pembelian::with('supplier')
            ->when($this->periode, function ($query) {
                $query->whereMonth('tgl', date('m', strtotime($this->periode)))
                    ->whereYear('tgl', date('Y', strtotime($this->periode)));
            })
            ->when($this->vendor, function ($query) {
                $query->where('supplier_id', $this->vendor);
            });
    }


    public function stats()
    {
        $pembelian = $this->getData();
        $this->hutang = $pembelian->sum('total');
        // sudah dibayar
        $this->dibayar = (clone $pembelian)->where('status_pembayaran', 'lunas')->sum('total');
        // belum dibayar
        // $this->belumDibayar = $this->hutang - $this->dibayar;
        $this->belumDibayar = (clone $pembelian)->where('status_pembayaran', null)->orWhere('status_pembayaran', 'tempo')->sum('total');
    }

    function updatedPeriode()
    {
        $this->stats();
    }

    function updatedVendor()
    {
        $this->stats();
    }

    public function render()
    {
        return view('livewire.hutang.stats');
    }
}
