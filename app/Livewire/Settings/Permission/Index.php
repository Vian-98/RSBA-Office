<?php

namespace App\Livewire\Settings\Permission;

use App\Traits\AuthorizesFromRoute;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;

#[Title('Permission')]
#[Lazy(isolate: false)]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.settings.permission.index');
    }
}
