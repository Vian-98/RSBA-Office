<?php

namespace App\Livewire\Settings\Role;

use Throwable;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

#[Lazy]
class SetPermission  extends Component
{
    use Interactions;

    public ?Role $role;
    public $searchPermissions;
    public $permission = [];

    public $rules = [
        'permission' => 'required'
    ];

    function mount($id)
    {
        $this->role = Role::findOrFail($id);

        $this->permission = $this->role->permissions()->pluck('name')->toArray();;
    }

    public function getPermissions()
    {
        return Permission::select('name')
            ->when($this->searchPermissions, function ($query, $search) {
                return $query->where('name', 'like', "%" . $search . "%");
            })->get();
    }

    public function SearchPermission($term)
    {
        $this->searchPermissions = $term;
    }

    public function selectAll()
    {
        $this->permission = Permission::pluck('name')->toArray();
    }

    public function deselectAll()
    {
        $this->permission = [];
    }

    function submit()
    {
        $this->validate();

        // dd($this->permission);

        DB::beginTransaction();
        try {
            $this->role->syncPermissions($this->permission);

            DB::commit();

            Cache::flush();

            $this->toast()
                ->success('Sukses', 'Setting permission role di update.')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();
            $this->toast()
                ->error('Failed', 'Error :' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view(
            'livewire.settings.role.set-permission',
            ['permissions' => $this->getPermissions()]
        );
    }
}
