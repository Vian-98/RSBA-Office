<?php

namespace App\Livewire\Master\Barang\Satuan;

use Throwable;
use App\Models\Master\BarangSatuan;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public string $nama, $deskripsi = '';

    public $rules = [
        'nama' => 'string|required'
    ];

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            BarangSatuan::create([
                'nama' => $this->nama,
                'deskripsi' => $this->deskripsi
            ]);
            DB::commit();

            $this->dispatch('satuan-created');
            $this->dispatch('close-modal', id: 'modal-new-satuan');

            $this->toast()
                ->success(
                    'Berhasil',
                    'Data satuan berhasil ditambahkan.'
                )
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error(
                    'Gagal',
                    'Data satuan gagal ditambahkan. Error : ' . $e->getMessage()
                )
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.barang.satuan.add');
    }
}
