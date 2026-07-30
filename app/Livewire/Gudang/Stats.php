<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use App\Models\Gudang\Stok;
use Livewire\Attributes\Lazy;

#[Lazy]
class Stats extends Component
{

    public int $barangAkanHabis;
    public int $barangBelumDisusun;
    public string $barangFastMoving;
    public string $barangSlowMoving;

    // TODO : Gudang Index
    /**
     * [] barang fast moving
     * [] barang slow moving
     * 
     * Slow Moving t&c : 
     * - last distribusi > 1 bulan
     * - count distribusi < 10
     * - stok > 0
     * 
     * Fast Moving t&c :
     * - last distribusi < 1 bulan
     * - count distribusi > 10
     * - stok > 0
     */

    public function mount()
    {
        $stok = Stok::all();
        $this->barangAkanHabis = $stok->groupBy('barang_id')->filter(function ($group) {
            $totalStok = $group->sum('stok');
            return $totalStok < $group->first()->barang->min_stok;
        })->count();
        $this->barangBelumDisusun = $stok->where('penyimpanan_id', null)->count();
        $this->barangFastMoving = '-';
        $this->barangSlowMoving = '-';
    }



    public function render()
    {
        return view('livewire.gudang.stats');
    }
}
