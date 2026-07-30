<?php

namespace App\Livewire\Master\Barang\Satuan;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use App\Models\Master\BarangSatuan;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public ?BarangSatuan $satuan;
    public $nama;


    function mount($id)
    {
        $this->satuan = BarangSatuan::findOrFail($id);
        $this->nama = $this->satuan?->nama;
    }

    function submit()
    {
        $this->validate([
            'nama' => 'string|required'
        ]);

        DB::beginTransaction();
        try {

            $this->satuan->update([
                'nama' => $this->nama
            ]);

            DB::commit();

            $this->dispatch('close-modal', id: 'modal-edit-satuan');
            $this->dispatch('satuan-updated');

            $this->toast()
                ->success(
                    'Berhasil',
                    'Data satuan berhasil diubah.'
                )
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error(
                    'Gagal',
                    'Data satuan gagal diubah. Error : ' . $e->getMessage()
                )
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.barang.satuan.edit');
    }
}
