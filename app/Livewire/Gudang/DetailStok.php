<?php

namespace App\Livewire\Gudang;

use App\Models\Master\Barang;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

#[Lazy]
class DetailStok extends Component
{
    use WithPagination, WithoutUrlPagination;

    // public ?Barang $barang;
    // #[Locked]
    public ?Barang $barang;
    public $stok;
    public $stokId = null;

    public function mount(Barang $barang, $stok = false)
    {
        $this->barang = $barang;
        $this->stok = $stok;
    }


    private function getStoks()
    {
        return $this->barang->stoks()
            ->when($this->stok, function ($query) {
                // $query->with(['distribusiDetails' => function ($query) {
                //     $query->orderBy('id', 'desc');
                // }])
                $query->with('distribusiDetails')
                    ->where('stok', '>', 0);
            })
            ->with('penerimaanDet.penerimaan')
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function getPenyimpanansProperty()
    {
        return \App\Models\Master\BarangPenyimpanan::with('lemaris')->get();
    }

    public function setLokasi($stokId, $penyimpananId, $lemariId = null)
    {
        $stok = \App\Models\Gudang\Stok::find($stokId);
        if ($stok) {
            $stok->penyimpanan_id = $penyimpananId;
            $stok->lemari_id = $lemariId ?: null;
            $stok->save();
            
            $this->dispatch('close-modal', id: 'modal-set-lokasi-' . $stokId);
        }
    }

    public function pecahStok($stokId, $qtyPecahan, $penyimpananId, $lemariId = null)
    {
        \Illuminate\Support\Facades\Validator::make(
            ['qtyPecahan' => $qtyPecahan],
            ['qtyPecahan' => 'required|numeric|min:1']
        )->validate();

        $stokAsli = \App\Models\Gudang\Stok::find($stokId);
        
        if (!$stokAsli) return;
        
        if ($qtyPecahan >= $stokAsli->stok) {
            $this->dispatch('close-modal', id: 'modal-pecah-stok-' . $stokId);
            // Too much quantity or equal
            return;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $stokSebelumLama = $stokAsli->stok;

            // 1. Kurangi stok asli
            $stokAsli->stok -= $qtyPecahan;
            $stokAsli->save();

            // 2. Catat Mutasi Keluar dari stok asli
            \App\Models\Gudang\StokMutasi::create([
                'barang_id' => $stokAsli->barang_id,
                'stok_id' => $stokAsli->id,
                'jenis_mutasi' => 'TRANSFER_KELUAR', // Custom type for splitting out
                'jumlah' => -$qtyPecahan,
                'stok_sebelum' => $stokSebelumLama,
                'stok_sesudah' => $stokAsli->stok,
                'harga_satuan' => $stokAsli->harga_satuan,
                'keterangan' => 'Pecah stok ke lokasi lain',
                'referensi_type' => \App\Models\Gudang\Stok::class,
                'referensi_id' => $stokAsli->id,
                'created_by' => auth()->id() ?? 1
            ]);

            // 3. Buat baris stok baru
            $stokBaru = $stokAsli->replicate();
            $stokBaru->stok = $qtyPecahan;
            $stokBaru->penyimpanan_id = $penyimpananId;
            $stokBaru->lemari_id = $lemariId ?: null;
            $stokBaru->save();

            // 4. Catat Mutasi Masuk untuk stok baru
            \App\Models\Gudang\StokMutasi::create([
                'barang_id' => $stokBaru->barang_id,
                'stok_id' => $stokBaru->id,
                'jenis_mutasi' => 'TRANSFER_MASUK', // Custom type for splitting in
                'jumlah' => $qtyPecahan,
                'stok_sebelum' => 0,
                'stok_sesudah' => $qtyPecahan,
                'harga_satuan' => $stokBaru->harga_satuan,
                'keterangan' => 'Hasil pecahan dari stok sebelumnya',
                'referensi_type' => \App\Models\Gudang\Stok::class,
                'referensi_id' => $stokAsli->id,
                'created_by' => auth()->id() ?? 1
            ]);

            \Illuminate\Support\Facades\DB::commit();
            $this->dispatch('close-modal', id: 'modal-pecah-stok-' . $stokId);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $th;
        }
    }

    public function render()
    {
        return view('livewire.gudang.detail-stok', [
            'stoks' => $this->getStoks(),
            'penyimpanans' => $this->penyimpanans
        ]);
    }
}
