<?php

namespace App\Livewire\Master\Barang;

use Throwable;
use App\Models\Master\Barang as MasterBarang;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use App\Models\Master\BarangSatuan;
use App\Models\Master\BarangKategori;
use TallStackUi\Traits\Interactions;

#[Lazy(isolate: false)]
class Edit extends Component
{
    use Interactions;

    public ?MasterBarang $barang;
    public string $sku, $nama, $tipe;
    public int $kategori, $satuan, $min_stok;
    public bool $bhp = false;

    public $kategoriOptions;
    public $satuanOptions;
    public array $tipeOptions = [
        ['value' => 'umum', 'label' => 'Umum'],
        ['value' => 'asset', 'label' => 'Asset']
    ];

    // public $rules = [];

    function mount($id)
    {
        $this->kategoriOptions = BarangKategori::get();
        $this->satuanOptions = BarangSatuan::get();

        $this->barang = MasterBarang::findOrFail($id);
        $this->sku = $this->barang->sku;
        $this->nama = $this->barang->nama;
        $this->tipe = $this->barang?->tipe;
        $this->kategori = $this->barang?->kategori_id;
        $this->satuan = $this->barang?->satuan_id;
        $this->min_stok = $this->barang?->min_stok;
        $this->bhp = $this->barang->bhp;
        
        $this->konversiSatuans = $this->barang->konversiSatuans->map(function ($k) {
            return ['id' => $k->id, 'satuan_id' => $k->satuan_id, 'rasio' => $k->rasio];
        })->toArray();

        // $this->rules = [
        //     'nama' => "required|unique:um_barang,nama,{$this->barang->id}",
        //     'tipe' => 'required',
        //     'kategori' => 'required',
        //     'satuan' => 'required'
        // ];
    }

    public function rules()
    {
        return [
            'nama' => "required|unique:um_barang,nama,{$this->barang->id}",
            'tipe' => 'required',
            'kategori' => 'required',
            'sku' => "required|unique:um_barang,sku,{$this->barang->id}",
            'satuan' => 'required'
        ];
    }

    public $konversiSatuans = [];

    public function addKonversi()
    {
        $this->konversiSatuans[] = ['id' => null, 'satuan_id' => '', 'rasio' => ''];
    }

    public function removeKonversi($index)
    {
        unset($this->konversiSatuans[$index]);
        $this->konversiSatuans = array_values($this->konversiSatuans);
    }

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->barang->update([
                'nama' => $this->nama,
                'tipe' => $this->tipe,
                'kategori_id' => $this->kategori,
                'satuan_id' => $this->satuan,
                'sku' => $this->sku,
                'bhp' => $this->bhp,
                'min_stok' => $this->min_stok
            ]);

            // Sync konversi satuan
            $this->barang->konversiSatuans()->delete();
            foreach ($this->konversiSatuans as $konversi) {
                if (!empty($konversi['satuan_id']) && !empty($konversi['rasio'])) {
                    $this->barang->konversiSatuans()->create([
                        'satuan_id' => $konversi['satuan_id'],
                        'rasio' => $konversi['rasio'],
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('close-modal', id: 'modal-edit-barang');
            $this->dispatch('barang-updated');

            $this->toast()
                ->success('Berhasil', 'Item barang berhasil diubah.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagal', "Item barang gagal diubah. Error: {$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.barang.edit');
    }
}
