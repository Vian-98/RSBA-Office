<?php

namespace App\Livewire\Master\Jabatan;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Lazy]
#[Title('Data Jabatan')]
class Index extends Component
{
    use AuthorizesFromRoute;

    #[Url]
    public string $activeTab = 'table';

    public function selectTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.master.jabatan.index');
    }
}
