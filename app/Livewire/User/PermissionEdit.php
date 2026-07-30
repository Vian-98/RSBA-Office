<?php

namespace App\Livewire\User;

use Throwable;
use App\Models\Menu;
use App\Models\User;
use Livewire\Component;
use App\Enums\MenuGroup;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;

#[Lazy]
class PermissionEdit extends Component
{
    use Interactions;

    #[Locked]
    public ?User $user;

    public $mainMenu;
    public $groups;
    public $group = '';

    public $permission = [];
    public $rolePermission = [];

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
        $this->mainMenu = Menu::first();
        $this->groups = MenuGroup::options();

        // $this->menus = Menu::where('id', '!=', $this->mainMenu->id)->with('submenus')->get();

        $this->permission = $this->user->getAllPermissions()->pluck('name')->toArray();

        $roleName = $this->user->getRoleNames()->first();
        $roleUser = $roleName ? Role::findByName($roleName) : null;
        $this->rolePermission = $roleUser ? $roleUser->permissions->pluck('name') : collect();
    }


    #[Computed]
    public function groupMenu()
    {
        return collect(MenuGroup::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->nama()
        ]);
    }

    #[Computed]
    public function menus()
    {
        return Menu::where('id', '!=', $this->mainMenu->id)
            ->with('submenus')
            ->where('group', $this->group)
            ->orderBy('group', 'ASC')
            ->orderBy('nama', 'ASC')
            ->get()
            ->groupBy('group');
    }

    function submit()
    {
        #Spesial permssion to user, selain user mempunyai permission berdasarkan Role, bisa juga diberi permission khusus dari sini

        // $this->validate();

        DB::beginTransaction();
        try {
            $this->user->syncPermissions($this->permission);

            DB::commit();

            $this->dispatch('updated-permission-user');

            // Clear Cache Menu & Permissions User
            Cache::forget('user-sidebar-menu:' . $this->user->id);
            Cache::forget('user-permissions:view:' . $this->user->id);

            $this->toast()
                ->success('Sukses', 'Spesial permission diperbaharui.')
                ->send();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->toast()
                ->error('Failed', 'Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.user.permission-edit');
    }
}
