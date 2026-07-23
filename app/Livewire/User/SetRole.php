<?php

namespace App\Livewire\User;

use Throwable;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Cache;

#[Lazy]
class SetRole extends Component
{
    use Interactions;

    public ?User $user;
    public ?Role $roleIdSelected;

    public $roles;
    public $role;

    public $rules = [
        'role' => 'required'
    ];

    function mount($id)
    {
        $this->user = User::findOrFail($id);
        $this->roles = Role::all();
        $this->role = $this->user->getRoleNames()->first();
    }


    function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $this->user->syncRoles($this->role);
            DB::commit();

            Cache::forget('user-sidebar-menu:' . $this->user->id);
            Cache::forget('user-permissions:view:' . $this->user->id);

            $this->dispatch('updated-role-user');
            $this->dispatch('close-modal', id: 'set-user-role');
            
            $this->toast()
                ->success('Sukses', 'Set role user berhasil.')
                ->send();
        } catch (Throwable $e) {
            DB::rollback();
            $this->toast()
                ->error('Failed', 'Error:' . $e->getMessage())
                ->send();
        }
    }

    function editPermission($roleId)
    {
        $this->roleIdSelected = Role::findOrFail($roleId);
        $this->dispatch(
            'open-modal',
            id: 'set-permission'
        );
    }


    public function render()
    {
        return view('livewire.user.set-role');
    }
}
