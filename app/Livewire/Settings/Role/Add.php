<?php

namespace App\Livewire\Settings\Role;

use Throwable;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Add extends Component
{
    use Interactions;

    public $nama;
    public $guard;

    public $rules = [
        'nama' => 'required|string'
    ];

    public function submit()
    {
        $this->validate();

        try {
            Role::create([
                'name' => $this->nama,
                'guard_name' => $this->guard ?? 'web'
            ]);

            $this->dispatch('new-role-created');

            $this->toast()
                ->success('Berhasil', 'Role baru berhasil dibuat.')
                ->send();
        } catch (Throwable $e) {
            $this->toast()
                ->success('Failed', 'Error : ' . $e->getMessage())
                ->send();
        }
    }


    public function render()
    {
        return view('livewire.settings.role.add');
    }
}
