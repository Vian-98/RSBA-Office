<?php

namespace App\Livewire\Master\Cuti;

use Throwable;
use App\Models\Surat\CutiJenis;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    #[Locked]
    public ?CutiJenis $cuti_jenis;

    public $nama;
    public $lama;
    public $periode;

    public $rules = [
        'nama' => 'required',
        'lama' => 'required'
    ];

    public function mount(?CutiJenis $cuti_jenis)
    {
        $this->cuti_jenis = $cuti_jenis;

        $this->nama = $cuti_jenis->nama;
        $this->lama = $cuti_jenis->lama;
        $this->periode = $cuti_jenis->periode;
    }


    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {

            $this->cuti_jenis->update([
                'nama' => $this->nama,
                'lama' => $this->lama,
                'periode' => $this->periode
            ]);
            DB::commit();

            $this->dispatch('updated');
            $this->toast()
                ->success('Berhasil update.')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();

            $this->toast()
                ->error('Tidak berhasil update.', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.cuti.edit');
    }
}
