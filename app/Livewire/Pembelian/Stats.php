<?php

namespace App\Livewire\Pembelian;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Gudang\Pembelian;
use Livewire\Attributes\Isolate;

#[Lazy]
#[Isolate]
class Stats extends Component
{
    // public ?Pembelian $pembelian;
    public $bulan, $tahun;
    public int $countTransaksiBulan, $nilaiPembelian, $onWaiting, $countBelumDibayar;
    public string $topItemDibeli, $topDistibutor, $higestItemValue;

    function mount()
    {
        $this->bulan = Carbon::today()->format('M Y');
        $this->tahun = Carbon::today()->format('Y');

        // get data this month
        $tahun = date('Y');
        $bulan = date('m');
        $pembelian = Pembelian::whereYear('tgl', $tahun)
            ->whereMonth('tgl', $bulan)
            ->get();

        // count total pembelian
        $this->countTransaksiBulan = $pembelian->count('id'); // total pembelian

        // waiting
        $this->onWaiting = $pembelian->where('status', 'waiting')->count();

        // jumlah total pembelian
        $this->nilaiPembelian = $pembelian->sum('total');


        // STATS => Item paling banyak dibeli
        $topItem = $pembelian->map(function ($det) {
            return $det->details->groupBy('barang_id')
                ->map(function ($barang) {
                    return [
                        'nama' => $barang->first()->barang->nama,
                        'count' => $barang->count(),
                        'sum' => $barang->sum('jumlah')
                    ];
                });
        })->flatten(1)->sortByDesc('sum')->first();
        $this->topItemDibeli = $topItem ? $topItem['nama'] : '-';
        // $this->countTopItemBeli = $topItem ? $topItem['count'] : 0;
        // $this->sumTopItemBeli = $topItem ? $topItem['sum'] : 0;


        // STAT : Supplier paling banyak
        $topDistibutor = $pembelian?->groupBy('supplier_id')
            ->map(function ($suplier_id) {
                return [
                    'count' => $suplier_id->count(),
                    'nama' => $suplier_id->first()->supplier->nama
                ];
            })->sortByDesc('count')->first();

        $this->topDistibutor = $topDistibutor ? $topDistibutor['nama'] : '-';
        // $this->totalTopDistributor = $topDistibutor ? $topDistibutor['count'] : '-';


        // STATS : Barang dengan harga tertinggi yang dibeli
        $this->higestItemValue = '-';
    }

    public function render()
    {
        return view('livewire.pembelian.stats');
    }
}
