<?php

namespace App\Livewire\Master\Bagian;

use Throwable;
use Livewire\Component;
use App\Models\Sdm\Bagian;
use Livewire\Attributes\Lazy;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama;
    public $group;

    public $groups = [
        ['value' => 'manajemen', 'label' => 'Manajemen'],
        ['value' => 'medis', 'label' => 'Medis'],
        ['value' => 'penunjang', 'label' => 'Penunjang'],
        ['value' => 'non_medis', 'label' => 'Non Medis']
    ];

    protected $rules = [
        'nama' => 'required|string'
    ];

    function submit()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'group' => $this->group
        ];

        try {
            Bagian::create($data);

            $this->dispatch('new-bagian-created');

            $this->toast()
                ->success('Berhasil', 'Bagian / Divisi baru berhasil dibuat.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->error('Error', 'Failed : ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.master.bagian.add');
    }
}
