<?php

namespace App\Livewire\User;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        // $this->authorize('view-user');
        $this->authorizeFromRoute();
        return view('livewire.user.index');
    }
}
