<?php

namespace App\Livewire\Profile;

use Throwable;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use TallStackUi\Traits\Interactions;

#[Lazy]
class GantiPassword extends Component
{
    use Interactions;

    public $current_password, $password, $password_confirmation;

    public $rules = [
        'current_password' => ['required', 'string'],
        'password' => ['required', 'string', 'min:8', 'confirmed']
    ];

    function gantiPassword(): void
    {
        $this->validate();

        // Check if the current password matches
        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->toast()->error('Confirmation Passowrd', 'Password baru tidak cocok.')->send();
            return;
        }


        try {
            $user = User::findOrFail(Auth::user()->id);
            $user->password = Hash::make($this->password);
            $user->update();

            $this->toast()->success('Sukses', 'Password berhasil diperbaharui.')->send();
        } catch (Throwable $e) {
            $this->toast()->error('Failed', 'Error :' . $e->getMessage())->send();
        }
    }

    public function render()
    {
        return view('livewire.profile.ganti-password');
    }
}
