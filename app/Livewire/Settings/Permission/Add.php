<?php

namespace App\Livewire\Settings\Permission;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use TallStackUi\Traits\Interactions;

class Add extends Component
{
    use Interactions;

    public $nama, $guard;

    public $rules = [
        'nama' => 'required|string|unique:permissions,name'
    ];

    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            Permission::findOrCreate($this->nama, $this->guard ?? 'web');
            $this->dispatch('new-permission-created');

            DB::commit();

            $this->toast()
                ->success('Disimpan', 'Permission baru disimpan.')
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
        return view('livewire.settings.permission.add');
    }
}
