<?php

namespace App\Livewire\Distribusi;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Isolate;
use App\Models\Gudang\Distribusi;
use Livewire\Attributes\Lazy;

#[Lazy]
#[Isolate]
class Stats extends Component
{
    public string $periode;

    public float $totalItemTerdistribusi, $totalTopItemTerdistribusi, $totalTopTujuanTerdistribusi, $totalHigestItemTerdistribusi;
    public string $topItemTerdistribusi;
    public string $topTujuanTerdistribusi;
    public string $higestItemTerdistribusi;

    function mount()
    {
        // periode todaay
        $this->periode = Carbon::today()->format('M Y');

        // get data
        $tahun = date('Y');
        $bulan = date('m');
        $distribusi = Distribusi::with(['details'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();


        // STATS [1]
        // Total item terdistibusi dari gudang
        $this->totalItemTerdistribusi = $distribusi?->sum(function ($d) {
            return $d->details->sum('jml');
        });


        // STATS [2]
        // nama barang paling banyak keluar
        $itemTop = $distribusi?->map(function ($d) {
            return $d->details
                ->sortByDesc('id')
                ->map(function ($detail) {
                    return [
                        'jml' => $detail->jml,
                        'nama' => $detail->stoks->barang->nama
                    ];
                });
        })->flatten(1)->sortByDesc('jml')->first();
        $this->topItemTerdistribusi = $itemTop ? $itemTop['nama'] : '-';
        $this->totalTopItemTerdistribusi = $itemTop ? $itemTop['jml'] : 0;


        // STATS [3]
        // Unit tujuan paling banyak transaksi
        $topUnit = $distribusi?->groupBy('tujuan')
            ->map(function ($d) {
                return [
                    'count' => $d->count(),
                    'nama' => $d->first()->ruangan->nama
                ];
            })->sortByDesc('count')->first();
        $this->topTujuanTerdistribusi = $topUnit ? $topUnit['nama'] : '-';
        $this->totalTopTujuanTerdistribusi = $topUnit ? $topUnit['count'] : 0;


        // STATS [4]
        // Item yg terdistribusi dengan harga tertinggi
        $maxHarga = $distribusi?->map(function ($d) {
            return $d->details
                // ->sortByDesc('id')
                ->map(function ($detail) {
                    return [
                        'harga_satuan' => $detail->stoks->harga_satuan,
                        'nama' => $detail->stoks->barang->nama
                    ];
                });
        })->flatten(1)->sortByDesc('harga_satuan')->first();
        $this->higestItemTerdistribusi = $maxHarga ? $maxHarga['nama'] : '-';
        $this->totalHigestItemTerdistribusi = $maxHarga ? $maxHarga['harga_satuan'] : 0;
    }

    public function render()
    {
        return view('livewire.distribusi.stats');
    }
}
