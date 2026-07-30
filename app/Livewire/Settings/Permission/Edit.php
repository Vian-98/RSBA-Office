<?php

namespace App\Livewire\Settings\Permission;

use Throwable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use TallStackUi\Traits\Interactions;

#[Lazy]
class Edit extends Component
{
    use Interactions;

    #[Locked]
    public ?Permission $permission;
    public ?string $nama;

    public function rules(): array
    {
        return [
            'nama' => 'required|unique:permissions,name'
        ];
    }

    public function mount($id)
    {
        $this->permission = Permission::findOrFail($id);
        $this->nama = $this->permission->name;
    }

    function submit()
    {
        $this->validate();

        $nama_old = "<span class='text-red-500'>" . $this->permission->name . "</span>"; //hanya untuk toaster
        $nama_new = "<span class='text-primary-500'>" . $this->nama . "</span>"; //hanya untuk toaster

        DB::beginTransaction();
        try {
            $this->permission->name = $this->nama;
            $this->permission->save();

            DB::commit();

            $this->toast()
                ->success('Berhasil', "Update $nama_old menjadi $nama_new")
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.settings.permission.edit');
    }
}
