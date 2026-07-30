<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class RolePermission extends Component
{
    public $role;
    public $permissionInRole;
    public $specialPermission;

    public function mount()
    {
        $user = Auth::user();

        $this->role = $user->getRoleNames();
        $this->permissionInRole = $user->getPermissionsViaRoles()->sortBy('name');
        $this->specialPermission = $user->getAllPermissions()->sortBy('name');
    }

    public function render()
    {
        return view('livewire.profile.role-permission');
    }
}
