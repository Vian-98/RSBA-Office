<?php

namespace App\Livewire\Master\Barang;

use Throwable;
use App\Models\Master\Barang;
use App\Models\Master\BarangKategori;
use App\Models\Master\BarangSatuan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $sku;
    public bool $checkboxSku =  true, $bhp = false;
    public string $nama;
    public $tipe;
    public $kategori;
    public $satuan;
    public int $min_stok = 0;

    public $kategoriOptions;
    public $satuanOptions;

    public array $tipeOptions = [
        ['value' => 'umum', 'label' => 'Umum'],
        ['value' => 'asset', 'label' => 'Asset']
    ];

    public $rules = [
        'nama' => 'required|unique:um_barang,nama',
        'tipe' => 'required',
        'kategori' => 'required',
        'sku' => 'unique:um_barang,sku',
        'satuan' => 'required'
    ];

    public $konversiSatuans = [];

    public function addKonversi()
    {
        $this->konversiSatuans[] = ['satuan_id' => '', 'rasio' => ''];
    }

    public function removeKonversi($index)
    {
        unset($this->konversiSatuans[$index]);
        $this->konversiSatuans = array_values($this->konversiSatuans);
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $this->nama,
                'tipe' => $this->tipe,
                'kategori_id' => $this->kategori,
                'satuan_id' => $this->satuan,
                'sku' => $this->sku ?? $this->generateSKU($this->kategori),
                'bhp' => $this->bhp,
                'min_stok' => $this->min_stok
            ];
            $barang = Barang::create($data);

            // Simpan konversi satuan
            foreach ($this->konversiSatuans as $konversi) {
                if (!empty($konversi['satuan_id']) && !empty($konversi['rasio'])) {
                    $barang->konversiSatuans()->create([
                        'satuan_id' => $konversi['satuan_id'],
                        'rasio' => $konversi['rasio'],
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('new-barang-created');

            $this->dispatch('close-modal', id: 'modal-new-barang');

            $this->toast()
                ->success('Berhasil', 'Item barang berhasil dibuat.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }

    function generateSKU($kategori)
    {
        // get prefix kategori
        $barangKategori = BarangKategori::find($kategori);

        // get data barang [barang terkahir]
        $last = Barang::select('sku', 'kategori_id')
            ->where('kategori_id', $kategori)
            ->orderBy('id', 'desc')
            ->first();


        // buat nomor SKU
        $no = 1;
        if ($last) {
            [$_, $noLast] = explode('-', $last->sku); //explode by -

            $no = (int)substr($noLast, 0, 4) + 1; // subtring text start char ke 0, ambil 4 chars
        }
        $no = str_pad($no, 4, '0', STR_PAD_LEFT);
        $date = now()->format('my');

        // return data
        return "{$barangKategori->prefix}-{$no}{$date}";
    }

    public function updatedCheckboxSku()
    {
        $this->sku = '';
    }

    function mount($nama = '')
    {
        $this->nama = $nama;
        $this->kategoriOptions = BarangKategori::get();
        $this->satuanOptions = BarangSatuan::get();
    }

    public function render()
    {
        return view('livewire.master.barang.add');
    }
}
