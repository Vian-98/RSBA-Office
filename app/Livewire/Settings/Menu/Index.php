<?php

namespace App\Livewire\Settings\Menu;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Lazy]
#[Title('Setting Menu')]
class Index extends Component
{
    use AuthorizesFromRoute;

    public function render()
    {
        // $this->authorize('view-menus');
        $this->authorizeFromRoute();
        return view('livewire.settings.menu.index');
    }
}
