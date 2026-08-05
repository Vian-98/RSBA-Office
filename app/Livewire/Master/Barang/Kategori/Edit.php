<?php

namespace App\Livewire\Master\Barang\Kategori;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use App\Models\Master\BarangKategori;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?BarangKategori $kategori;
    public string $nama = '', $deskripsi = '', $prefix = '';

    public function mount($id)
    {
        $this->kategori = BarangKategori::findOrFail($id);
        $this->nama = $this->kategori?->nama ?? '';
        $this->deskripsi = $this->kategori?->deskripsi ?? '';
        $this->prefix = $this->kategori->prefix ?? '';
    }

    function submit()
    {
        $this->validate([
            'nama' => 'required|string|unique:um_kategori,nama,' . $this->kategori->id,
            'deskripsi' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $this->kategori->update([
                'nama' => $this->nama,
                'deskripsi' => $this->deskripsi,
                'prefix' => $this->prefix
            ]);
            DB::commit();

            $this->dispatch('kategori-barang-updated');
            $this->dispatch('close-modal', id: 'modal-edit-kategori-barang');

            $this->toast()
                ->success('Berhasil', 'Kategori barang berhasil diubah.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->toast()
                ->error('Gagaal', "Kategori barang gagal diubah. Error: {$e->getMessage()}")
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.barang.kategori.edit');
    }
}
