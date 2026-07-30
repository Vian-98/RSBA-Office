<?php

namespace App\Livewire\Master\Ruangan;

use Throwable;
use App\Models\Ruangan;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama;

    public $rules = [
        'nama' => 'required|string'
    ];

    function submit()
    {
        $this->validate();

        try {
            Ruangan::create(
                ['nama' => $this->nama]
            );

            $this->toast()
                ->success('Berhasil', 'Ruangan baru berhasil dibuat.')
                ->send();

            $this->dispatch('new-ruangan-created');
        } catch (Throwable $e) {
            $this->toast()
                ->error('Failed', 'Error ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.ruangan.add');
    }
}
