<?php

namespace App\Livewire\Profile;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Setting')]
#[Lazy]
class Setting extends Component
{
    #[Url]
    public $tab = 'password';

    public function render()
    {
        return view('livewire.profile.setting')
            ->title('Settings');
    }
}
