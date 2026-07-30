<?php

namespace App\Livewire\Master\Barang\Kategori;

use Throwable;
use App\Models\Master\BarangKategori;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public string $kategori, $deskripsi, $prefix;

    public $rules = [
        'kategori' => 'required|string|unique:um_kategori,nama',
        'deskripsi' => 'required|string'
    ];

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'nama' => $this->kategori,
                'deskripsi' => $this->deskripsi,
                'prefix' => $this->prefix ?? null
            ];
            BarangKategori::create($data);

            DB::commit();

            $this->dispatch('close-modal', id: 'modal-new-kategori');
            $this->dispatch('new-kategori-created');

            $this->toast()
                ->success('Berhasil', 'Kategori barang berhasil dibuat.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.barang.kategori.add');
    }
}
