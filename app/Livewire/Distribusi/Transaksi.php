<?php

namespace App\Livewire\Distribusi;

use Throwable;
use App\Livewire\Forms\Distribusi\TransaksiForm as DistibusiTransaksiForm;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use App\Models\Master\Barang as MasterBarang;
use App\Traits\BlocksTransactionDuringOpname;
use Livewire\Attributes\Lazy;

#[Lazy]
class Transaksi extends Component
{
    use BlocksTransactionDuringOpname;
    use Interactions;

    public DistibusiTransaksiForm $form;

    public $searchItem;

    // public $tgl_distribusi;
    // public $ruangan;
    // public $penerima;
    // public $sebagai;
    // public $keterangan;
    // public $cartItems = [];

    // protected $rules = [
    //     'tgl_distribusi' => 'required|date',
    //     'ruangan' => 'required',
    //     'penerima' => 'required',
    //     'sebagai' => 'required',
    //     'cartItems' => 'required|array|min:1',
    // ];

    // public function messages()
    // {
    //     return [
    //         'cartItems.required' => 'Minimal satu item untuk didistribusikan.',
    //         'cartItems.min' => 'Minimal satu item untuk didistribusikan.'
    //     ];
    // }

    public function getBarang($id): ?object
    {
        $barang = MasterBarang::with('satuan', 'stoks')
            ->where('id', $id)
            ->orWhere('sku', $id)
            ->withSum('stoks', 'stok')
            ->first();

        if ($barang) {
            $items = (object) [
                'id' => $barang->id,
                'sku' => $barang->sku,
                'nama' => $barang->nama,
                'satuan' => $barang->satuan->nama,
                'stok' => $barang->stoks_sum_stok,
            ];
            return $items;
        }

        return null;
    }

    // function submit()
    // {
    //     $this->validate();

    //     DB::beginTransaction();

    //     // dd($this->cartItems);

    //     try {
    //         //  distribusi record
    //         $distribusi = Distribusi::create([
    //             'tanggal' => $this->tgl_distribusi,
    //             'tujuan' => $this->ruangan,
    //             'pengirim' => auth()->id(),
    //             'penerima' => $this->penerima,
    //             'dist_as' => $this->sebagai,
    //             'keterangan' => $this->keterangan,
    //         ]);

    //         // Distribusi detail
    //         foreach ($this->cartItems as $item) {
    //             $totalStok = Stok::with('barang')
    //                 ->where('barang_id', $item['id'])
    //                 ->sum('stok');

    //             if ($totalStok < $item['jumlah']) {
    //                 throw new \Exception("Stok barang {$totalStok->barang->nama} tidak mencukupi.");
    //             }


    //             // [01] Set jumlah yang akan didistribusikan
    //             $jumlahDistribusi = $item['jumlah'];

    //             // [02] Ambil stok barang yang akan didistribusikan
    //             $stoks = Stok::where('barang_id', $item['id'])
    //                 ->where('stok', '>', 0)
    //                 ->orderBy('created_at')
    //                 ->get();

    //             // [03] Looping stok barang yang akan didistribusikan
    //             foreach ($stoks as $stok) {
    //                 // [03.01] Jika jumlah distribusi <= 0, maka selesai untuk stok barang ini
    //                 if ($jumlahDistribusi <= 0) {
    //                     break;
    //                 }

    //                 // [03.02] perbandingan jumlah distribusi dengan stok barang, nilai terkecil akan menjadi variabel $stokTerpakai
    //                 $stokTerpakai = min($stok->stok, $jumlahDistribusi);

    //                 // [03.03] Update stok barang dan kurangi stok barang dengan $stokTerpakai
    //                 Stok::where('id', $stok->id)
    //                     ->update(['stok' => $stok->stok - $stokTerpakai]);

    //                 // [04] Catat transaksi ke distribusi detail
    //                 $distribusiDet = DistribusiDetail::create([
    //                     'distribusi_id' => $distribusi->id,
    //                     'stok_id' => $stok->id,
    //                     'jml' => $stokTerpakai,
    //                 ]);
    //                 $jumlahDistribusi -= $stokTerpakai;

    //                 // [05] Jika distribusi sebagai asset, maka tambahkan ke asset
    //                 if ($distribusi->dist_as === 'asset') {
    //                     // ::note:: karena identifikasi asset adalah per item, maka barang yang didistribusikan dicatat per item
    //                     // [05.01] looping asset sesuai jumlah distribusi
    //                     for ($i = 1; $i <= $stokTerpakai; $i++) {
    //                         // [05.02] buat asset barang baru
    //                         AssetBarang::create([
    //                             'distribusi_det_id' => $distribusiDet->id,
    //                             'barang_id' => $item['id'],
    //                             'ruangan_id' => $distribusi['tujuan'],
    //                             'nilai' => $stok->harga_satuan,
    //                             'tanggal_catat' => $distribusi['tanggal'],
    //                         ]);
    //                     }
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         $this->dispatch('new-distribusi-created');
    //         // 
    //         $this->reset(['tgl_distribusi', 'ruangan', 'penerima', 'sebagai', 'keterangan', 'cartItems']);


    //         $this->toast()
    //             ->success('Berhasil', 'Barang berhasil didistribusikan.')
    //             ->send();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         $this->toast()
    //             ->error('Gagal', $e->getMessage() . $e->getLine() . ' Silahkan coba lagi.')
    //             ->send();
    //     }
    // }

    function submit()
    {
        $this->form->validate();

        try {
            $this->form->simpan();

            $this->dispatch('new-distribusi-created');

            //Reset Property
            $this->form->reset(
                [
                    'tgl_distribusi',
                    'ruangan',
                    'penerima',
                    'sebagai',
                    'keterangan',
                    'cartItems'
                ]
            );

            $this->toast()
                ->success('Berhasil', 'Barang berhasil didistribusikan.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error("⚠️ Terjadi Kesalahan", "<i>{$e->getMessage()}</i> <br> Silahkan coba lagi.")
                ->send();
        }
    }

    public function render()
    {
        if (!$this->blockIfOpnameActive()) {
            return view('components.opname-block');
        }

        return view('livewire.distribusi.transaksi');
    }
}
