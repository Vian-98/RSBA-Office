<?php

namespace App\Livewire\Master\Ruangan;

use Throwable;
use App\Models\Ruangan;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    public $nama;
    public ?Ruangan $ruangan;

    public $rules = [
        'nama' => 'required|string'
    ];

    public function mount($id)
    {
        $this->ruangan = Ruangan::findOrFail($id);
        $this->nama = $this->ruangan->nama;
    }

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->ruangan->nama = $this->nama;
            $this->ruangan->save();

            DB::commit();

            $this->toast()
                ->success('Berhasil', 'Ruangan berhasil dibaharui.')
                ->send();

            $this->dispatch('new-ruangan-updated');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Tidak Berhasil', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.ruangan.edit');
    }
}
