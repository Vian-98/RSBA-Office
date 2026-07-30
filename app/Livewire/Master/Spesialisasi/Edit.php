<?php

namespace App\Livewire\Master\Spesialisasi;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\DokterSpesialisasi;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

class Edit extends Component
{
    use Interactions;

    public ?DokterSpesialisasi $spesialisasi;
    public string $nama;
    public ?string $singkatan = null;

    public $rules = [
        'nama' => 'required|string'
    ];

    function mount($id)
    {
        $this->spesialisasi = DokterSpesialisasi::find($id);

        if ($this->spesialisasi) {
            $this->nama = $this->spesialisasi->nama;
            $this->singkatan = $this->spesialisasi->singkatan;
        }
    }

    function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->spesialisasi->nama = $this->nama;
            $this->spesialisasi->singkatan = $this->singkatan;
            $this->spesialisasi->update();

            DB::commit();

            $this->dispatch('spesialisasi-updated');

            $this->toast()
                ->success('Berhasil', 'Data diupdate.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error :' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.spesialisasi.edit');
    }
}
