<?php

namespace App\Livewire\Forms\Distribusi;

use Throwable;
use Exception;
use Livewire\Form;
use App\Models\Gudang\Stok;
use App\Models\Master\Barang;
use App\Models\Gudang\Distribusi;
use App\Models\Assets\AssetBarang;
use Illuminate\Support\Facades\DB;
use App\Models\Gudang\DistribusiDetail;
use App\Models\Gudang\StokMutasi;

class TransaksiForm extends Form
{
    // use Interactions;

    // Var Distribusi
    public ?string $tgl_distribusi = null;
    public ?int $ruangan = null, $penerima = null;
    public ?string $sebagai = null, $keterangan = null;

    public ?int $lastDistribusiId;

    public ?object $lastDisribusi;

    public ?array $cartItems = [];

    protected function rules(): array
    {
        return [
            'tgl_distribusi' => ['required'],
            'ruangan' => ['required'],
            'penerima' => ['required'],
            'sebagai' => ['required'],
            'cartItems' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'cartItems.required' => 'Minimal satu item untuk didistribusikan.',
            'cartItems.min' => 'Minimal satu item untuk didistribusikan.'
        ];
    }

    public function fills(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function simpan(): void
    {
        DB::beginTransaction();
        try {
            $distribusi = $this->createDistribusi();

            // each item distribusi
            foreach ($this->cartItems as $item) {
                $this->prosesDistribusiItem($distribusi, $item);
            }

            DB::commit();
            $distribusi->load('details');
            $this->lastDistribusiId = $distribusi->id;
            $this->lastDisribusi = $distribusi;
        } catch (Throwable $th) {
            DB::rollBack();
            throw new Exception($th->getMessage());
        }
    }

    protected function createDistribusi(): ?Distribusi
    {
        $distribusi =  Distribusi::create([
            'tanggal' => $this->tgl_distribusi,
            'tujuan' => $this->ruangan,
            'pengirim' => auth()->id(),
            'penerima' => $this->penerima,
            'dist_as' => $this->sebagai,
            'keterangan' => $this->keterangan ?? null,
        ]);

        return $distribusi;
    }


    // PROSES DISTRIBUSI ITEM
    protected function prosesDistribusiItem($distribusi, $item)
    {
        $this->validateStok($item); //cek apakah stok mencukupi

        $remainingQty = $item['jumlah']; // Qty yang akan dikeluarkan
        $stoks = $this->getStokTersedia($item['id']); //Get all data stok yang tersedia

        foreach ($stoks as $stok) {

            if ($remainingQty <= 0) break;

            $stokSebelum = $stok->stok; //stok sebelum dilakukan transaksi

            // Perbandingan jumlah distribusi dengan stok barang, nilai terkecil akan menjadi variabel $stokTerpakai
            $stokTerpakai = min($stok->stok, $remainingQty);

            // update Stok Data
            $this->updateStok(
                stoks: $stok,
                stokDipakai: $stokTerpakai
            );

            // Create detail transaksi distribusi
            $distribusiDetail = $this->createDistribusiDetails(
                distribusiId: $distribusi->id,
                stokId: $stok->id,
                qty: $stokTerpakai
            );
            // decrement jumlah remaining quantity
            $remainingQty -= $stokTerpakai;

            // Mutasi stok [jenis: DISTRIBUSI]
            $this->createMutasi(
                stok: $stok,
                jumlah: $stokTerpakai,
                stokSebelum: $stokSebelum,
                stokTerpakai: $stokTerpakai,
                distribusiDetail: $distribusiDetail
            );


            // If distribusi sebagai asset
            if ($this->sebagai === 'asset') {
                // Set asset data
                $this->createAssetItem(
                    distribusiDetId: $distribusiDetail->id,
                    barangId: $item['id'],
                    ruanganId: $this->ruangan,
                    nilai: $stok->harga_satuan,
                    tanggal_catat: $this->tgl_distribusi,
                    qty: $stokTerpakai
                );
            }
        }
    }


    protected function validateStok($item)
    {
        $barang = Barang::withSum('stoks', 'stok')
            ->findOrFail($item['id']);

        if ($barang->stoks_sum_stok < $item['jumlah'] or $barang->stoks_sum_stok == 0 or  $barang->stoks_sum_stok == null) {
            throw new Exception("Stok barang <b>{$barang->nama}</b> tidak mencukupi.");
        }
    }

    // semua data stok barang yang tersedia
    protected function getStokTersedia($barang_id): ?Object
    {
        return Stok::where('barang_id', $barang_id)
            ->where('stok', '>', 0)
            ->orderBy('created_at')
            ->get();
    }

    // Update Stok Data 
    protected function updateStok($stoks, $stokDipakai): void
    {
        $stoks->update(['stok' => $stoks->stok - $stokDipakai]);
    }


    protected function createDistribusiDetails($distribusiId, $stokId, $qty): ?DistribusiDetail
    {
        return DistribusiDetail::create([
            'distribusi_id' => $distribusiId,
            'stok_id' => $stokId,
            'jml' => $qty
        ]);
    }

    protected function createMutasi(object $stok, int $jumlah, int $stokSebelum, int $stokTerpakai, object $distribusiDetail): ?StokMutasi
    {
        $stokSesudah = $stokSebelum - $stokTerpakai;
        $keterangan = sprintf(
            "Distribusi: {id: %s, oleh: %s}\n" .
                "Stok: {id: %d, awal: %s, akhir: %s }\n",
            $distribusiDetail->distribusi_id,
            $distribusiDetail->distribusi->PengirimNama,
            $stok->id,
            $stokSebelum,
            $stokSesudah
        );

        $mutasi =  StokMutasi::create([
            'stok_id' => $stok->id,
            'barang_id' => $stok->barang_id,
            'jenis_mutasi' => 'DISTRIBUSI',
            'jumlah' => abs($jumlah),
            'multiplier' => -1,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'keterangan' => $keterangan,
            'referensi_type' => DistribusiDetail::class,
            'referensi_id' => $distribusiDetail->id,
            'created_by' => auth()->user()->id,
            'is_posted' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $mutasi;
    }

    protected function createAssetItem($distribusiDetId, $barangId, $ruanganId, $nilai, $tanggal_catat, $qty): void
    {
        for ($i = 1; $i <= $qty; $i++) {
            AssetBarang::create([
                'distribusi_det_id' => $distribusiDetId,
                'barang_id' => $barangId,
                'ruangan_id' => $ruanganId,
                'nilai' => $nilai,
                'tanggal_catat' => $tanggal_catat
            ]);
        }
    }
}
