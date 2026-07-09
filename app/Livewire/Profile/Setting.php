<?php

namespace App\Livewire\Profile;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Setting')]
#[Lazy]
class Setting extends Component
{
    public $tabsActive;

    function switchTab($tab)
    {
        $this->tabsActive = $tab;
    }

    public function render()
    {
        return view('livewire.profile.setting')
            ->title('Settings');
    }
}
